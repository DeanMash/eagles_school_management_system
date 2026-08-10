<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\UserRequest;
use App\Models\BookTransaction;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class UserController extends Controller
{
    protected $user, $loc, $my_class;

    public function __construct(UserRepo $user, LocationRepo $loc, MyClassRepo $my_class)
    {
        $this->middleware('teamSA', ['only' => ['index', 'store', 'edit', 'update'] ]);
        $this->middleware('super_admin', ['only' => ['reset_pass','destroy'] ]);

        $this->user = $user;
        $this->loc = $loc;
        $this->my_class = $my_class;
    }

    public function index()
    {
        $ut = $this->user->getAllTypes();
        $ut2 = $ut->where('level', '>', 2);
        
        // Remove duplicates by title - keep only the first occurrence of each type
        $userTypes = Qs::userIsAdmin() ? $ut2 : $ut;
        $uniqueTypes = $userTypes->unique(function ($item) {
            return strtolower($item->title);
        });
        $d['user_types'] = $uniqueTypes->values()->sortBy('level');

        $d['provinces'] = $this->getZimbabweanProvinces();
        $d['users'] = $this->user->getPTAUsers();
        $d['nationals'] = $this->loc->getAllNationals();
        // Add sections and subjects for teacher assignment
        $d['sections'] = $this->my_class->getAllSections();
        $d['subjects'] = $this->my_class->getAllSubjects();
        return view('pages.support_team.users.index', $d);
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

    public function edit($id)
    {
        $id = Qs::decodeHash($id);
        $user = $this->user->find($id);
        
        if (!$user) {
            return back()->with('flash_danger', 'User not found.');
        }
        
        $d['user'] = $user;
        $d['provinces'] = $this->getZimbabweanProvinces();
        $d['users'] = $this->user->getPTAUsers();
        $d['nationals'] = $this->loc->getAllNationals();
        return view('pages.support_team.users.edit', $d);
    }

    public function reset_pass($id)
    {
        // Redirect if Making Changes to Head of Super Admins
        if(Qs::headSA($id)){
            return back()->with('flash_danger', __('msg.denied'));
        }

        $data['password'] = Hash::make('user');
        $this->user->update($id, $data);
        return back()->with('flash_success', __('msg.pu_reset'));
    }

    public function store(UserRequest $req)
    {
        try {
            $userTypeModel = $this->user->findType($req->user_type);
            if (!$userTypeModel) {
                return back()->withInput()->with('flash_danger', 'Invalid user type selected.');
            }
            $user_type = $userTypeModel->title;

            $data = $req->except(array_merge(Qs::getStaffRecord(), ['section_id', 'subject_ids']));
            $data['name'] = ucwords($req->name);
            $data['user_type'] = $user_type;
            $data['nal_id'] = $req->nal_id ?: null;
            $data['photo'] = Qs::getDefaultUserImage();
            $data['code'] = strtoupper(Str::random(10));

            $user_is_staff = in_array($user_type, Qs::getStaff());
            $user_is_teamSA = in_array($user_type, Qs::getTeamSA());

            $emp_date = $req->emp_date ?: now();
            $staff_id = Qs::getAppCode() . '/STAFF/' . date('Y/m', strtotime($emp_date)) . '/' . mt_rand(1000, 9999);
            $data['username'] = $uname = ($user_is_teamSA) ? $req->username : $staff_id;

            $pass = $req->password ?: $user_type;
            $data['password'] = Hash::make($pass);

            if ($req->hasFile('photo')) {
                $photo = $req->file('photo');
                $f = Qs::getFileMetaData($photo);
                $f['name'] = 'photo.' . $f['ext'];
                $f['path'] = $photo->storeAs(Qs::getUploadPath($user_type) . $data['code'], $f['name']);
                $data['photo'] = asset('storage/' . $f['path']);
            }

            if (!$uname && !$req->email) {
                return back()->withInput()->with('flash_danger', __('msg.user_invalid'));
            }

            $user = $this->user->create($data);

            if (!$user || !$user->id) {
                return back()->withInput()->with('flash_danger', 'Failed to create user account. Please try again.');
            }

            if ($user_is_staff) {
                $d2 = $req->only(Qs::getStaffRecord());
                $d2['user_id'] = $user->id;
                $d2['code'] = $staff_id;
                $this->user->createStaffRecord($d2);
            }

            if ($user_type === 'teacher') {
                if ($req->section_id) {
                    $section = $this->my_class->findSection($req->section_id);
                    if ($section) {
                        $this->my_class->updateSection($req->section_id, ['teacher_id' => $user->id]);
                    }
                }
                if ($req->has('subject_ids') && is_array($req->subject_ids)) {
                    foreach ($req->subject_ids as $subject_id) {
                        $subject = $this->my_class->findSubject($subject_id);
                        if ($subject) {
                            $this->my_class->updateSubject($subject_id, ['teacher_id' => $user->id]);
                        }
                    }
                }
            }

            $loginInfo = [
                'username' => $uname,
                'password' => $pass,
                'name' => $data['name'],
            ];

            return redirect()->route('users.index')
                ->with('flash_success', 'User created successfully!')
                ->with('user_login_info', $loginInfo);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('User creation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()->with('flash_danger', 'An error occurred while creating the user: ' . $e->getMessage());
        }
    }

    public function update(UserRequest $req, $id)
    {
        $id = Qs::decodeHash($id);

        // Redirect if Making Changes to Head of Super Admins
        if(Qs::headSA($id)){
            return Qs::json(__('msg.denied'), FALSE);
        }

        $user = $this->user->find($id);

        $user_type = $user->user_type;
        $user_is_staff = in_array($user_type, Qs::getStaff());
        $user_is_teamSA = in_array($user_type, Qs::getTeamSA());

        $data = $req->except(Qs::getStaffRecord());
        $data['name'] = ucwords($req->name);
        $data['user_type'] = $user_type;

        if($user_is_staff && !$user_is_teamSA){
            $data['username'] = Qs::getAppCode().'/STAFF/'.date('Y/m', strtotime($req->emp_date)).'/'.mt_rand(1000, 9999);
        }
        else {
            $data['username'] = $user->username;
        }

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath($user_type).$user->code, $f['name']);
            $data['photo'] = asset('storage/' . $f['path']);
        }

        $this->user->update($id, $data);   /* UPDATE USER RECORD */

        /* UPDATE STAFF RECORD */
        if($user_is_staff){
            $d2 = $req->only(Qs::getStaffRecord());
            $d2['code'] = $data['username'];
            $this->user->updateStaffRecord(['user_id' => $id], $d2);
        }

        return Qs::jsonUpdateOk();
    }

    public function show($user_id)
    {
        $user_id = Qs::decodeHash($user_id);
        if(!$user_id){return back();}

        $data['user'] = $this->user->find($user_id);

        /* Prevent Other Students from viewing Profile of others*/
        if(Auth::user()->id != $user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild(Auth::user()->id, $user_id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        return view('pages.support_team.users.show', $data);
    }

    public function destroy($id)
    {
        $id = Qs::decodeHash($id);

        // Redirect if Making Changes to Head of Super Admins
        if(Qs::headSA($id)){
            return back()->with('pop_error', __('msg.denied'));
        }

        $user = $this->user->find($id);

        if($user->user_type == 'teacher' && $this->userTeachesSubject($user)) {
            return back()->with('pop_error', __('msg.del_teacher'));
        }

        // book_transactions.student_id / issued_by FK ON DELETE CASCADE — refuse while
        // outstanding loans exist so user delete cannot wipe other students' loans or
        // leave books.available_copies unrestored.
        if (BookTransaction::where('status', 'issued')
            ->where(function ($q) use ($id) {
                $q->where('student_id', $id)->orWhere('issued_by', $id);
            })->exists()) {
            return back()->with('pop_warning', 'Cannot delete a user with outstanding library loans (as borrower or issuer). Return or resolve the loans first.');
        }

        $path = Qs::getUploadPath($user->user_type).$user->code;
        Storage::exists($path) ? Storage::deleteDirectory($path) : true;
        $this->user->delete($user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    protected function userTeachesSubject($user)
    {
        $subjects = $this->my_class->findSubjectByTeacher($user->id);
        return ($subjects->count() > 0) ? true : false;
    }

}
