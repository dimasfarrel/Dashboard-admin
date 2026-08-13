<?php

namespace App\Http\Controllers;

use App\Models\Lodging;
use App\Models\Room;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LodgingController extends Controller
{
    public function index(Request $request)
    {
        $query = Lodging::with('room');

        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('room_id')) $query->where('room_id', $request->room_id);

        $lodgings = $query->orderByDesc('check_in')->paginate(15);
        $rooms    = Room::orderBy('room_number')->get();

        $activeLodgings   = Lodging::where('status', 'active')->count();
        $totalLodgingRev  = Lodging::where('payment_status', 'paid')->sum('total_price');
        $defaultPrice     = (int) AppSetting::get('lodging_default_price', 150000);

        return view('lodgings.index', compact('lodgings', 'rooms', 'activeLodgings', 'totalLodgingRev', 'defaultPrice'));
    }

    public function create()
    {
        $rooms        = Room::with(['tenant', 'activeLodging'])->orderBy('room_number')->get();
        $defaultPrice = (int) AppSetting::get('lodging_default_price', 150000);
        return view('lodgings.create', compact('rooms', 'defaultPrice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'         => 'required|exists:rooms,id',
            'pic_name'        => 'required|string|max:255',
            'pic_phone'       => 'required|string|max:20',
            'pic_nik'         => 'nullable|string|max:16',
            'pic_address'     => 'nullable|string',
            'check_in'        => 'required|date',
            'check_out'       => 'required|date|after:check_in',
            'guest_count'     => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'deposit'         => 'nullable|numeric|min:0',
            'daily_discount'  => 'nullable|numeric|min:0',
            'fixed_discount'  => 'nullable|numeric|min:0',
            'custom_adjustment' => 'nullable|numeric',
            'payment_status'  => 'required|in:paid,partial,unpaid',
            'payment_method'  => 'nullable|in:tunai,transfer,qris,lain-lain',
            'status'          => 'required|in:active,completed,cancelled',
            'guest_names'     => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $checkIn  = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out'])->startOfDay();
        $duration = $checkIn->diffInDays($checkOut);

        $validated['duration_days']     = max(1, $duration);
        $validated['deposit']           = $validated['deposit'] ?? 0;
        $validated['daily_discount']    = $validated['daily_discount'] ?? 0;
        $validated['fixed_discount']    = $validated['fixed_discount'] ?? 0;
        $validated['custom_adjustment'] = $validated['custom_adjustment'] ?? 0;
        // Keep legacy discount field in sync with fixed_discount
        $validated['discount']          = $validated['fixed_discount'];

        // total = (price_per_night - daily_discount) * guest_count * duration_days - fixed_discount
        $netPerNight = max(0, $validated['price_per_night'] - $validated['daily_discount']);
        $base        = $netPerNight * $validated['guest_count'] * $validated['duration_days'];
        $total       = $base - $validated['fixed_discount'] - $validated['custom_adjustment'];
        $validated['total_price'] = max(0, $total);

        $lodging = Lodging::create($validated);

        $this->syncRoomStatus($lodging->room_id);

        return redirect()->route('lodgings.index')
            ->with('success', 'Data penginapan berhasil dicatat!');
    }

    public function show(Lodging $lodging)
    {
        $lodging->load('room');
        return view('lodgings.show', compact('lodging'));
    }

    public function edit(Lodging $lodging)
    {
        $rooms        = Room::with(['tenant', 'activeLodging'])->orderBy('room_number')->get();
        $defaultPrice = (int) AppSetting::get('lodging_default_price', 150000);
        return view('lodgings.edit', compact('lodging', 'rooms', 'defaultPrice'));
    }

    public function update(Request $request, Lodging $lodging)
    {
        $originalRoomId = $lodging->room_id;

        $validated = $request->validate([
            'room_id'         => 'required|exists:rooms,id',
            'pic_name'        => 'required|string|max:255',
            'pic_phone'       => 'required|string|max:20',
            'pic_nik'         => 'nullable|string|max:16',
            'pic_address'     => 'nullable|string',
            'check_in'        => 'required|date',
            'check_out'       => 'required|date|after:check_in',
            'guest_count'     => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'deposit'         => 'nullable|numeric|min:0',
            'daily_discount'  => 'nullable|numeric|min:0',
            'fixed_discount'  => 'nullable|numeric|min:0',
            'custom_adjustment' => 'nullable|numeric',
            'payment_status'  => 'required|in:paid,partial,unpaid',
            'payment_method'  => 'nullable|in:tunai,transfer,qris,lain-lain',
            'status'          => 'required|in:active,completed,cancelled',
            'guest_names'     => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $checkIn  = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out'])->startOfDay();
        $duration = $checkIn->diffInDays($checkOut);

        $validated['duration_days']     = max(1, $duration);
        $validated['deposit']           = $validated['deposit'] ?? 0;
        $validated['daily_discount']    = $validated['daily_discount'] ?? 0;
        $validated['fixed_discount']    = $validated['fixed_discount'] ?? 0;
        $validated['custom_adjustment'] = $validated['custom_adjustment'] ?? 0;
        $validated['discount']          = $validated['fixed_discount'];

        $netPerNight = max(0, $validated['price_per_night'] - $validated['daily_discount']);
        $base        = $netPerNight * $validated['guest_count'] * $validated['duration_days'];
        $total       = $base - $validated['fixed_discount'] - $validated['custom_adjustment'];
        $validated['total_price'] = max(0, $total);

        $lodging->update($validated);

        $this->syncRoomStatus($originalRoomId);
        if ($originalRoomId != $lodging->room_id) {
            $this->syncRoomStatus($lodging->room_id);
        }

        return redirect()->route('lodgings.show', $lodging)
            ->with('success', 'Data penginapan berhasil diperbarui!');
    }

    public function destroy(Lodging $lodging)
    {
        $roomId = $lodging->room_id;
        $lodging->delete();

        $this->syncRoomStatus($roomId);

        return redirect()->route('lodgings.index')
            ->with('success', 'Data penginapan berhasil dihapus.');
    }

    /**
     * Update the default lodging price setting
     */
    public function updateDefaultPrice(Request $request)
    {
        $request->validate([
            'lodging_default_price' => 'required|numeric|min:0',
        ]);

        AppSetting::set('lodging_default_price', $request->lodging_default_price);

        return redirect()->back()->with('success', 'Harga default penginapan berhasil diperbarui!');
    }

    private function syncRoomStatus($roomId)
    {
        $room = Room::with('tenant')->find($roomId);
        if (!$room) return;

        if ($room->tenant && $room->tenant->status === 'active') {
            $room->update(['status' => 'occupied']);
            return;
        }

        $hasActiveLodging = Lodging::where('room_id', $roomId)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveLodging) {
            $room->update(['status' => 'occupied']);
        } else {
            if ($room->status !== 'maintenance') {
                $room->update(['status' => 'available']);
            }
        }
    }
}
