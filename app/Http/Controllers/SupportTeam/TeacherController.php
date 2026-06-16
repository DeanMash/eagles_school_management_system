<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\Teacher\TeacherCreateRequest;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    protected $user, $my_class;

    public function __construct(UserRepo $user, MyClassRepo $my_class)
    {
        $this->middleware('teamSA');
        $this->user = $user;
        $this->my_class = $my_class;
    }

    public function index()
    {
        $d['teachers'] = $this->user->getUserByType('teacher');
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = $this->my_class->getAllSections();
        $d['subjects'] = $this->my_class->getAllSubjects();
        $d['provinces'] = $this->getZimbabweanProvinces();
        
        return view('pages.support_team.teachers.index', $d);
    }

    private function getZimbabweanProvinces()
    {
        return [
            'Bulawayo' => 'Bulawayo',
            'Harare' => 'Harare',
            'Manicaland' => 'Manicaland',
            'Mashonaland Central' => 'Mashonaland Central',
            'Mashonaland East' => 'Mashonaland East',
            'Mashonaland West' => 'Mashonaland West',
            'Masvingo' => 'Masvingo',
            'Matabeleland North' => 'Matabeleland North',
            'Matabeleland South' => 'Matabeleland South',
            'Midlands' => 'Midlands'
        ];
    }

    public function getDistricts(Request $request)
    {
        $province = $request->get('province');
        $districts = $this->getZimbabweanDistricts($province);
        
        $options = '<option value="">Select District</option>';
        foreach ($districts as $district) {
            $options .= '<option value="' . $district . '">' . $district . '</option>';
        }
        
        return response()->json(['success' => true, 'html' => $options]);
    }

    private function getZimbabweanDistricts($province = null)
    {
        $districts = [
            'Bulawayo' => [
                'Bulawayo Central',
                'Bulawayo East',
                'Bulawayo North',
                'Bulawayo South',
                'Bulawayo West'
            ],
            'Harare' => [
                'Harare Central',
                'Harare East',
                'Harare North',
                'Harare South',
                'Harare West'
            ],
            'Manicaland' => [
                'Buhera',
                'Chimanimani',
                'Chipinge',
                'Makoni',
                'Mutare',
                'Mutasa',
                'Nyanga'
            ],
            'Mashonaland Central' => [
                'Bindura',
                'Guruve',
                'Mazowe',
                'Mbire',
                'Mount Darwin',
                'Muzarabani',
                'Rushinga',
                'Shamva'
            ],
            'Mashonaland East' => [
                'Chikomba',
                'Goromonzi',
                'Marondera',
                'Mudzi',
                'Murehwa',
                'Mutoko',
                'Seke',
                'Uzumba-Maramba-Pfungwe',
                'Wedza'
            ],
            'Mashonaland West' => [
                'Chegutu',
                'Hurungwe',
                'Kariba',
                'Makonde',
                'Mhangura',
                'Zvimba'
            ],
            'Masvingo' => [
                'Bikita',
                'Chiredzi',
                'Chivi',
                'Gutu',
                'Masvingo',
                'Mwenezi',
                'Zaka'
            ],
            'Matabeleland North' => [
                'Binga',
                'Bubi',
                'Hwange',
                'Lupane',
                'Nkayi',
                'Tsholotsho',
                'Umguza'
            ],
            'Matabeleland South' => [
                'Beitbridge',
                'Bulilima',
                'Gwanda',
                'Insiza',
                'Mangwe',
                'Matobo',
                'Umzingwane'
            ],
            'Midlands' => [
                'Chirumhanzu',
                'Gokwe North',
                'Gokwe South',
                'Gweru',
                'Kwekwe',
                'Mberengwa',
                'Shurugwi',
                'Zvishavane'
            ]
        ];
        
        if ($province && isset($districts[$province])) {
            return $districts[$province];
        }
        
        return [];
    }

    public function store(TeacherCreateRequest $req)
    {
        try {
            $userRecordData = $req->only(Qs::getUserRecord());
            $userRecordData['name'] = ucwords($req->name);
            $userRecordData['user_type'] = 'teacher';
            $userRecordData['photo'] = Qs::getDefaultUserImage();
            $userRecordData['code'] = strtoupper(Str::random(10));
            $userRecordData['province'] = $req->province ?? null;
            $userRecordData['district'] = $req->district ?? null;
            $userRecordData['medical_history'] = $req->medical_history ?? null;

            // Generate username if not provided
            if ($req->username) {
                $userRecordData['username'] = $req->username;
            } else {
                $username = strtolower(preg_replace('/\s+/', '', $req->name)) . mt_rand(100, 999);
                $maxAttempts = 100;
                $attempt = 0;
                while (\App\Models\User::where('username', $username)->exists()) {
                    $username = strtolower(preg_replace('/\s+/', '', $req->name)) . mt_rand(100, 999);
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        return back()->withInput()->with('flash_danger', 'Unable to generate a unique username. Please provide one manually.');
                    }
                }
                $userRecordData['username'] = $username;
            }

            $defaultPassword = 'teacher';
            $pass = $req->password ?: $defaultPassword;
            $userRecordData['password'] = Hash::make($pass);

            // Handle photo upload
            if ($req->hasFile('photo')) {
                try {
                    $photo = $req->file('photo');
                    $f = Qs::getFileMetaData($photo);
                    $f['name'] = 'photo.' . $f['ext'];
                    $f['path'] = $photo->storeAs(Qs::getUploadPath('teacher') . $userRecordData['code'], $f['name']);
                    $userRecordData['photo'] = asset('storage/' . $f['path']);
                } catch (\Exception $e) {
                    \Log::warning('Photo upload failed: ' . $e->getMessage());
                }
            }

            $user = $this->user->create($userRecordData);

            if (!$user || !$user->id) {
                return back()->withInput()->with('flash_danger', 'Failed to create teacher account. Please try again.');
            }

            // Assign class teacher if section is selected
            if ($req->section_id) {
                $section = $this->my_class->findSection($req->section_id);
                if ($section) {
                    $this->my_class->updateSection($req->section_id, ['teacher_id' => $user->id]);
                }
            }

            // Assign subjects if selected
            if ($req->has('subject_ids') && is_array($req->subject_ids)) {
                foreach ($req->subject_ids as $subject_id) {
                    $subject = $this->my_class->findSubject($subject_id);
                    if ($subject) {
                        $this->my_class->updateSubject($subject_id, ['teacher_id' => $user->id]);
                    }
                }
            }

            $loginInfo = [
                'username' => $userRecordData['username'],
                'password' => $pass,
                'name' => $userRecordData['name'],
            ];

            return redirect()->route('teachers.index')
                ->with('flash_success', 'Teacher registered successfully!')
                ->with('user_login_info', $loginInfo);
        } catch (\Exception $e) {
            \Log::error('Teacher creation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()->with('flash_danger', 'An error occurred while creating the teacher: ' . $e->getMessage());
        }
    }

    public function assignClassTeacher(Request $req)
    {
        $req->validate([
            'section_id' => 'required|exists:sections,id',
            'teacher_id' => 'required',
        ]);

        $teacher_id = Qs::decodeHash($req->teacher_id);
        if (!\App\Models\User::where('id', $teacher_id)->where('user_type', 'teacher')->exists()) {
            return back()->with('flash_danger', 'Teacher not found');
        }

        $section = $this->my_class->findSection($req->section_id);
        if (!$section) {
            return back()->with('flash_danger', 'Section not found');
        }

        $this->my_class->updateSection($req->section_id, ['teacher_id' => $teacher_id]);

        return back()->with('flash_success', 'Class teacher assigned successfully!');
    }

    public function assignSubject(Request $req)
    {
        $req->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required',
        ]);

        $teacher_id = Qs::decodeHash($req->teacher_id);
        if (!\App\Models\User::where('id', $teacher_id)->where('user_type', 'teacher')->exists()) {
            return back()->with('flash_danger', 'Teacher not found');
        }

        $subject = $this->my_class->findSubject($req->subject_id);
        if (!$subject) {
            return back()->with('flash_danger', 'Subject not found');
        }

        $this->my_class->updateSubject($req->subject_id, ['teacher_id' => $teacher_id]);

        return back()->with('flash_success', 'Subject assigned to teacher successfully!');
    }

    public function show($id)
    {
        $teacher = $this->user->find($id);
        
        if (!$teacher || $teacher->user_type !== 'teacher') {
            return Qs::goWithDanger('teachers.index');
        }

        $d['teacher'] = $teacher;
        $d['sections'] = $this->my_class->getAllSections()->filter(function($section) use ($teacher) {
            return $section->teacher_id == $teacher->id;
        });
        $d['subjects'] = $this->my_class->getAllSubjects()->filter(function($subject) use ($teacher) {
            return $subject->teacher_id == $teacher->id;
        });
        
        return view('pages.support_team.teachers.show', $d);
    }

    public function edit($id)
    {
        $d['teacher'] = $this->user->find($id);
        
        if (!$d['teacher'] || $d['teacher']->user_type !== 'teacher') {
            return Qs::goWithDanger('teachers.index');
        }
        
        return view('pages.support_team.teachers.edit', $d);
    }

    public function update(Request $req, $id)
    {
        $req->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:Male,Female',
            'address' => 'nullable|string',
            'province' => 'required|string',
            'district' => 'required|string',
            'medical_history' => 'nullable|string',
            'username' => 'nullable|string|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = $req->only(['name', 'email', 'phone', 'gender', 'address', 'province', 'district', 'medical_history']);
        $data['name'] = ucwords($req->name);

        if ($req->username) {
            $data['username'] = $req->username;
        }

        if ($req->password) {
            $data['password'] = Hash::make($req->password);
        }

        // Handle photo upload
        if($req->hasFile('photo')) {
            try {
                $photo = $req->file('photo');
                $f = Qs::getFileMetaData($photo);
                $f['name'] = 'photo.' . $f['ext'];
                $f['path'] = $photo->storeAs(Qs::getUploadPath('teacher').$this->user->find($id)->code, $f['name']);
                $data['photo'] = asset('storage/' . $f['path']);
            } catch (\Exception $e) {
                \Log::warning('Photo upload failed: ' . $e->getMessage());
            }
        }

        $this->user->update($id, $data);

        return back()->with('flash_success', 'Teacher updated successfully!');
    }

    public function destroy($id)
    {
        $teacher = $this->user->find($id);
        
        if (!$teacher || $teacher->user_type !== 'teacher') {
            return back()->with('flash_danger', 'Teacher not found');
        }

        // Check if teacher is assigned to any sections or subjects
        $sectionsCount = \App\Models\Section::where('teacher_id', $teacher->id)->count();
        $subjectsCount = \App\Models\Subject::where('teacher_id', $teacher->id)->count();

        if ($sectionsCount > 0 || $subjectsCount > 0) {
            return back()->with('flash_danger', 'Cannot delete teacher. They are assigned to ' . ($sectionsCount + $subjectsCount) . ' class(es) or subject(s). Please reassign them first.');
        }

        $this->user->delete($id);
        return back()->with('flash_success', 'Teacher deleted successfully!');
    }
}
