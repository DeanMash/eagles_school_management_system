<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Helpers\Qs;
use App\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $user;
    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }

    /**
     * Redirect users based on their user_type
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToDashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Debug - remove in production
        // \Log::info('Redirecting user', ['user_id' => $user->id, 'user_type' => $user->user_type]);
        
        switch ($user->user_type) {
            case 'student':
                return redirect()->route('student.dashboard');
                
            case 'teacher':
                return redirect()->route('teacher.dashboard');
                
            case 'admin':
            case 'super_admin':
                return redirect()->route('admin.dashboard');
                
            case 'accountant':
                return redirect()->route('accountant.dashboard');
                
            case 'librarian':
                return redirect()->route('librarian.dashboard');
                
            case 'parent':
                return redirect()->route('my_children');
                
            default:
                // If user type is not recognized, show dashboard with warning
                return redirect()->route('dashboard')->with('flash_warning', 
                    'Your account type (' . ($user->user_type ?? 'Unknown') . ') is not properly configured.');
        }
    }

    public function index()
    {
        // If user is logged in, redirect to appropriate dashboard
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        
        // If not logged in, show landing page or login
        $users = \App\Models\User::all();
        $todayEvents = Event::today()->public()->get();
        $upcomingEvents = Event::upcoming()->public()->limit(5)->get();
        
        return view('support.dashboard', compact('users', 'todayEvents', 'upcomingEvents'));
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.terms_of_use', $data);
    }

    public function dashboard()
    {
        $d=[];
        if(Qs::userIsTeamSAT()){
            $d['users'] = $this->user->getAll();
        }

        return view('pages.support_team.dashboard', $d);
    }
}