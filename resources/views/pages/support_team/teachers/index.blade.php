@extends('layouts.master')
@section('page_title', 'Manage Teachers')
@section('content')

@if(session('flash_success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('flash_success') }}
        @if(session('user_login_info'))
            @php $loginInfo = session('user_login_info'); @endphp
            <hr>
            <strong>Teacher Login Credentials:</strong><br>
            <strong>Name:</strong> {{ $loginInfo['name'] }}<br>
            <strong>Username:</strong> {{ $loginInfo['username'] }}<br>
            <strong>Password:</strong> <code>{{ $loginInfo['password'] }}</code><br>
            <small class="text-muted"><i class="icon-info"></i> Please save this information securely. The teacher will need these credentials to login.</small>
        @endif
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('flash_danger'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('flash_danger') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Teachers</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#add-teacher" class="nav-link active" data-toggle="tab">Add Teacher</a></li>
            <li class="nav-item"><a href="#all-teachers" class="nav-link" data-toggle="tab">All Teachers</a></li>
            <li class="nav-item"><a href="#assign-class-teacher" class="nav-link" data-toggle="tab">Assign Class Teacher</a></li>
            <li class="nav-item"><a href="#assign-subject" class="nav-link" data-toggle="tab">Assign Subject</a></li>
        </ul>

        <div class="tab-content">
            <!-- Add Teacher Tab -->
            <div class="tab-pane fade show active" id="add-teacher">
                <div class="row">
                    <div class="col-md-8">
                        <form id="add-teacher-form" method="post" action="{{ route('teachers.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Full Name <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input name="name" value="{{ old('name') }}" required type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Full Name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Email</label>
                                <div class="col-lg-9">
                                    <input name="email" value="{{ old('email') }}" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@example.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Phone</label>
                                <div class="col-lg-9">
                                    <input name="phone" value="{{ old('phone') }}" type="text" class="form-control" placeholder="Phone Number">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Gender <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <select class="form-control select @error('gender') is-invalid @enderror" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Address</label>
                                <div class="col-lg-9">
                                    <textarea name="address" class="form-control" rows="3" placeholder="Address">{{ old('address') }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Province</label>
                                <div class="col-lg-9">
                                    <select onchange="getDistricts(this.value)" class="form-control select" name="province" id="province">
                                        <option value="">Select Province (Optional)</option>
                                        @foreach($provinces as $key => $province)
                                            <option value="{{ $key }}" {{ old('province') == $key ? 'selected' : '' }}>{{ $province }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">District</label>
                                <div class="col-lg-9">
                                    <select class="form-control select" name="district" id="district">
                                        <option value="">Select Province First (Optional)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Medical History</label>
                                <div class="col-lg-9">
                                    <textarea name="medical_history" class="form-control" rows="3" placeholder="Enter medical history or conditions">{{ old('medical_history') }}</textarea>
                                    <small class="form-text text-muted">Enter any medical conditions, allergies, or health information</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Username</label>
                                <div class="col-lg-9">
                                    <input name="username" value="{{ old('username') }}" type="text" class="form-control @error('username') is-invalid @enderror" placeholder="Leave blank to auto-generate">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">If left blank, username will be auto-generated</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Password</label>
                                <div class="col-lg-9">
                                    <input name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank for default: 'teacher'">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Default password is 'teacher' if left blank</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Photo</label>
                                <div class="col-lg-9">
                                    <input name="photo" type="file" class="form-control-file" accept="image/*">
                                    <small class="form-text text-muted">Optional: JPG, PNG, GIF (Max: 2MB)</small>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="font-weight-semibold mb-3">Assignments (Optional)</h6>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Assign as Class Teacher</label>
                                <div class="col-lg-9">
                                    <select class="form-control select" name="section_id" id="section_id" data-placeholder="Select Class/Section (Optional)">
                                        <option value="">Select Class/Section (Optional)</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                {{ $section->my_class->name }} - {{ $section->name }}
                                                @if($section->teacher)
                                                    (Current: {{ $section->teacher->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Assign this teacher as class teacher to manage attendance for a class section</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Assign Subjects</label>
                                <div class="col-lg-9">
                                    <select class="form-control select" name="subject_ids[]" id="subject_ids" multiple data-placeholder="Select Subjects (Optional)">
                                        <option value=""></option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ (is_array(old('subject_ids')) && in_array($subject->id, old('subject_ids'))) ? 'selected' : '' }}>
                                                {{ $subject->name }} ({{ $subject->my_class->name }})
                                                @if($subject->teacher)
                                                    (Current: {{ $subject->teacher->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select one or more subjects this teacher can teach. You can select multiple subjects.</small>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" id="submit-teacher-btn" class="btn btn-primary">
                                    <span id="submit-text">Add Teacher</span>
                                    <i class="icon-paperplane ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- All Teachers Tab -->
            <div class="tab-pane fade" id="all-teachers">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Username</th>
                            <th>Class Teacher</th>
                            <th>Subjects</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $teacher->photo }}" alt="photo"></td>
                                <td>{{ $teacher->name }}</td>
                                <td>{{ $teacher->email ?? 'N/A' }}</td>
                                <td>{{ $teacher->phone ?? 'N/A' }}</td>
                                <td><code>{{ $teacher->username }}</code></td>
                                <td>
                                    @php
                                        $classTeacherSections = \App\Models\Section::where('teacher_id', $teacher->id)->with('my_class')->get();
                                    @endphp
                                    @if($classTeacherSections->count() > 0)
                                        @foreach($classTeacherSections as $section)
                                            <span class="badge badge-info">{{ $section->my_class->name }} - {{ $section->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $teacherSubjects = \App\Models\Subject::where('teacher_id', $teacher->id)->with('my_class')->get();
                                    @endphp
                                    @if($teacherSubjects->count() > 0)
                                        @foreach($teacherSubjects as $subject)
                                            <span class="badge badge-success">{{ $subject->name }} ({{ $subject->my_class->name }})</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-left">
                                                <a href="{{ route('teachers.show', Qs::hash($teacher->id)) }}" class="dropdown-item">
                                                    <i class="icon-eye"></i> View Details
                                                </a>
                                                <a href="{{ route('teachers.edit', Qs::hash($teacher->id)) }}" class="dropdown-item">
                                                    <i class="icon-pencil"></i> Edit
                                                </a>
                                                @if(Qs::userIsSuperAdmin())
                                                    <a id="{{ Qs::hash($teacher->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item">
                                                        <i class="icon-trash"></i> Delete
                                                    </a>
                                                    <form method="post" id="item-delete-{{ Qs::hash($teacher->id) }}" action="{{ route('teachers.destroy', Qs::hash($teacher->id)) }}" class="hidden">
                                                        @csrf @method('delete')
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Assign Class Teacher Tab -->
            <div class="tab-pane fade" id="assign-class-teacher">
                <div class="row">
                    <div class="col-md-6">
                        <form method="post" action="{{ route('teachers.assign_class_teacher') }}">
                            @csrf
                            <div class="form-group">
                                <label>Select Section <span class="text-danger">*</span></label>
                                <select required class="form-control select-search" name="section_id" id="section_id">
                                    <option value="">Select Section</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">
                                            {{ $section->my_class->name }} - {{ $section->name }}
                                            @if($section->teacher)
                                                (Current: {{ $section->teacher->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Select Teacher <span class="text-danger">*</span></label>
                                <select required class="form-control select-search" name="teacher_id" id="teacher_id">
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ Qs::hash($teacher->id) }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Assign Class Teacher <i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Assign Subject Tab -->
            <div class="tab-pane fade" id="assign-subject">
                <div class="row">
                    <div class="col-md-6">
                        <form method="post" action="{{ route('teachers.assign_subject') }}">
                            @csrf
                            <div class="form-group">
                                <label>Select Subject <span class="text-danger">*</span></label>
                                <select required class="form-control select-search" name="subject_id" id="subject_id">
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">
                                            {{ $subject->name }} ({{ $subject->my_class->name }})
                                            @if($subject->teacher)
                                                (Current: {{ $subject->teacher->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Select Teacher <span class="text-danger">*</span></label>
                                <select required class="form-control select-search" name="teacher_id" id="teacher_id_subject">
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ Qs::hash($teacher->id) }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Assign Subject <i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for multi-select subjects
    $('#subject_ids').select2({
        placeholder: "Select Subjects (Optional)",
        allowClear: true,
        width: '100%'
    });
    
    // Initialize Select2 for class teacher assignment
    $('#section_id').select2({
        placeholder: "Select Class/Section (Optional)",
        allowClear: true,
        width: '100%'
    });
    
    // Handle form submission
    $('#add-teacher-form').on('submit', function(e) {
        // Show loading state
        var $btn = $('#submit-teacher-btn');
        var $text = $('#submit-text');
        var originalText = $text.text();
        
        $btn.prop('disabled', true);
        $text.text('Submitting...');
        
        // Allow form to submit normally
        // If there are validation errors, the form will reload with errors
        // If successful, the form will reload with success message
    });
});

function getDistricts(province) {
    if (!province) {
        $('#district').html('<option value="">Select Province First (Optional)</option>');
        return;
    }
    
    $.ajax({
        url: '{{ route("teachers.get_districts") }}',
        type: 'GET',
        data: { province: province },
        success: function(response) {
            if (response.success) {
                $('#district').html(response.html);
            }
        },
        error: function() {
            $('#district').html('<option value="">Error loading districts</option>');
        }
    });
}

// Load districts if province is already selected (on page reload with old input)
@if(old('province'))
    getDistricts('{{ old("province") }}');
    setTimeout(function() {
        $('#district').val('{{ old("district") }}');
    }, 500);
@endif
</script>
@endsection
