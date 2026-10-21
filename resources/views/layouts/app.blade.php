<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'Peminjaman alat')</title>
    <link rel="stylesheet" href="{{ asset('resources/css/bootstrap.min.css') }}">
</head>

<body class="bg-light">
    @auth
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a href="{{ route('dashboard') }}" class="navbar-brand">Peminjaman Alat</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    @if (auth()->user()->role === 'admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('user.index') }}">Data User</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('kategori.index') }}">Kategori</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('alat.index') }}">Alat</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.peminjaman.index') }}">Peminjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.pengembalian.index') }}">Pengembalian</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('log.index') }}">Log Aktivitas</a></li>
                    @elseif (auth()->user()->role === 'petugas')
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('alat.daftar') }}">Daftar Alat</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('peminjaman.persetujuan') }}">Persetujuan Peminjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pengembalian.pantau') }}">Pantau Pengembalian</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('laporan.index') }}">Laporan</a></li>
                    @elseif (auth()->user()->role === 'peminjam')
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('alat.daftar') }}">Daftar Alat</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('peminjaman.ajukan') }}">Ajukan Peminjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('peminjaman.saya') }}">Peminjaman Saya</a></li>
                    @endif
                </ul>
                <span class="navbar-text me-3">
                    {{ auth()->user()->nama_lengkap }} ({{ auth()->user()->role }})
                </span>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="btn btn-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    <div class="container py-4">
        @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
        @endif
        @if (session('gagal'))
        <div class="alert alert-danger">{{ session('gagal') }}</div>
        @endif
        @yield('isi')
    </div>

    <script src="{{ asset('resources/js/bootstrap.bundle.js') }}"></script>
</body>

</html>