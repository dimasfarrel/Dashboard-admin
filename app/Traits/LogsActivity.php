<?php

namespace App\Traits;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            self::logAction($model, 'Ditambahkan');
        });

        static::updated(function (Model $model) {
            // Check if actual attributes changed
            if (!empty($model->getDirty())) {
                self::logAction($model, 'Diperbarui');
            }
        });

        static::deleted(function (Model $model) {
            self::logAction($model, 'Dihapus');
        });
    }

    protected static function logAction(Model $model, $action)
    {
        $menuName = self::getMenuNameForModel($model);
        
        $oldData = $action === 'Diperbarui' ? $model->getOriginal() : null;
        $newData = $action !== 'Dihapus' ? $model->getAttributes() : null;
        
        $description = self::generateDescription($model, $action);

        SystemLog::create([
            'menu' => $menuName,
            'action' => $action,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }

    protected static function getMenuNameForModel(Model $model)
    {
        if (method_exists($model, 'getLogMenuName')) {
            return $model->getLogMenuName();
        }

        $className = class_basename(get_class($model));

        $menuNames = [
            'Room' => 'Kamar',
            'Tenant' => 'Penyewa',
            'Payment' => 'Sewa Kost',
            'Expense' => 'Pengeluaran',
            'RoomMaintenance' => 'Maintenance',
            'Lodging' => 'Penginapan',
            'OtherIncome' => 'Pendapatan Lain',
            'Loan' => 'Hutang / Piutang',
            'LoanRepayment' => 'Pelunasan',
            'TenantDeposit' => 'Deposit',
            'User' => 'Pengguna',
            'AppSetting' => 'Pengaturan',
        ];

        return $menuNames[$className] ?? $className;
    }

    protected static function generateDescription(Model $model, $action)
    {
        if (method_exists($model, 'getLogDescription')) {
            return $model->getLogDescription($action);
        }

        $className = class_basename(get_class($model));
        $identifier = $model->name ?? $model->title ?? $model->id ?? 'Unknown';

        // Custom descriptions based on model type
        switch ($className) {
            case 'Room':
                $identifier = "Kamar " . ($model->room_number ?? $model->id);
                break;
            case 'Payment':
                $identifier = "Pembayaran " . ($model->period_label ?? "Rp " . number_format($model->amount ?? 0, 0, ',', '.'));
                break;
            case 'Lodging':
                $identifier = "Penginapan oleh " . ($model->pic_name ?? 'Unknown');
                break;
            case 'Tenant':
                $identifier = "Penyewa " . ($model->name ?? 'Unknown');
                break;
            case 'Expense':
                $identifier = "Pengeluaran: " . ($model->title ?? 'Unknown');
                break;
            case 'OtherIncome':
                $identifier = "Pendapatan Lain: " . ($model->title ?? 'Unknown');
                break;
            case 'RoomMaintenance':
                $identifier = "Maintenance Kamar " . ($model->room->room_number ?? 'Unknown') . " - " . ($model->item_name ?? 'Unknown');
                break;
            case 'Loan':
                $identifier = "Pinjaman " . ($model->type === 'payable' ? 'Hutang' : 'Piutang') . " - " . ($model->name ?? 'Unknown');
                break;
            case 'LoanRepayment':
                $identifier = "Pelunasan " . ($model->type === 'payable' ? 'Hutang' : 'Piutang') . " Rp " . number_format($model->amount ?? 0, 0, ',', '.');
                break;
        }

        return "Data {$identifier}";
    }
}
