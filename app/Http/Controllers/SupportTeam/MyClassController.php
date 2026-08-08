<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\MyClass\ClassCreate;
use App\Http\Requests\MyClass\ClassUpdate;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\StudentRecord;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;

class MyClassController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->middleware('teamSA', ['except' => ['destroy',] ]);
        $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index()
    {
        $d['my_classes'] = $this->my_class->all();
        $d['class_types'] = $this->my_class->getTypes();

        return view('pages.support_team.classes.index', $d);
    }

    public function store(ClassCreate $req)
    {
        $data = $req->all();
        $mc = $this->my_class->create($data);

        // Create Default Section
        $s =['my_class_id' => $mc->id,
            'name' => 'A',
            'active' => 1,
            'teacher_id' => NULL,
        ];

        $this->my_class->createSection($s);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $d['c'] = $c = $this->my_class->find($id);
        $d['teachers'] = $this->user->getUserByType('teacher');
        $d['sections'] = $this->my_class->getClassSections($id);

        return is_null($c) ? Qs::goWithDanger('classes.index') : view('pages.support_team.classes.edit', $d) ;
    }

    public function update(ClassUpdate $req, $id)
    {
        $data = $req->only(['name']);
        $this->my_class->update($id, $data);

        if ($req->has('section_teachers') && is_array($req->section_teachers)) {
            foreach ($req->section_teachers as $section_id => $teacher_id) {
                $tid = $teacher_id ? Qs::decodeHash($teacher_id) : null;
                if ($section_id && is_numeric($section_id)) {
                    $this->my_class->updateSection($section_id, ['teacher_id' => $tid]);
                }
            }
        }

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        // student_records / marks / class payments FK ON DELETE CASCADE — refuse
        // while related rows exist so a class delete cannot wipe enrollments or fees.
        if (StudentRecord::where('my_class_id', $id)->exists()) {
            return back()->with('pop_warning', 'Cannot delete a class that has student records. Reassign or remove students first.');
        }
        if (Mark::where('my_class_id', $id)->exists()) {
            return back()->with('pop_warning', 'Cannot delete a class that has exam marks. Clear marks first.');
        }
        if (Payment::where('my_class_id', $id)->exists()) {
            return back()->with('pop_warning', 'Cannot delete a class that has fee payments. Remove class fees first.');
        }

        $this->my_class->delete($id);
        return back()->with('flash_success', __('msg.del_ok'));
    }

}
