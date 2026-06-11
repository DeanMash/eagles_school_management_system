<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Http\Requests\Student\StudentRecordCreateRequest;
use App\Http\Requests\Student\StudentRecordUpdateRequest;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentRecordController extends Controller
{
    protected $loc, $my_class, $user, $student;

    public function __construct(LocationRepo $loc, MyClassRepo $my_class, UserRepo $user, StudentRepo $student)
    {
        $this->middleware('teamSA', ['only' => ['edit','update', 'reset_pass', 'create', 'store', 'graduated', 'index', 'not_graduated']]);
        $this->middleware('super_admin', ['only' => ['destroy']]);

        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->user = $user;
        $this->student = $student;
    }

    public function index()
    {
        // Get all active students with their user information
        $data['students'] = $this->student->getAll()->with(['my_class', 'section'])->get()->sortBy('user.name');
        $data['my_classes'] = $this->my_class->all();
        
        return view('pages.support_team.students.index', $data);
    }

    public function reset_pass($st_id)
    {
        if(!$st_id){return Qs::goWithDanger();}

        $data['password'] = Hash::make('student');
        $this->user->update($st_id, $data);
        return back()->with('flash_success', __('msg.p_reset'));
    }

    public function create()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        
        // Add Zimbabwean provinces for your form
        $data['provinces'] = $this->getZimbabweanProvinces();
        
        // Add medical conditions and allergies for your form
        $data['medical_conditions'] = ['Asthma', 'Diabetes', 'Epilepsy', 'Hypertension', 'Allergies', 'HIV/AIDS', 'None', 'Other'];
        $data['allergies'] = ['None', 'Peanuts', 'Dairy', 'Eggs', 'Seafood', 'Pollen', 'Dust', 'Medications', 'Other'];
        
        return view('pages.support_team.students.add', $data);
    }

    public function store(StudentRecordCreateRequest $req)
    {
        try {
            // Get the validated data
            $validated = $req->validated();
            
            // Handle medical conditions and allergies arrays
            $userRecordData = $req->only(Qs::getUserRecord());
            $studentData = $req->only(Qs::getStudentData());
            
            // Process medical conditions
            if ($req->has('medical_conditions') && is_array($req->medical_conditions)) {
                $cleanedMedical = array_filter($req->medical_conditions, function($value) {
                    return !empty($value) && trim($value) !== '';
                });
                if (!empty($cleanedMedical)) {
                    $userRecordData['medical_conditions'] = implode(',', $cleanedMedical);
                }
            }
            
            // Process allergies
            if ($req->has('allergies') && is_array($req->allergies)) {
                $cleanedAllergies = array_filter($req->allergies, function($value) {
                    return !empty($value) && trim($value) !== '';
                });
                if (!empty($cleanedAllergies)) {
                    $userRecordData['allergies'] = implode(',', $cleanedAllergies);
                }
            }
            
            // Add other fields
            $userRecordData['emergency_contact_name'] = $req->emergency_contact_name ?? null;
            $userRecordData['emergency_contact_phone'] = $req->emergency_contact_phone ?? null;
            $userRecordData['emergency_contact_relationship'] = $req->emergency_contact_relationship ?? null;
            $userRecordData['province'] = $req->province ?? null;
            $userRecordData['district'] = $req->district ?? null;
            $userRecordData['nationality'] = $req->nationality ?? 'Zimbabwean';
            
            // Get class type code with error handling
            $classType = $this->my_class->findTypeByClass($req->my_class_id);
            if (!$classType) {
                return back()->withInput()->with('flash_danger', 'Class type not found. Please select a valid class.');
            }
            $ct = $classType->code ?? 'GEN';
            
            // Prepare user data
            $userRecordData['user_type'] = 'student';
            $userRecordData['name'] = ucwords($req->name);
            $userRecordData['code'] = strtoupper(Str::random(10));
            
            // Generate password - default is 'student'
            $defaultPassword = 'student';
            $userRecordData['password'] = Hash::make($defaultPassword);
            $userRecordData['photo'] = Qs::getDefaultUserImage();
            
            // Generate username
            $adm_no = $req->adm_no;
            $appCode = Qs::getAppCode() ?? 'EAGLES';
            
            // Extract year from admission_date if provided, otherwise use year_admitted or current year
            if ($req->has('admission_date') && $req->admission_date) {
                $year_admitted = date('Y', strtotime($req->admission_date));
                $studentData['admission_date'] = $req->admission_date;
            } else {
                $year_admitted = $studentData['year_admitted'] ?? date('Y');
            }
            
            // Set year_admitted for backward compatibility
            $studentData['year_admitted'] = $year_admitted;
            
            // Generate unique admission number and username
            if ($adm_no) {
                $adm_number = $adm_no;
                $proposedUsername = strtoupper($appCode.'/'.$ct.'/'.$year_admitted.'/'.$adm_number);
                
                // Check if the user-provided admission number already exists
                if (\App\Models\User::where('username', $proposedUsername)->exists()) {
                    return back()->withInput()->with('flash_danger', 'A student with admission number "' . $adm_no . '" already exists for this class and year. Please use a different admission number.');
                }
            } else {
                // Generate a unique admission number
                $maxAttempts = 100;
                $attempt = 0;
                do {
                    $adm_number = str_pad(mt_rand(1, 99999), 4, '0', STR_PAD_LEFT);
                    $proposedUsername = strtoupper($appCode.'/'.$ct.'/'.$year_admitted.'/'.$adm_number);
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        return back()->withInput()->with('flash_danger', 'Unable to generate a unique admission number. Please try again or provide a manual admission number.');
                    }
                } while (\App\Models\User::where('username', $proposedUsername)->exists());
            }
            
            $userRecordData['username'] = strtoupper($appCode.'/'.$ct.'/'.$year_admitted.'/'.$adm_number);

            // Handle photo upload
            if($req->hasFile('photo')) {
                try {
                    $photo = $req->file('photo');
                    $f = Qs::getFileMetaData($photo);
                    $f['name'] = 'photo.' . $f['ext'];
                    $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$userRecordData['code'], $f['name']);
                    $userRecordData['photo'] = asset('storage/' . $f['path']);
                } catch (\Exception $e) {
                    // Continue with default photo if upload fails
                    \Log::warning('Photo upload failed: ' . $e->getMessage());
                }
            }

            // Create User
            $user = $this->user->create($userRecordData);
            
            if (!$user || !$user->id) {
                return back()->withInput()->with('flash_danger', 'Failed to create user account. Please try again.');
            }

            // Prepare student record data
            $studentData['adm_no'] = $userRecordData['username'];
            $studentData['user_id'] = $user->id;
            $studentData['session'] = Qs::getSetting('current_session');
            
            // Ensure section_id is null if empty or not provided
            if (empty($studentData['section_id'])) {
                $studentData['section_id'] = null;
            }

            // Create Student Record
            $this->student->createRecord($studentData);
            
            // Prepare login information to display
            $loginInfo = [
                'username' => $userRecordData['username'],
                'password' => $defaultPassword,
                'name' => $userRecordData['name']
            ];
            
            return redirect()->route('students.index')
                ->with('flash_success', 'Student registered successfully!')
                ->with('student_login_info', $loginInfo);
                
        } catch (\Exception $e) {
            \Log::error('Student creation error: ' . $e->getMessage());
            return back()->withInput()->with('flash_danger', 'An error occurred while creating the student: ' . $e->getMessage());
        }
    }

    public function listByClass($class_id)
    {
        $data['my_class'] = $mc = $this->my_class->getMC(['id' => $class_id])->first();
        $data['students'] = $this->student->findStudentsByClass($class_id);
        $data['sections'] = $this->my_class->getClassSections($class_id);

        return is_null($mc) ? Qs::goWithDanger() : view('pages.support_team.students.list', $data);
    }

    public function graduated()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['students'] = $this->student->allGradStudents();

        return view('pages.support_team.students.graduated', $data);
    }

    public function not_graduated($sr_id)
    {
        if(!$sr_id){return Qs::goWithDanger();}

        $d['grad'] = 0;
        $d['grad_date'] = NULL;
        $d['session'] = Qs::getSetting('current_session');
        $this->student->updateRecord($sr_id, $d);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function show($sr_id)
    {
        if(!$sr_id){return Qs::goWithDanger();}

        $data['sr'] = $this->student->getRecord(['id' => $sr_id])->first();

        /* Prevent Other Students/Parents from viewing Profile of others */
        if(Auth::user()->id != $data['sr']->user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($data['sr']->user_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        return view('pages.support_team.students.show', $data);
    }

    public function edit($sr_id)
    {
        if(!$sr_id){return Qs::goWithDanger();}

        $data['sr'] = $this->student->getRecord(['id' => $sr_id])->first();
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        
        // Add Zimbabwean provinces
        $data['provinces'] = $this->getZimbabweanProvinces();
        
        // Parse medical conditions and allergies
        $data['medical_conditions'] = ['Asthma', 'Diabetes', 'Epilepsy', 'Hypertension', 'Allergies', 'HIV/AIDS', 'None', 'Other'];
        $data['selected_medical_conditions'] = $data['sr']->user->medical_conditions ? explode(',', $data['sr']->user->medical_conditions) : [];
        
        $data['allergies'] = ['None', 'Peanuts', 'Dairy', 'Eggs', 'Seafood', 'Pollen', 'Dust', 'Medications', 'Other'];
        $data['selected_allergies'] = $data['sr']->user->allergies ? explode(',', $data['sr']->user->allergies) : [];
        
        return view('pages.support_team.students.edit', $data);
    }

    public function update(StudentRecordUpdateRequest $req, $sr_id)
    {
        if(!$sr_id){return Qs::goWithDanger();}

        $sr = $this->student->getRecord(['id' => $sr_id])->first();
        $userRecordData = $req->only(Qs::getUserRecord());
        $userRecordData['name'] = ucwords($req->name);
        
        // Process medical conditions
        if ($req->has('medical_conditions') && is_array($req->medical_conditions)) {
            $cleanedMedical = array_filter($req->medical_conditions, function($value) {
                return !empty($value) && trim($value) !== '';
            });
            if (!empty($cleanedMedical)) {
                $userRecordData['medical_conditions'] = implode(',', $cleanedMedical);
            } else {
                $userRecordData['medical_conditions'] = null;
            }
        }
        
        // Process allergies
        if ($req->has('allergies') && is_array($req->allergies)) {
            $cleanedAllergies = array_filter($req->allergies, function($value) {
                return !empty($value) && trim($value) !== '';
            });
            if (!empty($cleanedAllergies)) {
                $userRecordData['allergies'] = implode(',', $cleanedAllergies);
            } else {
                $userRecordData['allergies'] = null;
            }
        }
        
        // Add other fields
        $userRecordData['emergency_contact_name'] = $req->emergency_contact_name ?? null;
        $userRecordData['emergency_contact_phone'] = $req->emergency_contact_phone ?? null;
        $userRecordData['emergency_contact_relationship'] = $req->emergency_contact_relationship ?? null;
        $userRecordData['province'] = $req->province ?? null;
        $userRecordData['district'] = $req->district ?? null;
        $userRecordData['nationality'] = $req->nationality ?? 'Zimbabwean';

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$sr->user->code, $f['name']);
            $userRecordData['photo'] = asset('storage/' . $f['path']);
        }

        $this->user->update($sr->user->id, $userRecordData); // Update User Details

        $studentData = $req->only(Qs::getStudentData());
        
        // Extract year from admission_date if provided
        if ($req->has('admission_date') && $req->admission_date) {
            $studentData['admission_date'] = $req->admission_date;
            $studentData['year_admitted'] = date('Y', strtotime($req->admission_date));
        } elseif ($req->has('year_admitted') && $req->year_admitted) {
            // Keep year_admitted if admission_date is not provided
            $studentData['year_admitted'] = $req->year_admitted;
        }

        $this->student->updateRecord($sr_id, $studentData); // Update St Rec

        /*** If Class/Section is Changed in Same Year, Delete Marks/ExamRecord of Previous Class/Section ****/
        Mk::deleteOldRecord($sr->user->id, $studentData['my_class_id']);

        return redirect()->route('students.show', Qs::hash($sr_id))->with('flash_success', 'Student updated successfully!');
    }

    public function destroy($st_id)
    {
        if(!$st_id){return Qs::goWithDanger();}

        $sr = $this->student->getRecord(['user_id' => $st_id])->first();
        $path = Qs::getUploadPath('student').$sr->user->code;
        Storage::exists($path) ? Storage::deleteDirectory($path) : false;
        $this->user->delete($sr->user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    // Add this method to handle AJAX requests for getting sections
    public function getSections($class_id)
    {
        $sections = $this->my_class->getClassSections($class_id);
        
        $options = '<option value="">Select Section (Optional)</option>';
        foreach ($sections as $section) {
            $options .= '<option value="' . $section->id . '">' . $section->name . '</option>';
        }
        
        return $options;
    }

    // Add this method to handle AJAX requests for getting districts
    public function getDistricts(Request $request)
    {
        $province = $request->get('province');
        $districts = $this->getZimbabweanDistricts($province);
        
        $options = '<option value="">Select District (Optional)</option>';
        foreach ($districts as $district) {
            $options .= '<option value="' . $district . '">' . $district . '</option>';
        }
        
        return response()->json(['success' => true, 'html' => $options]);
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
}