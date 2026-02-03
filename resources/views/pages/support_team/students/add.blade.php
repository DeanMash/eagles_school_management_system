@extends('layouts.master')
@section('page_title', 'Admit Student')
@section('content')
<div class="card">
    <div class="card-header bg-white header-elements-inline">
        <h6 class="card-title">Please fill The form Below To Admit A New Student</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        @if(session('flash_success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('flash_success') }}
                @if(session('student_login_info'))
                    @php $loginInfo = session('student_login_info'); @endphp
                    <hr>
                    <strong>Student Login Credentials:</strong><br>
                    <strong>Username:</strong> {{ $loginInfo['username'] }}<br>
                    <strong>Password:</strong> <code>{{ $loginInfo['password'] }}</code><br>
                    <small class="text-muted"><i class="icon-info"></i> Please save this information securely. The student will need these credentials to login.</small>
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
                <h6 class="alert-heading">Please fix the following errors:</h6>
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

        <form id="student-registration-form" method="POST" enctype="multipart/form-data" action="{{ route('students.store') }}">
            @csrf
            <h6>Personal data</h6>
            <div id="personal-data-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name: <span class="text-danger">*</span></label>
                            <input value="{{ old('name') }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Address: <span class="text-danger">*</span></label>
                            <input value="{{ old('address') }}" class="form-control" placeholder="Address" name="address" type="text" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Email address: </label>
                            <input type="email" value="{{ old('email') }}" name="email" class="form-control" placeholder="Email Address">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="gender">Gender: <span class="text-danger">*</span></label>
                            <select class="form-control select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option {{ (old('gender') == 'Male') ? 'selected' : '' }} value="Male">Male</option>
                                <option {{ (old('gender') == 'Female') ? 'selected' : '' }} value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Phone:</label>
                            <input value="{{ old('phone') }}" type="text" name="phone" class="form-control" placeholder="Primary Phone" >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Telephone:</label>
                            <input value="{{ old('phone2') }}" type="text" name="phone2" class="form-control" placeholder="Secondary Phone" >
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date of Birth:</label>
                            <input name="dob" value="{{ old('dob') }}" type="date" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nationality">Nationality: <span class="text-danger">*</span></label>
                            <select required name="nationality" id="nationality" class="form-control select">
                                <option value="">Select Nationality</option>
                                <option {{ (old('nationality') == 'Zimbabwean') ? 'selected' : '' }} value="Zimbabwean">Zimbabwean</option>
                                <option {{ (old('nationality') == 'Other') ? 'selected' : '' }} value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="province">Province: <span class="text-danger">*</span></label>
                        <select onchange="getDistricts(this.value)" required class="form-control select" name="province" id="province">
                            <option value="">Select Province</option>
                            @foreach($provinces as $key => $province)
                                <option {{ (old('province') == $key) ? 'selected' : '' }} value="{{ $key }}">{{ $province }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="district">District: </label>
                        <select class="form-control select" name="district" id="district">
                            <option value="">Select District (Optional)</option>
                            @if(old('district') && old('province'))
                                <option value="{{ old('district') }}" selected>{{ old('district') }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="medical_history">Medical History:</label>
                            <textarea class="form-control" name="medical_history" id="medical_history" rows="2" placeholder="Any past medical history, surgeries, or chronic conditions">{{ old('medical_history') }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Medical Conditions:</label>
                            <select class="form-control select-multiple" name="medical_conditions[]" id="medical_conditions" multiple="multiple" data-placeholder="Select conditions if any">
                                @foreach($medical_conditions as $condition)
                                    <option value="{{ $condition }}" {{ (is_array(old('medical_conditions')) && in_array($condition, old('medical_conditions'))) ? 'selected' : '' }}>{{ $condition }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Allergies:</label>
                            <select class="form-control select-multiple" name="allergies[]" id="allergies" multiple="multiple" data-placeholder="Select allergies if any">
                                @foreach($allergies as $allergy)
                                    <option value="{{ $allergy }}" {{ (is_array(old('allergies')) && in_array($allergy, old('allergies'))) ? 'selected' : '' }}>{{ $allergy }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="d-block">Upload Passport Photo: <small>(Optional)</small></label>
                            <input type="file" name="photo" class="form-control-file" id="photo" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewPhoto(this)">
                            <small class="form-text text-muted">Optional: JPG, PNG, GIF, WEBP (Max: 2MB)</small>
                            <div id="photo-preview" class="mt-2" style="display: none;">
                                <img id="photo-preview-img" src="" alt="Preview" style="max-width: 150px; max-height: 150px; border-radius: 50%; border: 2px solid #ddd;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="icon-info"></i> Student Account Generation</h6>
                    <p class="mb-0">The system will automatically create a student login account with:</p>
                    <ul class="mb-0">
                        <li><strong>Username:</strong> Auto-generated from class code, year, and admission number</li>
                        <li><strong>Password:</strong> Automatically set to <code>student</code> (student should change it after first login)</li>
                    </ul>
                    <small class="text-muted">Login credentials will be displayed after successful registration.</small>
                </div>

                <h6>Emergency Contact Information</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Emergency Contact Name:</label>
                            <input value="{{ old('emergency_contact_name') }}" type="text" name="emergency_contact_name" class="form-control" placeholder="Full Name" >
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Emergency Contact Phone:</label>
                            <input value="{{ old('emergency_contact_phone') }}" type="text" name="emergency_contact_phone" class="form-control" placeholder="Phone Number" >
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Relationship:</label>
                            <input value="{{ old('emergency_contact_relationship') }}" type="text" name="emergency_contact_relationship" class="form-control" placeholder="e.g., Father, Mother, Guardian" >
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            
            <h6>Student Data</h6>
            <div id="student-data-section">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="my_class_id">Class: <span class="text-danger">*</span></label>
                            <select onchange="getClassSections(this.value)" required name="my_class_id" id="my_class_id" class="form-control select">
                                <option value="">Select Class</option>
                                @foreach($my_classes as $c)
                                    <option {{ (old('my_class_id') == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="section_id">Section:</label>
                            <select name="section_id" id="section_id" class="form-control select">
                                <option value="">Select Section (Optional)</option>
                                @if(old('section_id'))
                                    <option value="{{ old('section_id') }}" selected>Loading...</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="my_parent_id">Parent: </label>
                            <select name="my_parent_id" id="my_parent_id" class="form-control select">
                                <option value="">Select Parent (Optional)</option>
                                @if(isset($parents) && count($parents) > 0)
                                    @foreach($parents as $p)
                                        <option {{ (old('my_parent_id') == Qs::hash($p->id)) ? 'selected' : '' }} value="{{ Qs::hash($p->id) }}">{{ $p->name }} ({{ $p->phone }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="admission_date">Admission Date: <span class="text-danger">*</span></label>
                            <input required type="date" name="admission_date" id="admission_date" class="form-control" value="{{ old('admission_date') }}" max="{{ date('Y-m-d') }}">
                            <small class="form-text text-muted">Select the date when the student was admitted</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="dorm_id">Dormitory: </label>
                        <select name="dorm_id" id="dorm_id" class="form-control select">
                            <option value="">Select Dormitory (Optional)</option>
                            @foreach($dorms as $d)
                                <option {{ (old('dorm_id') == $d->id) ? 'selected' : '' }} value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Dormitory Room No:</label>
                            <input type="text" name="dorm_room_no" placeholder="Dormitory Room No" class="form-control" value="{{ old('dorm_room_no') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sport House:</label>
                            <input type="text" name="house" placeholder="Sport House" class="form-control" value="{{ old('house') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Admission Number:</label>
                            <input type="text" name="adm_no" placeholder="Admission Number" class="form-control" value="{{ old('adm_no') }}">
                            <small class="form-text text-muted">Leave empty for auto-generation</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mt-4">
                <button type="submit" id="submit-btn" class="btn btn-primary btn-lg">
                    <span id="submit-text">Admit Student <i class="icon-paperplane ml-2"></i></span>
                    <span id="submit-loading" style="display: none;">
                        <i class="icon-spinner2 spinner ml-2"></i> Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select').select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
    });

    $('.select-multiple').select2({
        placeholder: "Select options or leave empty",
        allowClear: true,
        width: '100%',
        tags: false,
        createTag: function (params) {
            return null;
        }
    });

    // Form submission cleanup and validation
    var formSubmitted = false;
    $('#student-registration-form').on('submit', function(e) {
        // Prevent double submission
        if (formSubmitted) {
            e.preventDefault();
            return false;
        }
        
        // Ensure CSRF token is present and fresh
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var formToken = $('input[name="_token"]').val();
        if (!formToken && csrfToken) {
            $(this).append('<input type="hidden" name="_token" value="' + csrfToken + '">');
        } else if (formToken && csrfToken && formToken !== csrfToken) {
            // Update token if it's different
            $('input[name="_token"]').val(csrfToken);
        }
        
        // Clean up medical conditions
        var medicalConditions = $('#medical_conditions').val();
        if (medicalConditions && medicalConditions.length > 0) {
            var filteredMedical = medicalConditions.filter(function(value) {
                return value && value.trim() !== '';
            });
            $('#medical_conditions').val(filteredMedical.length > 0 ? filteredMedical : []);
        } else {
            $('#medical_conditions').val([]);
        }
        
        // Clean up allergies
        var allergies = $('#allergies').val();
        if (allergies && allergies.length > 0) {
            var filteredAllergies = allergies.filter(function(value) {
                return value && value.trim() !== '';
            });
            $('#allergies').val(filteredAllergies.length > 0 ? filteredAllergies : []);
        } else {
            $('#allergies').val([]);
        }
        
        // Show loading state
        $('#submit-text').hide();
        $('#submit-loading').show();
        $('#submit-btn').prop('disabled', true);
        
        // Mark form as submitted
        formSubmitted = true;
        
        // Allow form to submit normally
        return true;
    });

    // Load saved data
    @if(old('province'))
        getDistricts("{{ old('province') }}");
    @endif

    @if(old('my_class_id'))
        getClassSections("{{ old('my_class_id') }}");
    @endif
});

function getClassSections(class_id) {
    if (!class_id) {
        $('#section_id').html('<option value="">Select Class First</option>');
        $('#section_id').select2({
            placeholder: "Select Section (Optional)",
            allowClear: true,
            width: '100%'
        });
        return;
    }
    
    // Use absolute URL to avoid route parameter issues
    $.ajax({
        url: '/students/get-sections/' + class_id,
        type: 'GET',
        success: function(data) {
            $('#section_id').html(data);
            $('#section_id').select2({
                placeholder: "Select Section (Optional)",
                allowClear: true,
                width: '100%'
            });
        },
        error: function(xhr) {
            console.error('Error loading sections:', xhr.responseText);
            $('#section_id').html('<option value="">Error loading sections</option>');
            $('#section_id').select2({
                placeholder: "Select Section (Optional)",
                allowClear: true,
                width: '100%'
            });
        }
    });
}

function getDistricts(province) {
    if (!province) {
        $('#district').html('<option value="">Select Province First</option>');
        return;
    }
    
    $.ajax({
        url: '/students/get-districts',
        type: 'GET',
        data: { province: province },
        success: function(response) {
            if (response.success) {
                $('#district').html(response.html);
                $('#district').select2({
                    placeholder: "Select District (Optional)",
                    allowClear: true,
                    width: '100%'
                });
            } else {
                $('#district').html('<option value="">No districts found</option>');
            }
        },
        error: function(xhr) {
            console.error('Error loading districts:', xhr.responseText);
            $('#district').html('<option value="">Error loading districts</option>');
        }
    });
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#photo-preview-img').attr('src', e.target.result);
            $('#photo-preview').show();
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        $('#photo-preview').hide();
    }
}
</script>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container {
    width: 100% !important;
}
.select2-selection {
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    min-height: 38px !important;
}
.select2-selection__rendered {
    line-height: 36px !important;
}
.select2-selection__arrow {
    height: 36px !important;
}
.invalid-feedback {
    display: block !important;
}
.is-invalid {
    border-color: #dc3545 !important;
}
.spinner {
    animation: spin 1s linear infinite;
    display: inline-block;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
#photo-preview-img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 50%;
    border: 2px solid #ddd;
    object-fit: cover;
}
</style>
@endsection