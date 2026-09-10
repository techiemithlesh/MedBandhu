<?php

namespace App\Http\Controllers;

use App\Models\Ward;
use App\Support\Tenancy;
use Illuminate\View\View;

class BedBoardController extends Controller
{
    public function index(Tenancy $tenancy): View
    {
        $wards = Ward::where('branch_id', $tenancy->branchId())
            ->where('is_active', true)
            ->with(['beds' => fn ($q) => $q->where('is_active', true)->orderBy('bed_number'),
                'beds.currentAdmission.patient',
                'beds.currentAdmission.admittingDoctor'])
            ->orderBy('name')
            ->get();

        $all = $wards->flatMap->beds;

        return view('ipd.board', [
            'wards' => $wards,
            'summary' => [
                'total' => $all->count(),
                'available' => $all->where('status', 'available')->count(),
                'occupied' => $all->where('status', 'occupied')->count(),
                'cleaning' => $all->where('status', 'cleaning')->count(),
                'blocked' => $all->whereIn('status', ['blocked', 'reserved'])->count(),
            ],
        ]);
    }
}
