<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AutoCarModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $years = Appointment::select(DB::raw('YEAR(appointment_date) as year'))
            ->whereNotNull('appointment_date')
            ->where('is_deleted', 0)
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('appointment.list', compact('years'));
    }

    public function create(Request $request)
    {
        // Fetch all models
         $user = Auth::user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id;
        $subAdminId = session('selectedSubAdminId');
        if($role=='sub-admin'){
        $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->where('branch_id',$userBranchId)
            ->get();   
        }elseif($subAdminId){
            $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->where('branch_id',$subAdminId)
            ->get();   
        }elseif($role=='staff'){
            $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->where('branch_id',$BranchId)
            ->get();   
        }else{
            $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->where('branch_id',$userBranchId)
            ->get();
        }

        return view('appointment.add', compact('modals'));
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user();
        $role = $user->role;
        $userBranchId = $user->id;
        $subAdminId = session('selectedSubAdminId');
        
        $appointment = Appointment::findOrFail($id);
       if($role=='sub-admin'){
        $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->where('branch_id',$userBranchId)
            ->get();   
        }elseif($subAdminId){
            $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->where('branch_id',$subAdminId)
            ->get();   
        }else{
            $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->where('branch_id',$userBranchId)
            ->get();   
        }

        return view('appointment.edit', compact('appointment', 'modals'));
    }

    public function view($id)
    {
        $appointment = Appointment::findOrFail($id);
        $modals = AutoCarModel::where('is_deleted', 0)
            ->select('id', 'model_name', 'model_brand')
            ->get();

        return view('appointment.view', compact('appointment', 'modals'));
    }
}
