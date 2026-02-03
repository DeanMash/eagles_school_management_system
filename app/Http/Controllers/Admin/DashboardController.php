<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use App\Repositories\UserRepo;
use App\Repositories\StudentRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $user, $student, $my_class;

    public function __construct(UserRepo $user, StudentRepo $student, MyClassRepo $my_class)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        
        $this->user = $user;
        $this->student = $student;
        $this->my_class = $my_class;
    }

    public function index()
    {
        $d = [];
        
        // Get all users for admin overview
        if(Qs::userIsTeamSAT()){
            $d['users'] = $this->user->getAll();
        }
        
        // Get statistics
        $d['total_students'] = $this->student->activeStudents()->count();
        $d['total_classes'] = $this->my_class->all()->count();
        $d['total_users'] = \App\Models\User::count();
        
        // Get current session
        $d['current_session'] = Qs::getSetting('current_session');
        
        // Get recent students (last 10)
        $d['recent_students'] = \App\Models\StudentRecord::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('pages.support_team.dashboard', $d);
    }
}
