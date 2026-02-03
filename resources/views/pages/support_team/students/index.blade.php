@extends('layouts.master')
@section('page_title', 'Student Information')
@section('content')

@if(session('flash_success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('flash_success') }}
        @if(session('student_login_info'))
            @php $loginInfo = session('student_login_info'); @endphp
            <hr>
            <strong>Student Login Credentials:</strong><br>
            <strong>Name:</strong> {{ $loginInfo['name'] }}<br>
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

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">All Students</h6>
        <div class="header-elements">
            <div class="list-icons">
                <a href="{{ route('students.create') }}" class="btn btn-primary">
                    <i class="icon-plus-circle2 mr-2"></i> Admit New Student
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Filter by Class:</label>
                <select id="class-filter" class="form-control select">
                    <option value="">All Classes</option>
                    @foreach($my_classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <table class="table datatable-button-html5-columns">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>ADM No</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                    <tr data-class-id="{{ $s->my_class_id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $s->user->photo }}" alt="photo"></td>
                        <td>{{ $s->user->name }}</td>
                        <td><code>{{ $s->user->username }}</code></td>
                        <td>
                            <code>student</code>
                            <small class="text-muted d-block">(Default password - student should change after first login)</small>
                        </td>
                        <td>{{ $s->adm_no }}</td>
                        <td>{{ $s->my_class->name ?? 'N/A' }}</td>
                        <td>{{ $s->section->name ?? 'No Section' }}</td>
                        <td>{{ $s->user->email ?? 'N/A' }}</td>
                        <td>{{ $s->user->phone ?? 'N/A' }}</td>
                        <td class="text-center">
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left">
                                        <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item">
                                            <i class="icon-eye"></i> View Profile
                                        </a>
                                        @if(Qs::userIsTeamSA())
                                            <a href="{{ route('students.edit', Qs::hash($s->id)) }}" class="dropdown-item">
                                                <i class="icon-pencil"></i> Edit
                                            </a>
                                            <a href="{{ route('students.reset_password', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                                <i class="icon-lock"></i> Reset Password
                                            </a>
                                        @endif
                                        @if(Qs::userIsSuperAdmin())
                                            <a id="{{ Qs::hash($s->user->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item">
                                                <i class="icon-trash"></i> Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ Qs::hash($s->user->id) }}" action="{{ route('students.destroy', Qs::hash($s->user->id)) }}" class="hidden">
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
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select').select2({
        placeholder: "Select a class",
        allowClear: true,
        width: '100%'
    });

    // Filter by class
    $('#class-filter').on('change', function() {
        var classId = $(this).val();
        if (classId) {
            $('tbody tr').hide();
            $('tbody tr[data-class-id="' + classId + '"]').show();
        } else {
            $('tbody tr').show();
        }
    });
});

</script>
@endsection
