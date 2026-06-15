@extends('layouts.master')
@section('page_title', 'Manage Users')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Users</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#new-user" class="nav-link active" data-toggle="tab">Create New User</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Manage Users</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($user_types as $ut)
                            <a href="#ut-{{ Qs::hash($ut->id) }}" class="dropdown-item" data-toggle="tab">{{ $ut->name }}s</a>
                        @endforeach
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="new-user">
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
                    
                    @if(session('flash_success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('flash_success') }}
                            @if(session('user_login_info'))
                                @php $loginInfo = session('user_login_info'); @endphp
                                <hr>
                                <strong>User Login Credentials:</strong><br>
                                <strong>Name:</strong> {{ $loginInfo['name'] }}<br>
                                <strong>Username:</strong> {{ $loginInfo['username'] }}<br>
                                <strong>Password:</strong> <code>{{ $loginInfo['password'] }}</code><br>
                                <small class="text-muted"><i class="icon-info"></i> Please save this information securely. The user will need these credentials to login.</small>
                            @endif
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    @if(session('flash_danger') || session('pop_error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('flash_danger') ?? session('pop_error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <form id="add-user-form" method="post" enctype="multipart/form-data" action="{{ route('users.store') }}" data-fouc>
                        @csrf
                        
                        <h6 class="font-weight-semibold mb-3">Personal Data</h6>
                        
                        <div class="form-section">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="user_type"> Select User: <span class="text-danger">*</span></label>
                                        <select required data-placeholder="Select User" class="form-control select" name="user_type" id="user_type">
                                @foreach($user_types as $ut)
                                    <option value="{{ Qs::hash($ut->id) }}">{{ $ut->name }}</option>
                                @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Full Name: <span class="text-danger">*</span></label>
                                        <input value="{{ old('name') }}" required type="text" name="name" placeholder="Full Name" class="form-control @error('name') is-invalid @enderror">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address: <span class="text-danger">*</span></label>
                                        <input value="{{ old('address') }}" class="form-control @error('address') is-invalid @enderror" placeholder="Address" name="address" type="text" required>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Email address: </label>
                                        <input value="{{ old('email') }}" type="email" name="email" class="form-control" placeholder="your@email.com">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Username: </label>
                                        <input value="{{ old('username') }}" type="text" name="username" class="form-control" placeholder="Username">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Phone:</label>
                                        <input value="{{ old('phone') }}" type="text" name="phone" class="form-control" placeholder="+2341234567" >
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Telephone:</label>
                                        <input value="{{ old('phone2') }}" type="text" name="phone2" class="form-control" placeholder="+2341234567" >
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date of Employment:</label>
                                        <input autocomplete="off" name="emp_date" value="{{ old('emp_date') }}" type="text" class="form-control date-pick" placeholder="Select Date...">

                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="password">Password: </label>
                                        <input id="password" type="password" name="password" class="form-control"  >
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gender">Gender: <span class="text-danger">*</span></label>
                                        <select class="select form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required data-fouc data-placeholder="Choose..">
                                            <option value=""></option>
                                            <option {{ (old('gender') == 'Male') ? 'selected' : '' }} value="Male">Male</option>
                                            <option {{ (old('gender') == 'Female') ? 'selected' : '' }} value="Female">Female</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="nal_id">Nationality:</label>
                                        <select data-placeholder="Choose (optional)..." name="nal_id" id="nal_id" class="select-search form-control @error('nal_id') is-invalid @enderror">
                                            <option value=""></option>
                                            @foreach($nationals as $nal)
                                                <option {{ (old('nal_id') == $nal->id ? 'selected' : '') }} value="{{ $nal->id }}">{{ $nal->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('nal_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{--Province--}}
                                <div class="col-md-4">
                                    <label for="province">Province:</label>
                                    <select onchange="getDistricts(this.value)" data-placeholder="Choose Province (Optional).." class="select-search form-control" name="province" id="province">
                                        <option value=""></option>
                                        @foreach($provinces as $key => $province)
                                            <option {{ (old('province') == $key ? 'selected' : '') }} value="{{ $key }}">{{ $province }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{--District--}}
                                <div class="col-md-4">
                                    <label for="district">District:</label>
                                    <select data-placeholder="Select Province First (Optional)" class="select-search form-control" name="district" id="district">
                                        <option value=""></option>
                                    </select>
                                </div>
                                {{--MEDICAL HISTORY--}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="medical_history">Medical History: </label>
                                        <textarea class="form-control" id="medical_history" name="medical_history" rows="3" data-fouc placeholder="Enter medical history or conditions">{{ old('medical_history') }}</textarea>
                                        <small class="form-text text-muted">Enter any medical conditions, allergies, or health information</small>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                {{--PASSPORT--}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="d-block">Upload Passport Photo:</label>
                                        <input value="{{ old('photo') }}" accept="image/*" type="file" name="photo" class="form-input-styled" data-fouc>
                                        <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 2Mb</span>
                                    </div>
                                </div>
                            </div>

                            {{--Teacher Assignment Fields (shown only when Teacher is selected)--}}
                            <div id="teacher-assignments" style="display: none;">
                                <hr class="my-4">
                                <h6 class="font-weight-semibold mb-3">Teacher Assignments (Optional)</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="section_id">Assign as Class Teacher:</label>
                                            <select class="form-control" name="section_id" id="section_id" data-placeholder="Select Class/Section (Optional)">
                                                <option value="">Select Class/Section (Optional)</option>
                                                @if(isset($sections))
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                            {{ $section->my_class->name }} - {{ $section->name }}
                                                            @if($section->teacher)
                                                                (Current: {{ $section->teacher->name }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <small class="form-text text-muted">Assign this teacher as class teacher to manage attendance for a class section</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="subject_ids">Assign Subjects:</label>
                                            <select class="form-control" name="subject_ids[]" id="subject_ids" multiple data-placeholder="Select Subjects (Optional)">
                                                <option value=""></option>
                                                @php
                                                    // Debug: Check if subjects exist
                                                    $subjectsCount = isset($subjects) ? $subjects->count() : 0;
                                                @endphp
                                                @if(isset($subjects) && $subjects->count() > 0)
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ (is_array(old('subject_ids')) && in_array($subject->id, old('subject_ids'))) ? 'selected' : '' }}>
                                                            {{ $subject->name }}@if(isset($subject->my_class) && $subject->my_class) ({{ $subject->my_class->name }})@endif
                                                            @if(isset($subject->teacher) && $subject->teacher)
                                                                (Current: {{ $subject->teacher->name }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>No subjects available ({{ $subjectsCount }} found). Please add subjects first.</option>
                                                @endif
                                            </select>
                                            <small class="form-text text-muted">Select one or more subjects this teacher can teach. You can select multiple subjects.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Single Submit Button --}}
                        <div class="text-right mt-4 mb-4">
                            <button type="submit" id="submit-user-btn" class="btn btn-primary btn-lg" data-text="Creating User">
                                <i class="icon-paperplane mr-2"></i> Create User
                            </button>
                        </div>

                    </form>
                </div>

                @foreach($user_types as $ut)
                    <div class="tab-pane fade" id="ut-{{Qs::hash($ut->id)}}">                         <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users->where('user_type', $ut->title) as $u)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $u->photo }}" alt="photo"></td>
                                    <td>{{ $u->name }}</td>
                                    <td>{{ $u->username }}</td>
                                    <td>{{ $u->phone }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-left">
                                                    {{--View Profile--}}
                                                    <a href="{{ route('users.show', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                                    {{--Edit--}}
                                                    <a href="{{ route('users.edit', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                @if(Qs::userIsSuperAdmin())

                                                        <a href="{{ route('users.reset_password', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-lock"></i> Reset password</a>
                                                        {{--Delete--}}
                                                        <a id="{{ Qs::hash($u->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                        <form method="post" id="item-delete-{{ Qs::hash($u->id) }}" action="{{ route('users.destroy', Qs::hash($u->id)) }}" class="hidden">@csrf @method('delete')</form>
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
                @endforeach

            </div>
        </div>
    </div>

    {{--Student List Ends--}}

@endsection

@section('scripts')
<style>
    /* Hide any wizard-generated buttons */
    #add-user-form .actions,
    #add-user-form .wizard-actions,
    #add-user-form .steps-actions {
        display: none !important;
    }
    
    /* Ensure our submit button is visible */
    #submit-user-btn {
        display: inline-block !important;
    }
</style>
<script>
function getDistricts(province) {
    if (!province) {
        $('#district').html('<option value="">Select Province First</option>');
        return;
    }
    
    $.ajax({
        url: '{{ route("users.get_districts") }}',
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

// Show/hide teacher assignment fields based on user type
function toggleTeacherAssignments() {
    var userTypeText = $('#user_type option:selected').text().toLowerCase().trim();
    
    if (userTypeText === 'teacher' || userTypeText.includes('teacher')) {
        $('#teacher-assignments').slideDown(function() {
            // Wait a bit for the slideDown animation to complete
            setTimeout(function() {
                // Destroy existing Select2 if it exists to avoid conflicts
                try {
                    if ($('#subject_ids').hasClass('select2-hidden-accessible')) {
                        $('#subject_ids').select2('destroy');
                    }
                } catch(e) {
                    // Select2 might not be initialized, that's okay
                }
                
                try {
                    if ($('#section_id').hasClass('select2-hidden-accessible')) {
                        $('#section_id').select2('destroy');
                    }
                } catch(e) {
                    // Select2 might not be initialized, that's okay
                }
                
                // Check if subjects dropdown has options
                var subjectOptions = $('#subject_ids option').length;
                var subjectOptionsWithValue = $('#subject_ids option[value!=""]').length;
                console.log('Total subject options:', subjectOptions);
                console.log('Subject options with values:', subjectOptionsWithValue);
                
                // Re-initialize Select2 with proper options
                $('#subject_ids').select2({
                    placeholder: "Select Subjects (Optional)",
                    allowClear: true,
                    width: '100%',
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: 0
                });
                
                $('#section_id').select2({
                    placeholder: "Select Class/Section (Optional)",
                    allowClear: true,
                    width: '100%',
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: 0
                });
                
                // Force Select2 to update and show options
                $('#subject_ids').trigger('change');
            }, 200);
        });
    } else {
        $('#teacher-assignments').slideUp();
        // Clear selections when hiding
        if ($('#section_id').hasClass('select2-hidden-accessible')) {
            $('#section_id').val(null).trigger('change');
        } else {
            $('#section_id').val(null);
        }
        if ($('#subject_ids').hasClass('select2-hidden-accessible')) {
            $('#subject_ids').val(null).trigger('change');
        } else {
            $('#subject_ids').val(null);
        }
    }
}

$(document).ready(function() {
    // Debug: Check if subjects are available in the DOM
    setTimeout(function() {
        var subjectCount = $('#subject_ids option').length;
        var subjectCountWithValue = $('#subject_ids option[value!=""]').length;
        console.log('On page load - Total options in subject_ids:', subjectCount);
        console.log('On page load - Options with values:', subjectCountWithValue);
        
        // Check on page load
        toggleTeacherAssignments();
        
        // Check when user type changes
        $('#user_type').on('change', function() {
            toggleTeacherAssignments();
        });
    }, 500);
    
    // Ensure form can submit properly - Single submit button only
    var form = $('#add-user-form');
    if (form.length) {
        var $btn = $('#submit-user-btn');
        
        // Store original button HTML for restoration after AJAX
        var originalBtnHtml = $btn.html();
        $btn.data('original-html', originalBtnHtml);
        $btn.data('text', 'Create User'); // For disableBtn function
        
        // Hide any wizard-generated buttons immediately and continuously
        function hideWizardButtons() {
            form.find('.actions').hide();
            form.find('.wizard-actions').hide();
            form.find('.steps-actions').hide();
            $('.actions').not('#submit-user-btn').hide();
        }
        hideWizardButtons();
        setInterval(hideWizardButtons, 500);
        
        // Override enableBtn to restore our button properly
        (function() {
            var originalEnableBtn = window.enableBtn;
            window.enableBtn = function(btn) {
                if (!btn || !btn.length) return;
                
                var btnId = btn.attr ? btn.attr('id') : null;
                if (btnId === 'submit-user-btn') {
                    // Restore original HTML for our specific button
                    var originalHtml = $btn.data('original-html') || originalBtnHtml;
                    $btn.prop('disabled', false).html(originalHtml);
                } else {
                    // Use original function for other buttons
                    if (originalEnableBtn && typeof originalEnableBtn === 'function') {
                        originalEnableBtn.call(this, btn);
                    } else {
                        btn.prop('disabled', false);
                    }
                }
            };
        })();
        
        // Ensure button click submits the form
        $btn.on('click', function(e) {
            e.preventDefault();
            console.log('Submit button clicked');
            
            // Trigger form submit (AJAX handler will catch it)
            form.submit();
        });
        
        // Ensure form submits via AJAX
        form.on('submit', function(e) {
            console.log('Form submitting via AJAX...');
            console.log('Form action:', form.attr('action'));
            console.log('Form has ajax-store class:', form.hasClass('ajax-store'));
            
            // Verify CSRF token is present
            if (!$('input[name="_token"]', this).length) {
                var token = $('meta[name="csrf-token"]').attr('content');
                if (token) {
                    $(this).append('<input type="hidden" name="_token" value="' + token + '">');
                }
            }
        });
        
        // Monitor AJAX completion for debugging
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.url && (settings.url.includes('users.store') || settings.url.includes('/users'))) {
                console.log('AJAX completed for user creation');
                console.log('Status:', xhr.status);
                console.log('Response:', xhr.responseJSON);
                
                // Re-enable button if AJAX handler didn't
                setTimeout(function() {
                    if ($btn.prop('disabled')) {
                        $btn.prop('disabled', false).html(originalBtnHtml);
                    }
                }, 1000);
            }
        });
    }
});
</script>
@endsection
