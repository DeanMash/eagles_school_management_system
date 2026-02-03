<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use App\Repositories\StudentRepo;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $student;

    public function __construct(StudentRepo $student)
    {
        $this->middleware('auth');
        $this->middleware('parent');
        
        $this->student = $student;
    }

    public function index()
    {
        // Redirect to my_children page which shows parent's children
        return redirect()->route('my_children');
    }
}
