@extends('layouts.master')
@section('page_title', 'Manage TimeTable Record')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">{{ $ttr->name.' ('.$my_class->name.')'.' '.$ttr->year }}</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#grid-view" class="nav-link active" data-toggle="tab"><i class="icon-grid6 mr-2"></i>Grid View</a></li>
                <li class="nav-item"><a href="#manage-ts" class="nav-link" data-toggle="tab">Manage Time Slots</a></li>
                <li class="nav-item"><a href="#add-sub" class="nav-link" data-toggle="tab">Add Subject</a></li>
                <li class="nav-item"><a href="#edit-subs" class="nav-link " data-toggle="tab">Edit Subjects</a></li>
                <li class="nav-item"><a target="_blank" href="{{ route('ttr.show', $ttr->id) }}" class="nav-link" >View TImeTable</a></li>
            </ul>

            <div class="tab-content">
                {{--Grid View--}}
                <div class="tab-pane fade show active" id="grid-view">
                    <div class="mt-3">
                        @include('pages.support_team.timetables.grid')
                    </div>
                </div>
                {{--Weekly View (date-specific slots)--}}
                <div class="tab-pane fade" id="weekly-view">
                    <div class="mt-3">
                        @include('pages.support_team.timetables.weekly_grid')
                    </div>
                </div>
                {{--Add Time Slots--}}
                <div class="tab-pane fade" id="manage-ts">
                    @include('pages.support_team.timetables.time_slots.index')
                </div>
                {{--Add Subject--}}
                <div class="tab-pane fade" id="add-sub">
                    @include('pages.support_team.timetables.subjects.add')
                </div>
                {{--Edit Subject--}}
                <div class="tab-pane fade" id="edit-subs">
                    @include('pages.support_team.timetables.subjects.edit')
                </div>
            </div>
        </div>
    </div>

    {{--TimeTable Manage Ends--}}

@if(request('week_start'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tab = document.querySelector('a[href="#weekly-view"]');
    if (tab) { tab.click(); }
});
</script>
@endif
@endsection
