@extends('layouts.master')
@section('page_title', 'Edit Teacher - ' . $teacher->name)
@section('content')

@if(session('flash_success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('flash_success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Edit Teacher</h6>
    </div>

    <div class="card-body">
        <form method="post" action="{{ route('teachers.update', Qs::hash($teacher->id)) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Full Name <span class="text-danger">*</span></label>
                <div class="col-lg-9">
                    <input name="name" value="{{ old('name', $teacher->name) }}" required type="text" class="form-control" placeholder="Full Name">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Email</label>
                <div class="col-lg-9">
                    <input name="email" value="{{ old('email', $teacher->email) }}" type="email" class="form-control" placeholder="email@example.com">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Phone</label>
                <div class="col-lg-9">
                    <input name="phone" value="{{ old('phone', $teacher->phone) }}" type="text" class="form-control" placeholder="Phone Number">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Gender <span class="text-danger">*</span></label>
                <div class="col-lg-9">
                    <select class="form-control select" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $teacher->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $teacher->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Address</label>
                <div class="col-lg-9">
                    <textarea name="address" class="form-control" rows="3" placeholder="Address">{{ old('address', $teacher->address) }}</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Province</label>
                <div class="col-lg-9">
                    <select onchange="getDistricts(this.value)" class="form-control select" name="province" id="province">
                        <option value="">Select Province</option>
                        @php
                            $provinces = [
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
                        @endphp
                        @foreach($provinces as $key => $province)
                            <option value="{{ $key }}" {{ old('province', $teacher->province) == $key ? 'selected' : '' }}>{{ $province }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">District</label>
                <div class="col-lg-9">
                    <select class="form-control select" name="district" id="district">
                        <option value="">{{ old('district', $teacher->district) ?: 'Select Province First (Optional)' }}</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Medical History</label>
                <div class="col-lg-9">
                    <textarea name="medical_history" class="form-control" rows="3" placeholder="Enter medical history or conditions">{{ old('medical_history', $teacher->medical_history) }}</textarea>
                    <small class="form-text text-muted">Enter any medical conditions, allergies, or health information</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Username</label>
                <div class="col-lg-9">
                    <input name="username" value="{{ old('username', $teacher->username) }}" type="text" class="form-control" placeholder="Username">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">New Password</label>
                <div class="col-lg-9">
                    <input name="password" type="password" class="form-control" placeholder="Leave blank to keep current password">
                    <small class="form-text text-muted">Only fill if you want to change the password</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold">Photo</label>
                <div class="col-lg-9">
                    <div class="mb-2">
                        <img src="{{ $teacher->photo }}" alt="Current Photo" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                    <input name="photo" type="file" class="form-control-file" accept="image/*">
                    <small class="form-text text-muted">Optional: JPG, PNG, GIF (Max: 2MB)</small>
                </div>
            </div>

            <div class="text-right">
                <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Teacher <i class="icon-paperplane ml-2"></i></button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function getDistricts(province) {
    if (!province) {
        $('#district').html('<option value="">Select Province First</option>');
        return;
    }
    
    $.ajax({
        url: '{{ route("teachers.get_districts") }}',
        type: 'GET',
        data: { province: province },
        success: function(response) {
            if (response.success) {
                $('#district').html(response.html);
                @if($teacher->district)
                    setTimeout(function() {
                        $('#district').val('{{ $teacher->district }}');
                    }, 500);
                @endif
            }
        },
        error: function() {
            $('#district').html('<option value="">Error loading districts</option>');
        }
    });
}

// Load districts if province is already selected
@if($teacher->province)
    getDistricts('{{ $teacher->province }}');
@endif
</script>
@endsection
