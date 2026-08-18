<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::orderBy('created_at', 'desc');

        if ($request->has('menu') && $request->menu !== '') {
            $query->where('menu', $request->menu);
        }

        $logs = $query->paginate(50)->withQueryString();
        $selectedMenu = $request->input('menu', '');

        // Ambil daftar menu unik yang ada di log untuk dropdown filter
        $menus = SystemLog::select('menu')->distinct()->pluck('menu');

        return view('system_logs.index', compact('logs', 'menus', 'selectedMenu'));
    }
}
