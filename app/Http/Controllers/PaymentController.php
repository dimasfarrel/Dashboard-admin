<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Lodging;
use App\Models\OtherIncome;
use App\Models\TenantDeposit;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Auto-mark overdue payments: if today > due_day of the period month/year and status is still 'pending'
     */
    private function autoMarkOverdue(): void
    {
        $dueDay = (int) AppSetting::get('payment_due_day', 10);
        $today  = now();

        // Find all pending payments whose due date has passed
        Payment::where('status', 'pending')
            ->get()
            ->each(function (Payment $p) use ($dueDay, $today) {
                // Due date is the $dueDay of period_month/period_year
                $dueDate = Carbon::createFromDate($p->period_year, $p->period_month, min($dueDay, 28));
                if ($today->gt($dueDate)) {
                    $p->update(['status' => 'overdue', 'due_date' => $dueDate->toDateString()]);
                }
            });
    }

    public function index(Request $request)
    {
        // Auto-mark overdue on every page visit
        $this->autoMarkOverdue();

        $selectedPeriodMonth = $request->input('period_month') ? (int) $request->input('period_month') : null;
        $selectedPeriodYear  = $request->input('period_year') ? (int) $request->input('period_year') : null;
        $selectedPayMonth    = $request->input('pay_month') ? (int) $request->input('pay_month') : null;
        $selectedPayYear     = $request->input('pay_year') ? (int) $request->input('pay_year') : null;
        $selectedStatus      = $request->input('status');
        $selectedRoomId      = $request->input('room_id');
        $selectedType        = $request->input('type');

        $dueDay = (int) AppSetting::get('payment_due_day', 10);

        // --- 1. Rental Payments (Sewa) ---
        $paymentsQuery = Payment::with(['room', 'tenant']);
        
        if ($selectedPeriodMonth) $paymentsQuery->where('period_month', $selectedPeriodMonth);
        if ($selectedPeriodYear)  $paymentsQuery->where('period_year', $selectedPeriodYear);
        if ($selectedPayMonth)    $paymentsQuery->whereMonth('paid_at', $selectedPayMonth);
        if ($selectedPayYear)     $paymentsQuery->whereYear('paid_at', $selectedPayYear);
        if ($selectedStatus)      $paymentsQuery->where('status', $selectedStatus);
        if ($selectedRoomId)      $paymentsQuery->where('room_id', $selectedRoomId);

        $paymentsDb = $paymentsQuery->get();
        
        $paymentsList = $paymentsDb->map(function($p) {
            return [
                'type'      => 'rental',
                'id'        => $p->id,
                'room'      => $p->room?->room_number ?? '—',
                'name'      => $p->tenant?->name ?? '—',
                'amount'    => $p->amount,
                'method'    => $p->payment_method ?? '—',
                'status'    => $p->status,
                'period'    => $p->period_label,
                'date'      => $p->paid_at ?? $p->created_at,
                'created_at'=> $p->created_at,
                'due_date'  => $p->due_date,
                'link'      => route('payments.show', $p),
                'edit_link' => route('payments.edit', $p),
                'is_virtual'=> false,
            ];
        });

        // --- Virtual Unpaid Generation ---
        $virtualUnpaidList = collect([]);
        if ($selectedPeriodMonth && $selectedPeriodYear && (!$selectedType || $selectedType === 'rental')) {
            $activeTenants = Tenant::with('room')->where('status', 'active')->get();
            if ($selectedRoomId) {
                $activeTenants = $activeTenants->where('room_id', $selectedRoomId);
            }
            
            $existingTenantIds = $paymentsDb->pluck('tenant_id')->toArray();
            
            $today = now();
            foreach ($activeTenants as $tenant) {
                if (!in_array($tenant->id, $existingTenantIds)) {
                    // Check if it should be overdue
                    $tDueDay = $tenant->start_date ? Carbon::parse($tenant->start_date)->day : $dueDay;
                    $dueDate = Carbon::createFromDate($selectedPeriodYear, $selectedPeriodMonth, min($tDueDay, 28));
                    
                    $vStatus = $today->gt($dueDate) ? 'overdue' : 'pending';
                    
                    if ($selectedStatus && $selectedStatus !== $vStatus) {
                        continue;
                    }
                    if ($selectedPayMonth || $selectedPayYear) {
                        continue; // Virtual unpaid have no pay date
                    }

                    $monthName = Carbon::create()->setMonth((int)$selectedPeriodMonth)->translatedFormat('F');
                    
                    $virtualUnpaidList->push([
                        'type'      => 'rental',
                        'id'        => 'virtual_' . $tenant->id,
                        'room'      => $tenant->room?->room_number ?? '—',
                        'name'      => $tenant->name,
                        'amount'    => $tenant->room?->price ?? 0,
                        'method'    => '—',
                        'status'    => $vStatus,
                        'period'    => $monthName . ' ' . $selectedPeriodYear,
                        'date'      => null,
                        'created_at'=> null,
                        'due_date'  => $dueDate->toDateString(),
                        'link'      => route('payments.create', ['tenant_id' => $tenant->id, 'period_month' => $selectedPeriodMonth, 'period_year' => $selectedPeriodYear]),
                        'edit_link' => route('payments.create', ['tenant_id' => $tenant->id, 'period_month' => $selectedPeriodMonth, 'period_year' => $selectedPeriodYear]),
                        'is_virtual'=> true,
                    ]);
                }
            }
        }

        // --- 2. Lodgings (Harian) ---
        $lodgingsQuery = Lodging::with('room');
        if ($selectedPeriodMonth) $lodgingsQuery->whereMonth('paid_at', $selectedPeriodMonth);
        if ($selectedPeriodYear)  $lodgingsQuery->whereYear('paid_at', $selectedPeriodYear);
        if ($selectedPayMonth)    $lodgingsQuery->whereMonth('paid_at', $selectedPayMonth);
        if ($selectedPayYear)     $lodgingsQuery->whereYear('paid_at', $selectedPayYear);
        if ($selectedRoomId)      $lodgingsQuery->where('room_id', $selectedRoomId);

        if ($selectedStatus) {
            $lodgingStatus = $selectedStatus;
            if ($lodgingStatus === 'paid') {
                $lodgingsQuery->where('payment_status', 'paid');
            } elseif ($lodgingStatus === 'pending') {
                $lodgingsQuery->where('payment_status', 'partial');
            } else {
                $lodgingsQuery->where('payment_status', 'unpaid');
            }
        }

        $lodgingsList = $lodgingsQuery->get()->map(function($l) {
            return [
                'type'      => 'lodging',
                'id'        => $l->id,
                'room'      => $l->room?->room_number ?? '—',
                'name'      => $l->pic_name . ' (Tamu Harian)',
                'amount'    => $l->total_price,
                'method'    => $l->payment_method ?? '—',
                'status'    => $l->payment_status === 'paid' ? 'paid' : ($l->payment_status === 'partial' ? 'pending' : 'overdue'),
                'period'    => $l->check_in->format('d/m') . ' - ' . $l->check_out->format('d/m/Y'),
                'date'      => $l->paid_at ?? $l->check_in,
                'created_at'=> $l->created_at,
                'due_date'  => null,
                'link'      => route('lodgings.show', $l),
                'edit_link' => route('lodgings.edit', $l),
                'is_virtual'=> false,
            ];
        });

        // --- 3. Other Incomes (Lainnya) ---
        $otherIncomesQuery = OtherIncome::query();
        if ($selectedPeriodMonth) $otherIncomesQuery->where('period_month', $selectedPeriodMonth);
        if ($selectedPeriodYear)  $otherIncomesQuery->where('period_year', $selectedPeriodYear);
        if ($selectedPayMonth)    $otherIncomesQuery->whereMonth('income_date', $selectedPayMonth);
        if ($selectedPayYear)     $otherIncomesQuery->whereYear('income_date', $selectedPayYear);
        
        $otherList = $otherIncomesQuery->get()->map(function($o) {
            return [
                'type'      => 'other',
                'id'        => $o->id,
                'room'      => '—',
                'name'      => $o->title,
                'amount'    => $o->amount,
                'method'    => '—',
                'status'    => 'paid',
                'period'    => \Carbon\Carbon::now()->setMonth((int)$o->period_month)->translatedFormat('F') . ' ' . $o->period_year,
                'date'      => $o->income_date,
                'created_at'=> $o->created_at,
                'due_date'  => null,
                'link'      => route('other-incomes.show', $o),
                'edit_link' => route('other-incomes.edit', $o),
                'is_virtual'=> false,
            ];
        });

        if ($selectedStatus && $selectedStatus !== 'paid') {
            $otherList = collect([]);
        }
        if ($selectedRoomId) {
            $otherList = collect([]); // Other incomes don't have room_id
        }

        // --- 4. Deposit Incomes (Setor Deposit) ---
        $depositsQuery = TenantDeposit::with(['tenant.room'])->where('type', 'credit');
        if ($selectedPeriodMonth) $depositsQuery->whereMonth('date', $selectedPeriodMonth);
        if ($selectedPeriodYear)  $depositsQuery->whereYear('date', $selectedPeriodYear);
        if ($selectedPayMonth)    $depositsQuery->whereMonth('date', $selectedPayMonth);
        if ($selectedPayYear)     $depositsQuery->whereYear('date', $selectedPayYear);
        if ($selectedRoomId) {
            $depositsQuery->whereHas('tenant', function($q) use ($selectedRoomId) {
                $q->where('room_id', $selectedRoomId);
            });
        }
        if ($selectedStatus && $selectedStatus !== 'paid') {
            $depositsList = collect([]);
        } else {
            $depositsList = $depositsQuery->get()->map(function($d) {
                return [
                    'type'      => 'deposit',
                    'id'        => $d->id,
                    'room'      => $d->tenant?->room?->room_number ?? '—',
                    'name'      => ($d->tenant?->name ?? '—') . ' (Deposit)',
                    'amount'    => $d->amount,
                    'method'    => '—',
                    'status'    => 'paid',
                    'period'    => $d->description,
                    'date'      => $d->date,
                    'created_at'=> $d->created_at,
                    'due_date'  => null,
                    'link'      => route('tenants.show', $d->tenant_id),
                    'edit_link' => route('tenants.show', $d->tenant_id),
                    'is_virtual'=> false,
                ];
            });
        }

        // --- Merge and Filter by Type ---
        $allTransactions = collect([]);
        
        if (!$selectedType || $selectedType === 'rental') {
            $allTransactions = $allTransactions->concat($paymentsList)->concat($virtualUnpaidList);
        }
        if (!$selectedType || $selectedType === 'lodging') {
            $allTransactions = $allTransactions->concat($lodgingsList);
        }
        if (!$selectedType || $selectedType === 'other') {
            $allTransactions = $allTransactions->concat($otherList);
        }
        if (!$selectedType || $selectedType === 'deposit') {
            $allTransactions = $allTransactions->concat($depositsList);
        }

        // Sort: real records by date, virtual ones at top? 
        $transactions = $allTransactions->sortByDesc(function($t) {
            if ($t['created_at'] === null) {
                return PHP_INT_MAX; 
            }
            return \Carbon\Carbon::parse($t['created_at'])->timestamp;
        });

        // Pagination
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentPageItems = $transactions->values()->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedTransactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $transactions->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // --- Calculate Summary based on CURRENT Filter ---
        $totalPaid = $allTransactions->where('status', 'paid')->where('type', 'rental')->sum('amount');
        $totalLodgingPaid = $allTransactions->where('status', 'paid')->where('type', 'lodging')->sum('amount');
        $totalOtherPaid = $allTransactions->where('status', 'paid')->where('type', 'other')->sum('amount');
        $totalDepositPaid = $allTransactions->where('status', 'paid')->where('type', 'deposit')->sum('amount');

        $totalPending = $allTransactions->where('status', 'pending')->count();
        $totalOverdue = $allTransactions->where('status', 'overdue')->count();

        $rooms = Room::orderBy('room_number')->get();

        return view('payments.index', compact(
            'paginatedTransactions', 'totalPaid', 'totalLodgingPaid', 'totalOtherPaid', 'totalDepositPaid',
            'totalPending', 'totalOverdue', 'dueDay', 'rooms'
        ));
    }

    public function create()
    {
        $tenants = Tenant::with('room')->orderBy('status')->orderBy('name')->get();
        $dueDay  = (int) AppSetting::get('payment_due_day', 10);
        return view('payments.create', compact('tenants', 'dueDay'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id'      => 'required|exists:tenants,id',
            'room_id'        => 'required|exists:rooms,id',
            'amount'         => 'required|numeric|min:0',
            'period_month'   => 'required|integer|between:1,12',
            'period_year'    => 'required|integer|min:2020',
            'paid_at'        => 'nullable|date',
            'due_date'       => 'nullable|date',
            'status'         => 'required|in:paid,pending,overdue',
            'payment_method' => 'nullable|in:tunai,transfer,qris,lain-lain',
            'receipt_photo'  => 'nullable|image|max:2048',
            'notes'          => 'nullable|string',
        ]);

        // Auto-fill due_date if not provided
        if (empty($validated['due_date'])) {
            $tenant = Tenant::find($validated['tenant_id']);
            $dueDay = $tenant ? $tenant->start_date->day : (int) AppSetting::get('payment_due_day', 10);
            $validated['due_date'] = Carbon::createFromDate(
                $validated['period_year'],
                $validated['period_month'],
                min($dueDay, 28)
            )->toDateString();
        }

        if ($request->hasFile('receipt_photo')) {
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('receipts', 'public');
        }

        $payment = Payment::create($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Pembayaran berhasil dicatat!');
    }

    public function show(Payment $payment)
    {
        $payment->load(['room', 'tenant']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $tenants = Tenant::with('room')->orderBy('status')->orderBy('name')->get();
        $dueDay  = (int) AppSetting::get('payment_due_day', 10);
        return view('payments.edit', compact('payment', 'tenants', 'dueDay'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'tenant_id'      => 'required|exists:tenants,id',
            'room_id'        => 'required|exists:rooms,id',
            'amount'         => 'required|numeric|min:0',
            'period_month'   => 'required|integer|between:1,12',
            'period_year'    => 'required|integer|min:2020',
            'paid_at'        => 'nullable|date',
            'due_date'       => 'nullable|date',
            'status'         => 'required|in:paid,pending,overdue',
            'payment_method' => 'nullable|in:tunai,transfer,qris,lain-lain',
            'receipt_photo'  => 'nullable|image|max:2048',
            'notes'          => 'nullable|string',
        ]);

        if ($request->hasFile('receipt_photo')) {
            if ($payment->receipt_photo) Storage::disk('public')->delete($payment->receipt_photo);
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('receipts', 'public');
        }

        $payment->update($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Data pembayaran berhasil diperbarui!');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->receipt_photo) Storage::disk('public')->delete($payment->receipt_photo);
        $payment->delete();
        return redirect()->route('payments.index')
            ->with('success', 'Data pembayaran berhasil dihapus.');
    }

    /**
     * Update the payment due day setting
     */
    public function updateDueDay(Request $request)
    {
        $request->validate([
            'due_day' => 'required|integer|between:1,28',
        ]);

        AppSetting::set('payment_due_day', $request->due_day);

        return redirect()->route('payments.index')
            ->with('success', "Tanggal jatuh tempo berhasil diubah ke tanggal {$request->due_day}!");
    }
}
