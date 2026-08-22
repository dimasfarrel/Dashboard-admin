<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\RoomType;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function vacantForecast(Request $request)
    {
        $currentMonth = $request->input('month', date('n'));
        $currentYear = $request->input('year', date('Y'));
        
        $selectedDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();
        
        $tenants = Tenant::with('room')
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $selectedDate->toDateString())
            ->orderBy('end_date', 'desc')
            ->get();
            
        return view('rooms.vacant_forecast', compact('tenants', 'currentMonth', 'currentYear'));
    }

    public function index(Request $request)
    {
        $query = Room::with(['tenant', 'activeLodging', 'facilities']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Load all rooms and group by floor (no pagination — scroll instead)
        $rooms       = $query->orderBy('floor')->orderBy('room_number')->get();
        $roomsByFloor = $rooms->groupBy('floor');

        return view('rooms.index', compact('rooms', 'roomsByFloor'));
    }

    public function create()
    {
        $facilities = Facility::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $floors     = Floor::orderBy('number')->get();
        $roomTypes  = RoomType::orderBy('name')->get();
        return view('rooms.create', compact('facilities', 'floors', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number'   => 'required|string|max:20|unique:rooms',
            'floor'         => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
            'status'        => 'required|in:available,occupied,maintenance',
            'type'          => 'nullable|string|max:50',
            'size_sqm'      => 'nullable|integer|min:1',
            'description'   => 'nullable|string',
            'is_published'  => 'nullable|boolean',
            'images'        => 'nullable|array',
            'images.*'      => 'image|max:2048',
            'facilities'    => 'nullable|array',
            'facilities.*'  => 'exists:facilities,id',
        ]);

        $validated['is_published'] = $request->has('is_published');

        if ($request->hasFile('images')) {
            // Save the first image to the 'photo' column for backwards compatibility
            $validated['photo'] = $request->file('images')[0]->store('rooms', 'public');
        }

        $facilityIds = $validated['facilities'] ?? [];
        unset($validated['facilities']);

        $room = Room::create($validated);
        $room->facilities()->sync($facilityIds);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('rooms', 'public');
                $room->images()->create([
                    'image_path' => $path,
                    'is_primary' => $index === 0
                ]);
            }
        }

        return redirect()->route('rooms.show', $room)
            ->with('success', "Kamar {$room->room_number} berhasil ditambahkan!");
    }

    public function show(Room $room)
    {
        $room->load(['facilities', 'tenant', 'maintenances' => function($q) {
            $q->latest()->take(5);
        }, 'payments' => function($q) {
            $q->latest()->take(6);
        }]);

        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $facilities   = Facility::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $roomFacIds   = $room->facilities->pluck('id')->toArray();
        $floors       = Floor::orderBy('number')->get();
        $roomTypes    = RoomType::orderBy('name')->get();
        return view('rooms.edit', compact('room', 'facilities', 'roomFacIds', 'floors', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number'   => 'required|string|max:20|unique:rooms,room_number,' . $room->id,
            'floor'         => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
            'status'        => 'required|in:available,occupied,maintenance',
            'type'          => 'nullable|string|max:50',
            'size_sqm'      => 'nullable|integer|min:1',
            'description'   => 'nullable|string',
            'photo'         => 'nullable|image|max:2048',
            'facilities'    => 'nullable|array',
            'facilities.*'  => 'exists:facilities,id',
        ]);

        if ($request->hasFile('photo')) {
            if ($room->photo) Storage::disk('public')->delete($room->photo);
            $validated['photo'] = $request->file('photo')->store('rooms', 'public');
        }

        $facilityIds = $validated['facilities'] ?? [];
        unset($validated['facilities']);

        $room->update($validated);
        $room->facilities()->sync($facilityIds);

        return redirect()->route('rooms.show', $room)
            ->with('success', "Data kamar {$room->room_number} berhasil diperbarui!");
    }

    /**
     * Quick-update room status (AJAX/redirect)
     */
    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $room->update(['status' => $request->status]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $room->status]);
        }

        return redirect()->back()->with('success', "Status kamar {$room->room_number} diperbarui!");
    }

    public function destroy(Room $room)
    {
        if ($room->photo) Storage::disk('public')->delete($room->photo);
        $roomNumber = $room->room_number;
        $room->delete();
        return redirect()->route('rooms.index')
            ->with('success', "Kamar {$roomNumber} berhasil dihapus.");
    }
}
