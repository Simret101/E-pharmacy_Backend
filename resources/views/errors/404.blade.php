@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Error') }}</div>

                <div class="card-body">
                    <p class="text-danger">
                        {{ $message }}
                    </p>

                    <a href="{{ route('home') }}" class="btn btn-primary">
                        {{ __('Back to Home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection