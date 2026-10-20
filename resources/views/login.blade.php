@extends('layouts.app')
@section('judul', 'Login')
@section('isi')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="text-center mb-4">Login</h4>

                @error('username')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <form action="{{ route('login.proses') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection