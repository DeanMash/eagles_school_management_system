@extends('layouts.master')

@section('title', 'My Account')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Account Settings</h3>
    </div>
    <div class="card-body">
        <div class="list-group">
            <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">
                <i class="icon-user mr-2"></i> View Profile
            </a>
            <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                <i class="icon-pencil mr-2"></i> Edit Profile
            </a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="list-group-item list-group-item-action text-danger">
                <i class="icon-switch2 mr-2"></i> Logout
            </a>
        </div>
    </div>
</div>
@endsection