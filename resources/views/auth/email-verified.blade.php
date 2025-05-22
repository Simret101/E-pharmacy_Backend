@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Email Verification') }}</div>

                <div class="card-body">
                    @if($status === 'success')
                        <div class="alert alert-success">
                            {{ $message }}
                        </div>
                    @else
                        <div class="alert alert-danger">
                            {{ $message }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection