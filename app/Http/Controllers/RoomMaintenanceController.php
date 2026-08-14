<?php

namespace App\Http\Controllers;

use App\Models\RoomMaintenance;
use App\Models\Room;
use App\Models\MaintenanceCategory;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomMaintenance::with('room');

        if ($request->filled('room_id'))   $query->where('room_id', $request->room_id);
        if ($request->filled('category'))  $query->where('category', $request->category);
        if ($request->filled('vendor'))    $query->where('vendor', 'like', '%' . $request->vendor . '%');
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('month'))     $query->whereMonth('report_date', $request->month);
        if ($request->filled('year'))      $query->whereYear('report_date', $request->year);

        $maintenances = $query->orderByDesc('report_date')->paginate(15);
        $rooms        = Room::orderBy('room_number')->get();
        $categories   = MaintenanceCategory::orderBy('name')->get();

        $baseStatsQuery = RoomMaintenance::query();
        if ($request->filled('room_id'))   $baseStatsQuery->where('room_id', $request->room_id);
        if ($request->filled('category'))  $baseStatsQuery->where('category', $request->category);
        if ($request->filled('month'))     $baseStatsQuery->whereMonth('report_date', $request->month);
        if ($request->filled('year'))      $baseStatsQuery->whereYear('report_date', $request->year);
        if ($request->filled('vendor'))    $baseStatsQuery->where('vendor', 'like', '%' . $request->vendor . '%');
        
        $totalCost = (clone $baseStatsQuery)->sum('cost');
        $pending = (clone $baseStatsQuery)->where('status', 'pending')->count();
        $inProgress = (clone $baseStatsQuery)->where('status', 'in_progress')->count();
        $resolvedCount = (clone $baseStatsQuery)->where('status', 'done')->count();

        $month = $request->input('month');
        $year = $request->input('year');

        return view('maintenances.index', compact('maintenances', 'rooms', 'categories', 'totalCost', 'pending', 'inProgress', 'resolvedCount', 'month', 'year'));
    }

    public function create()
    {
        $rooms = Room::orderBy('room_number')->get();
        $categories = MaintenanceCategory::orderBy('name')->get();
        return view('maintenances.create', compact('rooms', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'       => 'required|exists:rooms,id',
            'category'      => 'required|exists:maintenance_categories,slug',
            'item_name'     => 'required|string|max:255',
            'description'   => 'required|string',
            'cost'          => 'nullable|numeric|min:0',
            'vendor'        => 'nullable|string|max:255',
            'vendor_phone'  => 'nullable|string|max:20',
            'report_date'   => 'required|date',
            'done_date'     => 'nullable|date|after_or_equal:report_date',
            'before_photo'  => 'nullable|image|max:2048',
            'after_photo'   => 'nullable|image|max:2048',
            'status'        => 'required|in:pending,in_progress,done,cancelled',
            'notes'         => 'nullable|string',
        ]);

        if ($request->hasFile('before_photo')) {
            $validated['before_photo'] = $request->file('before_photo')->store('maintenance', 'public');
        }
        if ($request->hasFile('after_photo')) {
            $validated['after_photo'] = $request->file('after_photo')->store('maintenance', 'public');
        }

        $validated['cost'] = $validated['cost'] ?? 0;
        $maintenance = RoomMaintenance::create($validated);

        // Otomatis catat ke pengeluaran kost jika ada biaya
        if ($maintenance->cost > 0) {
            $this->syncExpense($maintenance);
        }

        return redirect()->route('maintenances.index')
            ->with('success', "Data maintenance berhasil dicatat!");
    }

    public function show(RoomMaintenance $maintenance)
    {
        $maintenance->load('room');
        return view('maintenances.show', compact('maintenance'));
    }

    public function edit(RoomMaintenance $maintenance)
    {
        $rooms = Room::orderBy('room_number')->get();
        $categories = MaintenanceCategory::orderBy('name')->get();
        return view('maintenances.edit', compact('maintenance', 'rooms', 'categories'));
    }

    public function update(Request $request, RoomMaintenance $maintenance)
    {
        $validated = $request->validate([
            'room_id'       => 'required|exists:rooms,id',
            'category'      => 'required|exists:maintenance_categories,slug',
            'item_name'     => 'required|string|max:255',
            'description'   => 'required|string',
            'cost'          => 'nullable|numeric|min:0',
            'vendor'        => 'nullable|string|max:255',
            'vendor_phone'  => 'nullable|string|max:20',
            'report_date'   => 'required|date',
            'done_date'     => 'nullable|date|after_or_equal:report_date',
            'before_photo'  => 'nullable|image|max:2048',
            'after_photo'   => 'nullable|image|max:2048',
            'status'        => 'required|in:pending,in_progress,done,cancelled',
            'notes'         => 'nullable|string',
        ]);

        if ($request->hasFile('before_photo')) {
            if ($maintenance->before_photo) Storage::disk('public')->delete($maintenance->before_photo);
            $validated['before_photo'] = $request->file('before_photo')->store('maintenance', 'public');
        }
        if ($request->hasFile('after_photo')) {
            if ($maintenance->after_photo) Storage::disk('public')->delete($maintenance->after_photo);
            $validated['after_photo'] = $request->file('after_photo')->store('maintenance', 'public');
        }

        $validated['cost'] = $validated['cost'] ?? 0;
        $maintenance->update($validated);

        // Sync pengeluaran kost: update jika ada biaya, hapus jika biaya 0
        if ($maintenance->cost > 0) {
            $this->syncExpense($maintenance);
        } else {
            Expense::where('room_maintenance_id', $maintenance->id)->delete();
        }

        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', "Data maintenance berhasil diperbarui!");
    }

    public function destroy(RoomMaintenance $maintenance)
    {
        if ($maintenance->before_photo) Storage::disk('public')->delete($maintenance->before_photo);
        if ($maintenance->after_photo)  Storage::disk('public')->delete($maintenance->after_photo);
        // Hapus expense terkait jika ada
        Expense::where('room_maintenance_id', $maintenance->id)->delete();
        $maintenance->delete();
        return redirect()->route('maintenances.index')
            ->with('success', "Data maintenance berhasil dihapus.");
    }

    /**
     * Buat atau update Expense dari data maintenance.
     */
    private function syncExpense(RoomMaintenance $maintenance): void
    {
        $room = $maintenance->room ?? Room::find($maintenance->room_id);
        $roomLabel = $room ? "Kamar {$room->room_number}" : "Kamar";

        // Cek apakah kategori 'renovasi' ada di expense_categories
        $categorySlug = \App\Models\ExpenseCategory::where('slug', 'renovasi')->exists()
            ? 'renovasi'
            : (\App\Models\ExpenseCategory::first()?->slug ?? 'lain-lain');

        $expenseDate = $maintenance->done_date ?? $maintenance->report_date;

        Expense::updateOrCreate(
            ['room_maintenance_id' => $maintenance->id],
            [
                'category'      => $categorySlug,
                'title'         => "[Maintenance] {$maintenance->item_name} — {$roomLabel}",
                'description'   => $maintenance->description,
                'amount'        => $maintenance->cost,
                'expense_date'  => $expenseDate,
                'period_month'  => $expenseDate->month,
                'period_year'   => $expenseDate->year,
                'notes'         => $maintenance->vendor ? "Vendor: {$maintenance->vendor}" : null,
            ]
        );
    }
}
