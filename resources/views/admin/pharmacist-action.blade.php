@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ $title }}
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($action === 'approved')
                        <p>The pharmacist has been successfully approved.</p>
                        <p>Name: {{ $pharmacist->name }}</p>
                        <p>Email: {{ $pharmacist->email }}</p>
                        <p>License Number: {{ $pharmacist->license_number }}</p>
                    @else
                        <p>The pharmacist has been successfully rejected.</p>
                        <p>Name: {{ $pharmacist->name }}</p>
                        <p>Email: {{ $pharmacist->email }}</p>
                        <p>License Number: {{ $pharmacist->license_number }}</p>
                        <p>Reason: {{ $reason }}</p>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
