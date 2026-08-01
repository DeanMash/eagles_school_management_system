@extends('layouts.master')
@section('page_title', 'Edit Student')
@section('content')

        <div class="card">
            <div class="card-header bg-white header-elements-inline">
                <h6 id="ajax-title" class="card-title">Please fill The form Below To Edit record of {{ $sr->user->name }}</h6>

                {!! Qs::getPanelOptions() !!}
            </div>

            <form method="post" enctype="multipart/form-data" class="wizard-form steps-validation ajax-update" data-reload="#ajax-title" action="{{ route('students.update', Qs::hash($sr->id)) }}" data-fouc>
                @csrf @method('PUT')
                <h6>Personal data</h6>
                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name: <span class="text-danger">*</span></label>
                                <input value="{{ $sr->user->name }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Address: <span class="text-danger">*</span></label>
                                <input value="{{ $sr->user->address }}" class="form-control" placeholder="Address" name="address" type="text" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Email address: <span class="text-danger">*</span></label>
                                <input value="{{ $sr->user->email  }}" type="email" name="email" class="form-control" placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="gender">Gender: <span class="text-danger">*</span></label>
                                <select class="select form-control" id="gender" name="gender" required data-fouc data-placeholder="Choose..">
                                    <option value=""></option>
                                    <option {{ ($sr->user->gender  == 'Male' ? 'selected' : '') }} value="Male">Male</option>
                                    <option {{ ($sr->user->gender  == 'Female' ? 'selected' : '') }} value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Phone:</label>
                                <input value="{{ $sr->user->phone  }}" type="text" name="phone" class="form-control" placeholder="" >
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Telephone:</label>
                                <input value="{{ $sr->user->phone2  }}" type="text" name="phone2" class="form-control" placeholder="" >
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date of Birth:</label>
                                <input name="dob" value="{{ $sr->user->dob  }}" type="text" class="form-control date-pick" placeholder="Select Date...">

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                                <select data-placeholder="Choose..." required name="nal_id" id="nal_id" class="select-search form-control">
                                    <option value=""></option>
                                    @foreach($nationals as $na)
                                        <option {{  ($sr->user->nal_id  == $na->id ? 'selected' : '') }} value="{{ $na->id }}">{{ $na->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label for="state_id">Province: <span class="text-danger">*</span></label>
                            <select onchange="getLGA(this.value)" required data-placeholder="Choose.." class="select-search form-control" name="state_id" id="state_id">
                                <option value=""></option>
                                @foreach($states as $st)
                                    <option {{ ($sr->user->state_id  == $st->id ? 'selected' : '') }} value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="lga_id">District: <span class="text-danger">*</span></label>
                            <select required data-placeholder="Select Province First" class="select-search form-control" name="lga_id" id="lga_id">
                                @if($sr->user->lga_id)
                                    <option selected value="{{ $sr->user->lga_id }}">{{ $sr->user->lga->name}}</option>
                                @endif
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="medical_info">Medical Information / Allergies:</label>
                                <textarea class="form-control" name="medical_info" id="medical_info" rows="2" placeholder="Any allergies, medical conditions, or special requirements">{{ old('medical_info', $sr->user->medical_info ?? '') }}</textarea>
                                <small class="form-text text-muted">Optional: List any allergies or medical conditions</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block">Upload Passport Photo:</label>
                                <input value="{{ old('photo') }}" accept="image/*" type="file" name="photo" class="form-input-styled" data-fouc>
                                <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 2Mb</span>
                            </div>
                        </div>
                    </div>

                </fieldset>

                <h6>Student Data</h6>
                <fieldset>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="my_class_id">Class: </label>
                                <select onchange="getClassSections(this.value)" name="my_class_id" required id="my_class_id" class="form-control select-search" data-placeholder="Select Class">
                                    <option value=""></option>
                                    @foreach($my_classes as $c)
                                        <option {{ $sr->my_class_id == $c->id ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="section_id">Section: </label>
                                <select name="section_id" required id="section_id" class="form-control select" data-placeholder="Select Section">
                                    <option value="{{ $sr->section_id }}">{{ $sr->section->name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="my_parent_id">Parent: </label>
                                <select data-placeholder="Choose..."  name="my_parent_id" id="my_parent_id" class="select-search form-control">
                                    <option  value=""></option>
                                    @foreach($parents as $p)
                                        <option {{ ((string) $sr->my_parent_id === (string) $p->id) ? 'selected' : '' }} value="{{ Qs::hash($p->id) }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="admission_date">Admission Date: </label>
                                <input type="date" name="admission_date" id="admission_date" class="form-control" value="{{ old('admission_date', $sr->admission_date ? date('Y-m-d', strtotime($sr->admission_date)) : '') }}" max="{{ date('Y-m-d') }}">
                                <small class="form-text text-muted">Select the date when the student was admitted</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="dorm_id">Dormitory: </label>
                            <select data-placeholder="Choose..."  name="dorm_id" id="dorm_id" class="select-search form-control">
                                <option value=""></option>
                                @foreach($dorms as $d)
                                    <option {{ ($sr->dorm_id == $d->id) ? 'selected' : '' }} value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>

                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dormitory Room No:</label>
                                <input type="text" name="dorm_room_no" placeholder="Dormitory Room No" class="form-control" value="{{ $sr->dorm_room_no }}">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- PASSWORD MANAGEMENT SECTION -->
                <h6>Password Management</h6>
                <fieldset>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info border-left-info border-left-3">
                                <h6 class="alert-heading font-weight-semibold">Password Management Options</h6>
                                <p>You can either change the password by providing current password, or reset it to default values.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Option 1: Change Password with Current Password Verification -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="icon-lock mr-2"></i> Change Password
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="current_password">Current Password: <span class="text-danger">*</span></label>
                                        <input type="password" name="current_password" id="current_password" 
                                               class="form-control" placeholder="Enter current password">
                                        <small class="form-text text-muted">Required to verify identity</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="new_password">New Password:</label>
                                        <input type="password" name="new_password" id="new_password" 
                                               class="form-control" minlength="6" placeholder="Enter new password">
                                        <small class="form-text text-muted">Minimum 6 characters</small>
                                        <div id="password-strength" class="mt-1"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="new_password_confirmation">Confirm New Password:</label>
                                        <input type="password" name="new_password_confirmation" 
                                               id="new_password_confirmation" class="form-control" 
                                               placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <button type="button" class="btn btn-primary" onclick="updatePassword()">
                                    <i class="icon-lock mr-1"></i> Update Password
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Quick Reset Buttons -->
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h6 class="card-title mb-0">
                                <i class="icon-reset mr-2"></i> Quick Password Reset
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group text-center">
                                        <button type="button" class="btn btn-warning btn-block" 
                                                onclick="resetToDefault('{{ Qs::hash($sr->user->id) }}')">
                                            <i class="icon-reset mr-1"></i> Reset to "student"
                                        </button>
                                        <small class="form-text text-muted">System default password</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group text-center">
                                        <button type="button" class="btn btn-info btn-block" 
                                                onclick="resetToCustom('{{ Qs::hash($sr->user->id) }}')">
                                            <i class="icon-key mr-1"></i> Reset to "Eagles@2024"
                                        </button>
                                        <small class="form-text text-muted">Custom default password</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group text-center">
                                        <button type="button" class="btn btn-success btn-block" 
                                                onclick="showPasswordGenerator()">
                                            <i class="icon-cog5 mr-1"></i> Generate Strong Password
                                        </button>
                                        <small class="form-text text-muted">Generate secure random password</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="alert alert-warning border-left-warning border-left-3">
                                        <h6 class="alert-heading font-weight-semibold mb-1">Important Notes:</h6>
                                        <ul class="mb-0 pl-3">
                                            <li>Password reset will log the student out of all devices</li>
                                            <li>Default passwords should be changed on first login</li>
                                            <li>Keep passwords secure and don't share them</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Username Field (Optional - for changing username) -->
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="card-title mb-0">
                                <i class="icon-user mr-2"></i> Username Management
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username">Username:</label>
                                        <input type="text" name="username" id="username" class="form-control" 
                                               value="{{ $sr->user->username }}" placeholder="Username for login">
                                        <small class="form-text text-muted">Current username: <strong>{{ $sr->user->username }}</strong></small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username_check">Check Availability:</label>
                                        <div class="input-group">
                                            <input type="text" id="username_check" class="form-control" 
                                                   placeholder="Check new username">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-secondary" onclick="checkUsername()">
                                                    Check
                                                </button>
                                            </div>
                                        </div>
                                        <div id="username-result" class="mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

            </form>
        </div>

        <!-- Password Generator Modal -->
        <div id="password-generator-modal" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="icon-cog5 mr-2"></i> Generated Password
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Generated Password:</label>
                            <div class="input-group">
                                <input type="text" id="generated-password" class="form-control" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary" onclick="copyGeneratedPassword()">
                                        <i class="icon-copy2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Password Strength:</label>
                            <div class="progress">
                                <div id="password-strength-bar" class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <small class="form-text text-success">Very Strong</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" onclick="useGeneratedPassword()">Use This Password</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Password strength indicator
        $('#new_password').on('keyup', function() {
            var password = $(this).val();
            if (!password) {
                $('#password-strength').html('');
                return;
            }
            
            var strength = 0;
            var tips = [];
            
            // Check length
            if (password.length >= 8) strength++;
            else tips.push("Make it at least 8 characters");
            
            // Check for mixed case
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            else tips.push("Use both uppercase and lowercase letters");
            
            // Check for numbers
            if (password.match(/\d/)) strength++;
            else tips.push("Include at least one number");
            
            // Check for special characters
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            else tips.push("Include at least one special character");
            
            var strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength];
            var strengthColor = ['danger', 'danger', 'warning', 'info', 'success'][strength];
            var strengthPercent = [20, 40, 60, 80, 100][strength];
            
            var html = '<div class="mt-1">';
            html += '<div class="progress" style="height: 5px;">';
            html += '<div class="progress-bar bg-' + strengthColor + '" style="width: ' + strengthPercent + '%"></div>';
            html += '</div>';
            html += '<small class="text-' + strengthColor + '">Strength: ' + strengthText + '</small>';
            
            if (tips.length > 0 && strength < 4) {
                html += '<small class="form-text text-muted d-block">Tips: ' + tips.join(', ') + '</small>';
            }
            
            html += '</div>';
            
            $('#password-strength').html(html);
        });

        // Update password function
        function updatePassword() {
            var currentPassword = $('#current_password').val();
            var newPassword = $('#new_password').val();
            var confirmPassword = $('#new_password_confirmation').val();
            
            if (!currentPassword) {
                alert('Please enter current password');
                $('#current_password').focus();
                return;
            }
            
            if (!newPassword) {
                alert('Please enter new password');
                $('#new_password').focus();
                return;
            }
            
            if (newPassword.length < 6) {
                alert('Password must be at least 6 characters');
                $('#new_password').focus();
                return;
            }
            
            if (newPassword !== confirmPassword) {
                alert('New password and confirmation do not match');
                $('#new_password_confirmation').focus();
                return;
            }
            
            if (confirm('Are you sure you want to update the password?')) {
                $.ajax({
                    url: '{{ route("students.update.password", Qs::hash($sr->user->id)) }}',
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        current_password: currentPassword,
                        new_password: newPassword,
                        new_password_confirmation: confirmPassword
                    },
                    success: function(response) {
                        alert('Password updated successfully!');
                        // Clear password fields
                        $('#current_password').val('');
                        $('#new_password').val('');
                        $('#new_password_confirmation').val('');
                        $('#password-strength').html('');
                    },
                    error: function(xhr) {
                        var error = xhr.responseJSON?.errors || {};
                        if (error.current_password) {
                            alert('Error: ' + error.current_password[0]);
                        } else {
                            alert('Error updating password. Please try again.');
                        }
                    }
                });
            }
        }

        // Reset to default password
        function resetToDefault(userId) {
            if (confirm('Reset password to "student"? This will log the student out of all devices.')) {
                window.location.href = "{{ route('students.reset_pass', '') }}/" + userId;
            }
        }

        // Reset to custom password
        function resetToCustom(userId) {
            if (confirm('Reset password to "Eagles@2024"? This will log the student out of all devices.')) {
                $.ajax({
                    url: '{{ route("students.reset.password.custom", "") }}/' + userId,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert('Password has been reset to: ' + response.default_password);
                    },
                    error: function() {
                        alert('Error resetting password');
                    }
                });
            }
        }

        // Generate strong password
        function generateStrongPassword() {
            var length = 12;
            var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
            var password = "";
            
            // Ensure at least one of each type
            password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(Math.floor(Math.random() * 26));
            password += "abcdefghijklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 26));
            password += "0123456789".charAt(Math.floor(Math.random() * 10));
            password += "!@#$%^&*()_+".charAt(Math.floor(Math.random() * 12));
            
            // Fill the rest
            for (var i = 4; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            
            // Shuffle the password
            password = password.split('').sort(function(){return 0.5-Math.random()}).join('');
            
            return password;
        }

        // Show password generator modal
        function showPasswordGenerator() {
            var password = generateStrongPassword();
            $('#generated-password').val(password);
            $('#password-generator-modal').modal('show');
        }

        // Copy generated password to clipboard
        function copyGeneratedPassword() {
            var copyText = document.getElementById("generated-password");
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");
            alert("Password copied to clipboard!");
        }

        // Use generated password
        function useGeneratedPassword() {
            var password = $('#generated-password').val();
            $('#new_password').val(password);
            $('#new_password_confirmation').val(password);
            $('#password-generator-modal').modal('hide');
            $('#new_password').keyup(); // Trigger strength check
        }

        // Check username availability
        function checkUsername() {
            var username = $('#username_check').val();
            if (!username) {
                $('#username-result').html('<small class="text-danger">Please enter a username to check</small>');
                return;
            }
            
            $.ajax({
                url: '{{ route("students.check.username") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    username: username,
                    user_id: {{ $sr->user->id }}
                },
                success: function(response) {
                    if (response.available) {
                        $('#username-result').html('<small class="text-success">✓ ' + response.message + '</small>');
                    } else {
                        $('#username-result').html('<small class="text-danger">✗ ' + response.message + '</small>');
                    }
                },
                error: function() {
                    $('#username-result').html('<small class="text-danger">Error checking username</small>');
                }
            });
        }

        // Auto-check username when typing
        $('#username_check').on('keyup', function() {
            var username = $(this).val();
            if (username.length >= 3) {
                // Debounce the check
                clearTimeout($(this).data('timeout'));
                $(this).data('timeout', setTimeout(function() {
                    checkUsername();
                }, 500));
            }
        });

        // Suggest username based on name
        function suggestUsername() {
            var name = $('#name').val();
            if (name) {
                var suggested = name.toLowerCase().replace(/\s+/g, '.') + '{{ date("Y") }}';
                $('#username_check').val(suggested);
                checkUsername();
            }
        }
        </script>
@endsection