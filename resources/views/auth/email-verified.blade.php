<!-- resources/views/auth/email-verified.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Email Verified') }}</div>

                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Success!</h4>
                        <p>Your email has been successfully verified.</p>
                        <hr>
                        <p class="mb-0">You can now use all features of the application.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection