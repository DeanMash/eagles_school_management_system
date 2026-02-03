@extends('layouts.master')
@section('page_title', 'Manage TimeTables')
@section('content')

    {{-- Instructions Card --}}
    @if(Qs::userIsTeamSA())
    <div class="card bg-primary-400">
        <div class="card-body">
            <h5 class="text-white mb-3"><i class="icon-info22 mr-2"></i> How to Create a Timetable</h5>
            <ol class="text-white mb-0" style="line-height: 2;">
                <li><strong>Step 1:</strong> Create a timetable record below (select class and name it)</li>
                <li><strong>Step 2:</strong> Click "Manage" to set up time slots (e.g., 8:00 AM - 9:00 AM)</li>
                <li><strong>Step 3:</strong> Add subjects to each time slot for each day of the week</li>
                <li><strong>Step 4:</strong> View the completed timetable - students and teachers will see it on their dashboards</li>
            </ol>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-calendar52 mr-2"></i> Manage TimeTables</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                @if(Qs::userIsTeamSA())
                <li class="nav-item"><a href="#add-tt" class="nav-link active" data-toggle="tab"><i class="icon-plus-circle2 mr-2"></i>Create Timetable</a></li>
                @endif
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle {{ !Qs::userIsTeamSA() ? 'active' : '' }}" data-toggle="dropdown"><i class="icon-list mr-2"></i>View Timetables</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @if($my_classes->count() > 0)
                            @foreach($my_classes as $mc)
                                <a href="#ttr{{ $mc->id }}" class="dropdown-item" data-toggle="tab">{{ $mc->name }}</a>
                            @endforeach
                        @else
                            <span class="dropdown-item text-muted">No classes available</span>
                        @endif
                    </div>
                </li>
            </ul>


            <div class="tab-content">

                @if(Qs::userIsTeamSA())
                <div class="tab-pane fade show active" id="add-tt">
                   <div class="row">
                       <div class="col-md-8">
                           <div class="alert alert-info border-left-info">
                               <h6 class="alert-heading"><i class="icon-info22 mr-2"></i>Create New Timetable</h6>
                               <p class="mb-0">Start by creating a timetable record for a class. After creation, you'll be able to manage time slots and assign subjects.</p>
                           </div>
                           
                           <form id="create-timetable-form" class="ajax-store" method="post" action="{{ route('ttr.store') }}" data-reload="">
                               @csrf
                               <div class="form-group row">
                                   <label class="col-lg-3 col-form-label font-weight-semibold">Timetable Name <span class="text-danger">*</span></label>
                                   <div class="col-lg-9">
                                       <input name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="e.g., Form 1A Timetable 2026">
                                       <small class="form-text text-muted">Give this timetable a descriptive name</small>
                                   </div>
                               </div>

                               <div class="form-group row">
                                   <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold">Class <span class="text-danger">*</span></label>
                                   <div class="col-lg-9">
                                       <select required data-placeholder="Select Class" class="form-control select" name="my_class_id" id="my_class_id">
                                           @if($my_classes->count() > 0)
                                               @foreach($my_classes as $mc)
                                                   <option {{ old('my_class_id') == $mc->id ? 'selected' : '' }} value="{{ $mc->id }}">{{ $mc->name }}</option>
                                               @endforeach
                                           @else
                                               <option value="" disabled>No classes available. Please create a class first.</option>
                                           @endif
                                       </select>
                                       <small class="form-text text-muted">Select the class this timetable is for</small>
                                   </div>
                               </div>

                               <div class="form-group row">
                                   <label for="exam_id" class="col-lg-3 col-form-label font-weight-semibold">Timetable Type</label>
                                   <div class="col-lg-9">
                                       <select class="select form-control" name="exam_id" id="exam_id">
                                           <option value="">Regular Class Timetable</option>
                                           @if($exams->count() > 0)
                                               @foreach($exams as $ex)
                                                   <option {{ old('exam_id') == $ex->id ? 'selected' : '' }} value="{{ $ex->id }}">{{ $ex->name }} (Exam Timetable)</option>
                                               @endforeach
                                           @endif
                                       </select>
                                       <small class="form-text text-muted">Leave as "Regular Class Timetable" for weekly schedules, or select an exam for exam schedules</small>
                                   </div>
                               </div>

                               <div class="text-right">
                                   <button id="ajax-btn" type="submit" class="btn btn-primary btn-lg" data-text="Creating Timetable">
                                       <i class="icon-plus-circle2 mr-2"></i> <span id="submit-text">Create Timetable</span>
                                   </button>
                               </div>
                           </form>
                       </div>
                       
                       <div class="col-md-4">
                           <div class="card bg-light">
                               <div class="card-header bg-primary text-white">
                                   <h6 class="mb-0"><i class="icon-question7 mr-2"></i> Quick Tips</h6>
                               </div>
                               <div class="card-body">
                                   <ul class="list-unstyled mb-0">
                                       <li class="mb-2"><i class="icon-checkmark3 text-success mr-2"></i> One timetable per class</li>
                                       <li class="mb-2"><i class="icon-checkmark3 text-success mr-2"></i> Students see their class timetable</li>
                                       <li class="mb-2"><i class="icon-checkmark3 text-success mr-2"></i> Teachers see subjects they teach</li>
                                       <li class="mb-2"><i class="icon-checkmark3 text-success mr-2"></i> After creating, click "Manage" to add time slots</li>
                                   </ul>
                               </div>
                           </div>
                       </div>
                   </div>

                </div>
                @endif

                @foreach($my_classes as $mc)
                    <div class="tab-pane fade {{ !Qs::userIsTeamSA() ? 'show active' : '' }}" id="ttr{{ $mc->id }}">
                        <div class="mb-3">
                            <h5 class="font-weight-semibold"><i class="icon-windows2 mr-2"></i>{{ $mc->name }} Timetables</h5>
                            <p class="text-muted">Manage timetables for {{ $mc->name }}. Click "Manage" to add time slots and subjects.</p>
                        </div>
                        
                        @php
                            $classTimetables = $tt_records->where('my_class_id', $mc->id);
                        @endphp
                        
                        @if($classTimetables->count() > 0)
                            <table class="table datatable-button-html5-columns">
                                <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($classTimetables as $ttr)
                                    @php
                                        $timeSlotsCount = \App\Models\TimeSlot::where('ttr_id', $ttr->id)->count();
                                        $subjectsCount = \App\Models\TimeTable::where('ttr_id', $ttr->id)->count();
                                        $isComplete = $timeSlotsCount > 0 && $subjectsCount > 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $ttr->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $ttr->my_class->name }}</small>
                                        </td>
                                        <td>
                                            @if($ttr->exam_id)
                                                <span class="badge badge-info">{{ $ttr->exam->name ?? 'Exam' }}</span>
                                            @else
                                                <span class="badge badge-primary">Class Timetable</span>
                                            @endif
                                        </td>
                                        <td>{{ $ttr->year }}</td>
                                        <td>
                                            @if($isComplete)
                                                <span class="badge badge-success">
                                                    <i class="icon-checkmark3 mr-1"></i>Complete
                                                </span>
                                                <br><small class="text-muted">{{ $subjectsCount }} subjects</small>
                                            @elseif($timeSlotsCount > 0)
                                                <span class="badge badge-warning">
                                                    <i class="icon-info22 mr-1"></i>Needs Subjects
                                                </span>
                                                <br><small class="text-muted">{{ $timeSlotsCount }} time slots</small>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="icon-warning22 mr-1"></i>Empty
                                                </span>
                                                <br><small class="text-muted">No time slots</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                        <i class="icon-menu9"></i>
                                                    </a>

                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        {{--View--}}
                                                        <a href="{{ route('ttr.show', $ttr->id) }}" target="_blank" class="dropdown-item">
                                                            <i class="icon-eye mr-2"></i> View Timetable
                                                        </a>

                                                        @if(Qs::userIsTeamSA())
                                                        {{--Manage--}}
                                                        <a href="{{ route('ttr.manage', $ttr->id) }}" class="dropdown-item">
                                                            <i class="icon-plus-circle2 mr-2"></i> Manage (Add Time Slots & Subjects)
                                                        </a>
                                                        {{--Edit--}}
                                                        <a href="{{ route('ttr.edit', $ttr->id) }}" class="dropdown-item">
                                                            <i class="icon-pencil mr-2"></i> Edit Details
                                                        </a>
                                                        @endif

                                                        {{--Delete--}}
                                                        @if(Qs::userIsSuperAdmin())
                                                            <div class="dropdown-divider"></div>
                                                            <a id="{{ $ttr->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item text-danger">
                                                                <i class="icon-trash mr-2"></i> Delete
                                                            </a>
                                                            <form method="post" id="item-delete-{{ $ttr->id }}" action="{{ route('ttr.destroy', $ttr->id) }}" class="hidden">@csrf @method('delete')</form>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info">
                                <i class="icon-info22 mr-2"></i>
                                No timetables created for {{ $mc->name }} yet.
                                @if(Qs::userIsTeamSA())
                                    <a href="#add-tt" class="alert-link" data-toggle="tab">Create one now</a>.
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    {{--TimeTable Ends--}}

@endsection
