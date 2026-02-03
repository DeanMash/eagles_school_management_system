@extends('layouts.master')

@section('title', 'Terms of Use')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Terms of Use</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset('global_assets/images/logo.png') }}" alt="Eagles Logo" style="height: 50px; width: auto; margin-bottom: 10px; opacity: 0.85;"/>
                    </div>
                    <h4>Terms of Use for <img src="{{ asset('global_assets/images/logo.png') }}" alt="Eagles Logo" style="height: 30px; width: auto; vertical-align: middle; margin: 0 8px; opacity: 0.85;"> {{ $app_name ?? 'Eagles School Management System' }}</h4>
                    
                    <h5>1. Acceptance of Terms</h5>
                    <p>By accessing and using the <img src="{{ asset('global_assets/images/logo.png') }}" alt="Eagles Logo" style="height: 20px; width: auto; vertical-align: middle; margin: 0 5px; opacity: 0.85;"> {{ $app_name ?? 'Eagles School Management System' }}, you accept and agree to be bound by the terms and provision of this agreement.</p>
                    
                    <h5>2. Use License</h5>
                    <p>The system is provided for educational and administrative purposes only. Users are granted a limited, non-exclusive license to use the system in accordance with school policies.</p>
                    
                    <h5>3. User Responsibilities</h5>
                    <p>Users are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of their login credentials</li>
                        <li>Using the system only for its intended educational purposes</li>
                        <li>Not sharing sensitive information with unauthorized parties</li>
                        <li>Complying with all school policies and regulations</li>
                    </ul>
                    
                    <h5>4. System Availability</h5>
                    <p>We strive to maintain system availability but do not guarantee uninterrupted access. Maintenance and updates may require temporary downtime.</p>
                    
                    <h5>5. Contact Information</h5>
                    <p>For questions about these Terms of Use, please contact: {{ $contact_phone ?? 'School Administration' }}</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection