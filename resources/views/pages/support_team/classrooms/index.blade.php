@extends('layouts.master')
@section('page_title', 'Manage Classrooms')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Classrooms</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-classrooms" class="nav-link active" data-toggle="tab">Classrooms</a></li>
                @if(Qs::userIsTeamSA())
                <li class="nav-item"><a href="#new-classroom" class="nav-link" data-toggle="tab"><i class="icon-plus2"></i> Add Room</a></li>
                @endif
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="all-classrooms">
                    <table class="table datatable-button-html5-columns">
                        <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Room Number</th>
                            <th>Capacity</th>
                            <th>Building</th>
                            @if(Qs::userIsTeamSA())<th>Action</th>@endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($classrooms as $room)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $room->room_number }}</td>
                                <td>{{ $room->capacity }}</td>
                                <td>{{ $room->building ?? '-' }}</td>
                                @if(Qs::userIsTeamSA())
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                            <div class="dropdown-menu dropdown-menu-left">
                                                <a id="{{ $room->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item text-danger"><i class="icon-trash"></i> Delete</a>
                                                <form method="post" id="item-delete-{{ $room->id }}" action="{{ route('classrooms.destroy', $room->id) }}" class="hidden">@csrf @method('delete')</form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if($classrooms->isEmpty())
                        <p class="text-muted">No classrooms yet. Add rooms to assign them to timetable slots.</p>
                    @endif
                </div>

                @if(Qs::userIsTeamSA())
                <div class="tab-pane fade" id="new-classroom">
                    <div class="row">
                        <div class="col-md-6">
                            <form class="ajax-store" method="post" action="{{ route('classrooms.store') }}">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Room Number <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input name="room_number" value="{{ old('room_number') }}" required type="text" class="form-control" placeholder="e.g. R101">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Capacity</label>
                                    <div class="col-lg-9">
                                        <input name="capacity" value="{{ old('capacity', 30) }}" type="number" min="1" max="500" class="form-control" placeholder="30">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Building</label>
                                    <div class="col-lg-9">
                                        <input name="building" value="{{ old('building') }}" type="text" class="form-control" placeholder="e.g. Block A">
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button id="ajax-btn" type="submit" class="btn btn-primary">Add Room <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

@endsection
