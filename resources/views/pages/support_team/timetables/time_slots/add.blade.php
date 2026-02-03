<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info text-center">
            <span>You can Add New Time Slots or Choose To Use Existing Time Slots of Another Timetable. <strong>NB:</strong> Using Exisiting Time Slots Resets The Current Timetable</span>
        </div>
    </div>
    
    {{-- Quick Create: form so button submit works --}}
    <div class="col-md-12 mb-3">
        <div class="card bg-success-400">
            <div class="card-body">
                <form id="bulk-create-slots-form" method="post" action="{{ route('ttr.bulk_create_time_slots', $ttr->id) }}" class="d-flex justify-content-between align-items-center">
                    @csrf
                    <div>
                        <h5 class="text-white mb-1"><i class="icon-magic-wand mr-2"></i>Quick Create Time Slots</h5>
                        <p class="text-white mb-0">Automatically create 30-minute slots from 7:30 AM to 3:30 PM with break at 10:00 AM and lunch at 2:00 PM</p>
                    </div>
                    <div>
                        <button type="submit" id="bulk-create-slots-btn" class="btn btn-light btn-lg">
                            <i class="icon-plus-circle2 mr-2"></i> Create All Slots
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header header-elements-inline bg-danger">
                <h6 class="font-weight-bold card-title">Add Time Slots</h6>
                {!! Qs::getPanelOptions() !!}
            </div>

            <div class="card-body collapse">
                <div class="col-md-12">
                    <form data-reload="#time_slots_table" class="ajax-store" method="post" action="{{ route('tt.time_slots.store') }}">
                        @csrf
                        <input name="ttr_id" value="{{ $ttr->id }}" type="hidden">

                        {{--TIME BEGIN--}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Start Time <span
                                        class="text-danger">*</span></label>

                            <div class="col-lg-3">
                                <select data-placeholder="Hour" required class="select-search form-control" name="hour_from" id="hour_from">

                                    <option value=""></option>
                                    @for($t=1; $t<=12; $t++)
                                        <option {{ old('hour_from') == $t ? 'selected' : '' }} value="{{ $t }}">{{ $t}}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <select data-placeholder="Minute" required class="select-search form-control" name="min_from" id="min_from">

                                    <option value=""></option>
                                    <option value="00">00</option>
                                    <option value="05">05</option>
                                    @for($t=10; $t<=55; $t+=5)
                                        <option {{ old('min_from') == $t ? 'selected' : '' }} value="{{ $t }}">{{ $t}}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <select data-placeholder="Meridian" required class="select form-control" name="meridian_from" id="meridian_from">

                                    <option value=""></option>
                                    <option {{ old('meridian_from') == 'AM' ? 'selected' : '' }} value="AM">AM
                                    </option>
                                    <option {{ old('meridian_from') == 'PM' ? 'selected' : '' }} value="PM">PM
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{--TIME END--}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">End Time <span class="text-danger">*</span></label>

                            <div class="col-lg-3">
                                <select data-placeholder="Hour" required class="select-search form-control" name="hour_to" id="hour_to">

                                    <option value=""></option>
                                    @for($t=1; $t<=12; $t++)
                                        <option {{ old('hour_to') == $t ? 'selected' : '' }} value="{{ $t }}">{{ $t}}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <select data-placeholder="Minute" required class="select-search form-control" name="min_to" id="min_to">

                                    <option value=""></option>
                                    <option value="00">00</option>
                                    <option value="05">05</option>
                                    @for($t=10; $t<=55; $t+=5)
                                        <option {{ old('min_to') == $t ? 'selected' : '' }} value="{{ $t }}">{{ $t}}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <select data-placeholder="Meridian" required class="select form-control"
                                        name="meridian_to" id="meridian_to">

                                    <option value=""></option>
                                    <option {{ old('meridian_to') == 'AM' ? 'selected' : '' }} value="AM">AM
                                    </option>
                                    <option {{ old('meridian_to') == 'PM' ? 'selected' : '' }} value="PM">PM
                                    </option>
                                </select>
                            </div>
                        </div>


                        <div class="text-right">
                            <button  type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header header-elements-inline bg-dark">
                <h6 class="font-weight-bold card-title">Use Existing Time Slots</h6>
                {!! Qs::getPanelOptions() !!}
            </div>

            <div class="card-body collapse">
                <div class="col-md-12">
                    <form method="post" action="{{ route('tt.time_slots.use', $ttr->id) }}">
                        @csrf

                        {{--TIME BEGIN--}}
                        <div class="form-group">
                            <label for="ttr_id" class="col-form-label-lg font-weight-semibold mb-lg-2">Select Existing Time Slots <span class="text-danger">*</span></label>

                            <div class="col-lg-8">
                                <select id="ttr_id" data-placeholder="Select..." required class="select-search form-control-lg" name="ttr_id">

                                    <option value=""></option>
                                    @foreach($ts_existing as $ttr_ts)
                                        <option value="{{ $ttr_ts->id }}">{{ $ttr_ts->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-lg btn-success">Submit form <i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#bulk-create-slots-form').on('submit', function(e) {
        if (!confirm('This will delete all existing time slots and create new 30-minute slots from 7:30 AM to 3:30 PM. Continue?')) {
            e.preventDefault();
            return false;
        }
        var btn = $('#bulk-create-slots-btn');
        btn.prop('disabled', true).html('<i class="icon-spinner spinner mr-2"></i> Creating Slots...');
    });
});
</script>
