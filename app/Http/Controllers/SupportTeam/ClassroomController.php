<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamSA', ['except' => ['index']]);
    }

    public function index()
    {
        $classrooms = Classroom::orderBy('room_number')->get();
        return view('pages.support_team.classrooms.index', compact('classrooms'));
    }

    public function store(Request $req)
    {
        $req->validate([
            'room_number' => 'required|string|max:50|unique:classrooms,room_number',
            'capacity' => 'nullable|integer|min:1|max:500',
            'building' => 'nullable|string|max:100',
        ], [
            'room_number.required' => 'Room number is required.',
            'room_number.unique' => 'This room number already exists.',
        ]);

        Classroom::create([
            'room_number' => $req->room_number,
            'capacity' => $req->capacity ?? 30,
            'building' => $req->building,
        ]);

        return Qs::jsonStoreOk();
    }

    public function destroy($classroom_id)
    {
        Classroom::findOrFail($classroom_id)->delete();
        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
