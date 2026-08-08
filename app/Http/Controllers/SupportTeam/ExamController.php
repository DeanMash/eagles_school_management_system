<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\Exam\ExamCreate;
use App\Http\Requests\Exam\ExamUpdate;
use App\Models\ExamRecord;
use App\Models\Mark;
use App\Models\TimeTableRecord;
use App\Repositories\ExamRepo;
use App\Http\Controllers\Controller;

class ExamController extends Controller
{
    protected $exam;
    public function __construct(ExamRepo $exam)
    {
        $this->middleware('teamSA', ['except' => ['destroy',] ]);
        $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->exam = $exam;
    }

    public function index()
    {
        $d['exams'] = $this->exam->all();
        return view('pages.support_team.exams.index', $d);
    }

    public function store(ExamCreate $req)
    {
        $data = $req->only(['name', 'term']);
        $data['year'] = Qs::getSetting('current_session');

        $this->exam->create($data);
        return back()->with('flash_success', __('msg.store_ok'));
    }

    public function edit($id)
    {
        $d['ex'] = $this->exam->find($id);
        return view('pages.support_team.exams.edit', $d);
    }

    public function update(ExamUpdate $req, $id)
    {
        $data = $req->only(['name', 'term']);

        $this->exam->update($id, $data);
        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function destroy($id)
    {
        // marks / exam_records / exam time_table_records FK ON DELETE CASCADE —
        // refuse while related rows exist so deleting an exam cannot wipe
        // school-wide results or exam schedules.
        if (Mark::where('exam_id', $id)->exists()) {
            return back()->with('flash_danger', 'Cannot delete an exam that has marks. Clear marks first.');
        }
        if (ExamRecord::where('exam_id', $id)->exists()) {
            return back()->with('flash_danger', 'Cannot delete an exam that has exam records. Clear exam records first.');
        }
        if (TimeTableRecord::where('exam_id', $id)->exists()) {
            return back()->with('flash_danger', 'Cannot delete an exam that has exam timetables. Remove exam timetables first.');
        }

        $this->exam->delete($id);
        return back()->with('flash_success', __('msg.del_ok'));
    }
}
