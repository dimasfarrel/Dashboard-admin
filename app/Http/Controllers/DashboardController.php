<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\RoomMaintenance;
use App\Models\Expense;
use App\Models\Lodging;
use App\Models\OtherIncome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Accept month/year filter — default to current month
        $currentMonth = (int) $request->input('month', now()->month);
        $currentYear  = (int) $request->input('year', now()->year);

        $totalRooms     = Room::count();
        $availableRooms = Room::where('status', 'available')->count();
        $occupiedRooms  = Room::where('status', 'occupied')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();

        // Omzet bulan ini dari pembayaran
        $monthlyRevenue = Payment::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->where('status', 'paid')
            ->sum('amount');

        // Omzet dari penginapan bulan ini
        $lodgingRevenue = Lodging::whereMonth('paid_at', $currentMonth)
            ->whereYear('paid_at', $currentYear)
            ->where('payment_status', 'paid')
            ->sum('total_price');

        // Omzet dari pendapatan lain-lain bulan ini
        $otherIncomeRevenue = OtherIncome::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->sum('amount');

        // Total pengeluaran bulan ini
        $monthlyExpenses = Expense::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->sum('amount');

        // Total biaya maintenance bulan ini
        $maintenanceCost = RoomMaintenance::whereMonth('report_date', $currentMonth)
            ->whereYear('report_date', $currentYear)
            ->sum('cost');

        // Pembayaran pending
        $pendingPayments = Payment::where('status', 'pending')->count();
        $overduePayments = Payment::where('status', 'overdue')->count();

        // Penginapan aktif
        $activeLodgings = Lodging::where('status', 'active')->count();

        // Maintenance pending
        $pendingMaintenance = RoomMaintenance::whereIn('status', ['pending', 'in_progress'])->count();

        // Omzet 6 bulan terakhir (untuk chart) — selalu relative to now()
        $revenueChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $month = $date->month;
            $year  = $date->year;

            $revPayments = Payment::where('period_month', $month)
                ->where('period_year', $year)
                ->where('status', 'paid')
                ->sum('amount');

            $revLodgings = Lodging::whereMonth('paid_at', $month)
                ->whereYear('paid_at', $year)
                ->where('payment_status', 'paid')
                ->sum('total_price');

            $revOther = OtherIncome::where('period_month', $month)
                ->where('period_year', $year)
                ->sum('amount');

            $rev = $revPayments + $revLodgings + $revOther;

            $exp = Expense::where('period_month', $month)
                ->where('period_year', $year)
                ->sum('amount');

            $maint = RoomMaintenance::whereMonth('report_date', $month)
                ->whereYear('report_date', $year)
                ->where('status', 'done')
                ->sum('cost');

            $totalExpenses = $exp + $maint;

            $revenueChart[] = [
                'label'   => $date->translatedFormat('M Y'),
                'revenue' => (float)$rev,
                'expense' => (float)$totalExpenses,
            ];
        }

        // Kamar terbaru
        $recentRooms = Room::with('tenant')->latest()->take(5)->get();

        // Pembayaran terbaru
        $recentPayments = Payment::with(['room', 'tenant'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalRooms', 'availableRooms', 'occupiedRooms', 'maintenanceRooms',
            'monthlyRevenue', 'lodgingRevenue', 'otherIncomeRevenue',
            'monthlyExpenses', 'maintenanceCost',
            'pendingPayments', 'overduePayments', 'activeLodgings', 'pendingMaintenance',
            'revenueChart', 'recentRooms', 'recentPayments',
            'currentMonth', 'currentYear'
        ));
    }
}
