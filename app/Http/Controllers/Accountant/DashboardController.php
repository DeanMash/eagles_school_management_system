<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use App\Repositories\DormRepo;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $payment, $student, $dorm, $year;

    public function __construct(PaymentRepo $payment, StudentRepo $student, DormRepo $dorm)
    {
        $this->middleware('auth');
        $this->middleware('teamAccount');
        
        $this->payment = $payment;
        $this->student = $student;
        $this->dorm = $dorm;
        $this->year = Qs::getCurrentSession();
    }

    public function index()
    {
        $d = [];
        
        // Payment Statistics
        $currentYearPayments = $this->payment->getPayment(['year' => $this->year])->get();
        $allPayments = $this->payment->all();
        
        // Calculate payment statistics
        $d['total_payments'] = $allPayments->count();
        $d['current_year_payments'] = $currentYearPayments->count();
        $d['payment_years'] = $this->payment->getPaymentYears();
        
        // Get recent payment records (last 10)
        $d['recent_payments'] = \App\Models\PaymentRecord::with(['payment', 'student'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Calculate total amount collected this year
        $d['total_collected'] = \App\Models\PaymentRecord::where('year', $this->year)
            ->sum('amt_paid');
        
        // Calculate total amount due this year (sum of payment amounts)
        $paymentRecords = \App\Models\PaymentRecord::where('year', $this->year)
            ->with('payment')
            ->get();
        $d['total_due'] = $paymentRecords->sum(function($pr) {
            return $pr->payment->amount ?? 0;
        });
        
        // Hostel/Dormitory Statistics
        $d['total_dorms'] = $this->dorm->getAll()->count();
        $d['dorms'] = $this->dorm->getAll();
        
        // Get students with hostel assignments
        $d['students_in_hostels'] = \App\Models\StudentRecord::whereNotNull('dorm_id')
            ->with(['user', 'dorm'])
            ->count();
        
        // Get hostel occupancy by dorm
        $d['hostel_occupancy'] = \App\Models\StudentRecord::whereNotNull('dorm_id')
            ->with('dorm')
            ->get()
            ->groupBy('dorm_id')
            ->map(function($students, $dormId) {
                $dorm = \App\Models\Dorm::find($dormId);
                return [
                    'dorm_name' => $dorm ? $dorm->name : 'Unknown',
                    'count' => $students->count()
                ];
            });
        
        // Pending payments (unpaid or partially paid) - balance > 0
        $d['pending_payments'] = \App\Models\PaymentRecord::where('year', $this->year)
            ->where('balance', '>', 0)
            ->with(['payment', 'student'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('pages.accountant.dashboard', $d);
    }
}
