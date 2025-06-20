@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Email Verified') }}</div>

                <div class="card-body">
                    <p class="text-success">
                        Your email has been verified successfully!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection