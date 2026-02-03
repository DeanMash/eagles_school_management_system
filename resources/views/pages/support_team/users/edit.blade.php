@extends('layouts.master')
@section('page_title', 'Edit User')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit User Details</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            @if(!$user)
                <div class="alert alert-danger">User not found.</div>
            @else
            <form method="post" enctype="multipart/form-data" class="wizard-form steps-validation ajax-update" action="{{ route('users.update', Qs::hash($user->id)) }}" data-fouc>
                @csrf @method('PUT')
                <h6>Personal Data</h6>
                <fieldset>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="user_type"> Select User: <span class="text-danger">*</span></label>
                                <select disabled="disabled" class="form-control select" id="user_type">
                                    <option value="">{{ strtoupper($user->user_type) }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Full Name: <span class="text-danger">*</span></label>
                                <input value="{{ $user->name }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Address: <span class="text-danger">*</span></label>
                                <input value="{{ $user->address }}" class="form-control" placeholder="Address" name="address" type="text" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email address: </label>
                                <input value="{{ $user->email }}" type="email" name="email" class="form-control" placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Phone:</label>
                                <input value="{{ $user->phone }}" type="text" name="phone" class="form-control" placeholder="" >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Telephone:</label>
                                <input value="{{ $user->phone2 }}" type="text" name="phone2" class="form-control" placeholder="" >
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        @if(in_array($user->user_type, Qs::getStaff()))
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date of Employment:</label>
                                    <input autocomplete="off" name="emp_date" value="{{ $user->staff->first()->emp_date ?? '' }}" type="text" class="form-control date-pick" placeholder="Select Date...">

                                </div>
                            </div>
                        @endif

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gender">Gender: <span class="text-danger">*</span></label>
                                <select class="select form-control" id="gender" name="gender" required data-fouc data-placeholder="Choose..">
                                    <option value=""></option>
                                    <option {{ ($user->gender == 'Male') ? 'selected' : '' }} value="Male">Male</option>
                                    <option {{ ($user->gender == 'Female') ? 'selected' : '' }} value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                                <select data-placeholder="Choose..." required name="nal_id" id="nal_id" class="select-search form-control">
                                    <option value=""></option>
                                    @foreach($nationals as $nal)
                                        <option {{ ($user->nal_id == $nal->id) ? 'selected' : '' }} value="{{ $nal->id }}">{{ $nal->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label for="province">Province:</label>
                            <select onchange="getDistricts(this.value)" data-placeholder="Choose Province (Optional).." class="select-search form-control" name="province" id="province">
                                <option value=""></option>
                                @foreach($provinces as $key => $province)
                                    <option {{ (old('province', $user->province) == $key ? 'selected' : '') }} value="{{ $key }}">{{ $province }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="district">District:</label>
                            <select data-placeholder="Select Province First (Optional)" class="select-search form-control" name="district" id="district">
                                <option value="">{{ old('district', $user->district) ?: 'Select Province First (Optional)' }}</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="medical_history">Medical History: </label>
                                <textarea class="form-control" id="medical_history" name="medical_history" rows="3" data-fouc placeholder="Enter medical history or conditions">{{ old('medical_history', $user->medical_history) }}</textarea>
                                <small class="form-text text-muted">Enter any medical conditions, allergies, or health information</small>
                            </div>
                        </div>

                    </div>

                    {{--Passport--}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block">Upload Passport Photo:</label>
                                <input value="{{ old('photo') }}" accept="image/*" type="file" name="photo" class="form-input-styled" data-fouc>
                                <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 2Mb</span>
                            </div>
                        </div>
                    </div>

                </fieldset>



            </form>
            @endif
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
        url: '{{ route("users.get_districts") }}',
        type: 'GET',
        data: { province: province },
        success: function(response) {
            if (response.success) {
                $('#district').html(response.html);
                @if($user->district)
                    setTimeout(function() {
                        $('#district').val('{{ $user->district }}');
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
@if($user->province)
    getDistricts('{{ $user->province }}');
@endif
</script>
@endsection
