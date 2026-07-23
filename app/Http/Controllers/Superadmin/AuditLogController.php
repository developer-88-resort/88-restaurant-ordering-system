<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(): View
    {
        return view('superadmin.audit-logs.index', [
            'logs' => Activity::with('causer')->latest()->paginate(20),
        ]);
    }
}
