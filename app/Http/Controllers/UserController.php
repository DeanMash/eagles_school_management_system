<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\StudentRecord;
use App\Helpers\Qs;

class UserController extends Controller
{
    /**
     * Display user profile
     */
    public function show($id)
    {
        $user = User::findOrFail(Qs::decodeHash($id));
        
        // Check if current user can view this profile
        if (Auth::id() != $user->id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($user->id, Auth::id())) {
            abort(403, 'Unauthorized access');
        }
        
        // Redirect students to their student profile
        if ($user->user_type == 'student') {
            $studentRecord = StudentRecord::where('user_id', $user->id)->first();
            if ($studentRecord) {
                return redirect()->route('students.show', Qs::hash($studentRecord->id));
            }
        }
        
        // For non-students, show general user profile
        return view('users.show', compact('user'));
    }

    /**
     * Show current user's profile
     */
    public function profile()
    {
        $user = Auth::user();
        
        // Redirect students to their student profile
        if (Qs::userIsStudent()) {
            $studentRecord = StudentRecord::where('user_id', $user->id)->first();
            if ($studentRecord) {
                return redirect()->route('students.show', Qs::hash($studentRecord->id));
            }
        }
        
        // For non-students, show general profile
        return view('users.profile', compact('user'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('users.edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);
        
        $data = $request->only(['name', 'email', 'phone']);
        
        // Update password if provided
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'Current password is incorrect');
            }
            $data['password'] = Hash::make($request->new_password);
        }
        
        $user->update($data);
        
        return redirect()->route('profile')->with('success', 'Profile updated successfully');
    }

    /**
     * My Account settings (referenced in top_menu.blade.php)
     */
    public function myAccount()
    {
        $user = Auth::user();
        return view('users.my-account', compact('user'));
    }
}