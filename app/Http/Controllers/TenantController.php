<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Room;
use App\Models\TenantCustomField;
use App\Models\TenantFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with(['room', 'deposits']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('room', function ($q2) use ($search) {
                      $q2->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('parent_search')) {
            $parentSearch = $request->parent_search;
            $query->where(function ($q) use ($parentSearch) {
                $q->where('emergency_contact_name', 'like', "%{$parentSearch}%")
                  ->orWhere('emergency_contact_name_2', 'like', "%{$parentSearch}%");
            });
        }

        $tenants = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        $rooms        = Room::with('tenant')->orderBy('room_number')->get();
        $customFields = TenantCustomField::orderBy('sort_order')->orderBy('name')->get();
        return view('tenants.create', compact('rooms', 'customFields'));
    }

    public function store(Request $request)
    {
        $customFields = TenantCustomField::orderBy('sort_order')->get();

        // Build dynamic validation rules for custom fields
        $customRules = [];
        foreach ($customFields as $field) {
            $rule = $field->is_required ? 'required' : 'nullable';
            $customRules["custom_field.{$field->field_key}"] = $rule . '|string|max:500';
        }

        $validated = $request->validate(array_merge([
            'room_id'                   => 'required|exists:rooms,id',
            'status'                    => 'required|in:active,inactive',
            'name'                      => 'required|string|max:255',
            'nickname'                  => 'nullable|string|max:100',
            'nik'                       => 'required|string|max:255|unique:tenants',
            'phone_wa'                  => 'required|string|max:20',
            'emergency_contact_name'    => 'nullable|string|max:255',
            'emergency_contact_phone'   => 'nullable|string|max:20',
            'emergency_contact_name_2'  => 'nullable|string|max:255',
            'emergency_contact_phone_2' => 'nullable|string|max:20',
            'occupation'                => 'nullable|string|max:100',
            'origin_city'               => 'nullable|string|max:100',
            'gender'                    => 'nullable|in:laki-laki,perempuan',
            'birth_date'                => 'nullable|date',
            'start_date'                => 'required|date',
            'end_date'                  => 'nullable|date|after:start_date',
            'ktp_photo'                 => 'nullable|image|max:3072',
            'selfie_photo'              => 'nullable|image|max:3072',
            'notes'                     => 'nullable|string',
        ], $customRules));

        // Validasi: cegah double-booking kamar untuk penyewa aktif
        if ($validated['status'] === 'active') {
            $existingActive = Tenant::where('room_id', $validated['room_id'])
                ->where('status', 'active')
                ->exists();
            if ($existingActive) {
                return back()->withInput()->withErrors(['room_id' => 'Kamar ini sudah ditempati oleh penyewa aktif lain.']);
            }
        }

        if ($request->hasFile('ktp_photo')) {
            $validated['ktp_photo'] = $request->file('ktp_photo')->store('tenants/ktp', 'public');
        }
        if ($request->hasFile('selfie_photo')) {
            $validated['selfie_photo'] = $request->file('selfie_photo')->store('tenants/selfie', 'public');
        }

        // Remove custom fields from main data
        $customFieldData = $request->input('custom_field', []);
        unset($validated['custom_field']);

        $tenant = DB::transaction(function () use ($validated, $customFieldData) {
            $tenant = Tenant::create($validated);

            // Save custom field values
            foreach ($customFieldData as $key => $value) {
                if ($value !== null && $value !== '') {
                    TenantFieldValue::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'field_key' => $key],
                        ['value' => $value]
                    );
                }
            }

            // Update room status to occupied only if active
            if ($validated['status'] === 'active') {
                Room::where('id', $validated['room_id'])->update(['status' => 'occupied']);
            }

            return $tenant;
        });

        return redirect()->route('tenants.show', $tenant)
            ->with('success', "Data penyewa {$tenant->name} berhasil ditambahkan!");
    }

    public function show(Tenant $tenant)
    {
        $tenant->load([
            'room',
            'payments' => function($q) {
                $q->orderByDesc('period_year')->orderByDesc('period_month');
            },
            'fieldValues',
            'deposits' => function($q) {
                $q->orderByDesc('date')->orderByDesc('created_at');
            },
        ]);

        $customFields = TenantCustomField::orderBy('sort_order')->orderBy('name')->get();

        return view('tenants.show', compact('tenant', 'customFields'));
    }

    public function edit(Tenant $tenant)
    {
        $tenant->load('fieldValues');
        $rooms        = Room::where('status', 'available')
                            ->orWhere('id', $tenant->room_id)
                            ->orderBy('room_number')
                            ->get();
        $customFields = TenantCustomField::orderBy('sort_order')->orderBy('name')->get();

        // Build a map of existing field values
        $fieldValues = $tenant->fieldValues->pluck('value', 'field_key');

        return view('tenants.edit', compact('tenant', 'rooms', 'customFields', 'fieldValues'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $customFields = TenantCustomField::orderBy('sort_order')->get();

        $customRules = [];
        foreach ($customFields as $field) {
            $rule = $field->is_required ? 'required' : 'nullable';
            $customRules["custom_field.{$field->field_key}"] = $rule . '|string|max:500';
        }

        $validated = $request->validate(array_merge([
            'room_id'                   => 'required|exists:rooms,id',
            'name'                      => 'required|string|max:255',
            'nickname'                  => 'nullable|string|max:100',
            'nik'                       => 'required|string|max:255|unique:tenants,nik,' . $tenant->id,
            'phone_wa'                  => 'required|string|max:20',
            'emergency_contact_name'    => 'nullable|string|max:255',
            'emergency_contact_phone'   => 'nullable|string|max:20',
            'emergency_contact_name_2'  => 'nullable|string|max:255',
            'emergency_contact_phone_2' => 'nullable|string|max:20',
            'occupation'                => 'nullable|string|max:100',
            'origin_city'               => 'nullable|string|max:100',
            'gender'                    => 'nullable|in:laki-laki,perempuan',
            'birth_date'                => 'nullable|date',
            'start_date'                => 'required|date',
            'end_date'                  => 'nullable|date|after:start_date',
            'ktp_photo'                 => 'nullable|image|max:3072',
            'selfie_photo'              => 'nullable|image|max:3072',
            'status'                    => 'required|in:active,inactive',
            'notes'                     => 'nullable|string',
        ], $customRules));

        // Validasi: cegah double-booking kamar jika pindah kamar & status aktif
        if ($validated['status'] === 'active') {
            $existingActive = Tenant::where('room_id', $validated['room_id'])
                ->where('status', 'active')
                ->where('id', '!=', $tenant->id)
                ->exists();
            if ($existingActive) {
                return back()->withInput()->withErrors(['room_id' => 'Kamar ini sudah ditempati oleh penyewa aktif lain.']);
            }
        }

        if ($request->hasFile('ktp_photo')) {
            if ($tenant->ktp_photo) Storage::disk('public')->delete($tenant->ktp_photo);
            $validated['ktp_photo'] = $request->file('ktp_photo')->store('tenants/ktp', 'public');
        }
        if ($request->hasFile('selfie_photo')) {
            if ($tenant->selfie_photo) Storage::disk('public')->delete($tenant->selfie_photo);
            $validated['selfie_photo'] = $request->file('selfie_photo')->store('tenants/selfie', 'public');
        }

        $customFieldData = $request->input('custom_field', []);
        unset($validated['custom_field']);

        DB::transaction(function () use ($tenant, $validated, $customFieldData) {
            $oldRoomId = $tenant->room_id;
            $tenant->update($validated);

            // Update room statuses if room changed
            if ($oldRoomId !== $validated['room_id']) {
                Room::where('id', $oldRoomId)->update(['status' => 'available']);
            }

            // Set new room status based on tenant status
            if ($validated['status'] === 'active') {
                Room::where('id', $validated['room_id'])->update(['status' => 'occupied']);
            } else {
                Room::where('id', $validated['room_id'])->update(['status' => 'available']);
            }

            // Save custom field values
            foreach ($customFieldData as $key => $value) {
                TenantFieldValue::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'field_key' => $key],
                    ['value' => $value ?? '']
                );
            }
        });

        return redirect()->route('tenants.show', $tenant)
            ->with('success', "Data penyewa {$tenant->name} berhasil diperbarui!");
    }

    public function destroy(Tenant $tenant)
    {
        $roomId = $tenant->room_id;
        $name   = $tenant->name;
        if ($tenant->ktp_photo)    Storage::disk('public')->delete($tenant->ktp_photo);
        if ($tenant->selfie_photo) Storage::disk('public')->delete($tenant->selfie_photo);
        $wasActive = $tenant->status === 'active';
        $tenant->delete();
        
        if ($wasActive) {
            Room::where('id', $roomId)->update(['status' => 'available']);
        }
        return redirect()->route('tenants.index')
            ->with('success', "Data penyewa {$name} berhasil dihapus.");
    }
}
