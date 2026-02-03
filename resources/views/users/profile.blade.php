@extends('layouts.master')

@section('title', 'My Profile')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Profile</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="{{ $user->photo }}" alt="Profile Photo" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ ucfirst($user->user_type) }}</p>
                
                <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">Edit Profile</a>
            </div>
            <div class="col-md-9">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $user->phone ?? 'Not provided' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $user->address ?? 'Not provided' }}</td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td>{{ $user->gender ?? 'Not provided' }}</td>
                    </tr>
                    @if($user->dob)
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{ \Carbon\Carbon::parse($user->dob)->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection