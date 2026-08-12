<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
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
            'photo'         => 'nullable|image|max:2048',
            'facilities'    => 'nullable|array',
            'facilities.*'  => 'exists:facilities,id',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('rooms', 'public');
        }

        $facilityIds = $validated['facilities'] ?? [];
        unset($validated['facilities']);

        $room = Room::create($validated);
        $room->facilities()->sync($facilityIds);

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
