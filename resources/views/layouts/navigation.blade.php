@php
    $user = auth()->user();
    $unreadRequests = $user?->receivedRequests()->where('status', 'pending')->count() ?? 0;
    $initial = strtoupper(substr($user?->nama ?? 'U', 0, 1));
@endphp

<a class="tb-sidebar-brand" href="{{ route('dashboard') }}" aria-label="Teman Belajar Udinus - Beranda">
    <span class="tb-brand-logo">
        <img src="{{ asset('img/logo.png') }}" alt="Teman Belajar Udinus">
    </span>
</a>

<nav class="tb-sidebar-nav" aria-label="Navigasi utama">
    @if ($user->isMahasiswa())
        <div class="tb-sidebar-section">Menu</div>
        <a class="tb-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <x-icon name="house-door" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Beranda</span>
        </a>
        @if (Route::has('rekomendasi.index'))
        <a class="tb-sidebar-link {{ request()->routeIs('rekomendasi.*') ? 'active' : '' }}" href="{{ route('rekomendasi.index') }}">
            <x-icon name="stars" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Rekomendasi</span>
        </a>
        @endif
        @if (Route::has('permintaan.index'))
        <a class="tb-sidebar-link {{ request()->routeIs('permintaan.*') ? 'active' : '' }}" href="{{ route('permintaan.index') }}">
            <x-icon name="envelope-paper" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Permintaan</span>
            @if ($unreadRequests > 0)
                <span class="tb-sidebar-badge" aria-label="{{ $unreadRequests }} permintaan baru">{{ $unreadRequests }}</span>
            @endif
        </a>
        @endif
        <a class="tb-sidebar-link {{ request()->routeIs('profil.*') ? 'active' : '' }}" href="{{ route('profil.edit') }}">
            <x-icon name="sliders" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Profil</span>
        </a>
        <a class="tb-sidebar-link {{ request()->routeIs('setting.*') ? 'active' : '' }}" href="{{ route('setting.index') }}">
            <x-icon name="gear" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Setting</span>
        </a>
        <a class="tb-sidebar-link {{ request()->routeIs('aktivitas.index') ? 'active' : '' }}" href="{{ route('aktivitas.index') }}">
            <x-icon name="clock-history" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Aktivitas</span>
        </a>
    @endif

    @if ($user->isAdmin())
        <div class="tb-sidebar-section">Admin</div>
        <a class="tb-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <x-icon name="speedometer2" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Dashboard</span>
        </a>
        @if (Route::has('admin.fakultas.index'))
        <a class="tb-sidebar-link {{ request()->routeIs('admin.fakultas.*') ? 'active' : '' }}" href="{{ route('admin.fakultas.index') }}">
            <x-icon name="diagram-3" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Fakultas</span>
        </a>
        @endif
        @if (Route::has('admin.prodi.index'))
        <a class="tb-sidebar-link {{ request()->routeIs('admin.prodi.*') ? 'active' : '' }}" href="{{ route('admin.prodi.index') }}">
            <x-icon name="mortarboard" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Program Studi</span>
        </a>
        @endif
        @if (Route::has('admin.opsi.index'))
        @php
            $opsiKategori = ['minat', 'tujuan', 'gaya', 'jadwal', 'mode'];
            $opsiAktif = request()->routeIs('admin.opsi.*');
        @endphp
        <div class="tb-sidebar-group" x-data="{ open: {{ $opsiAktif ? 'true' : 'false' }} }">
            <button type="button" class="tb-sidebar-link tb-sidebar-toggle {{ $opsiAktif ? 'active' : '' }}" @click="open = !open" :aria-expanded="open">
                <x-icon name="list-check" aria-hidden="true" />
                <span class="tb-sidebar-link-text">Opsi Preferensi</span>
                <x-icon name="chevron-down" class="tb-sidebar-chevron" aria-hidden="true" />
            </button>
            <div class="tb-sidebar-sub" x-show="open" x-cloak
                 x-transition:enter="tb-sub-trans"
                 x-transition:enter-start="tb-sub-start"
                 x-transition:enter-end="tb-sub-end"
                 x-transition:leave="tb-sub-trans"
                 x-transition:leave-start="tb-sub-end"
                 x-transition:leave-end="tb-sub-start">
                @foreach ($opsiKategori as $k)
                    <a class="tb-sidebar-sub-link {{ request('tipe') === $k ? 'active' : '' }}" href="{{ route('admin.opsi.index', ['tipe' => $k]) }}">{{ ucfirst($k) }}</a>
                @endforeach
            </div>
        </div>
        @endif
        @if (Route::has('admin.mahasiswa.index'))
        <a class="tb-sidebar-link {{ request()->routeIs('admin.mahasiswa.*') ? 'active' : '' }}" href="{{ route('admin.mahasiswa.index') }}">
            <x-icon name="people" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Mahasiswa</span>
        </a>
        @endif
        @if (Route::has('admin.aktivitas.index'))
        <a class="tb-sidebar-link {{ request()->routeIs('admin.aktivitas.*') ? 'active' : '' }}" href="{{ route('admin.aktivitas.index') }}">
            <x-icon name="clock-history" aria-hidden="true" />
            <span class="tb-sidebar-link-text">Aktivitas</span>
        </a>
        @endif
    @endif
</nav>

<div class="tb-sidebar-footer">
    <div class="tb-sidebar-user">
        <div class="tb-sidebar-avatar" aria-hidden="true">{{ $initial }}</div>
        <div class="tb-sidebar-user-info">
            <div class="name" title="{{ $user->nama }}">{{ $user->nama }}</div>
            <div class="role">{{ ucfirst($user->role) }}</div>
        </div>
    </div>
    <form method="POST" action="{{ $user->isAdmin() ? route('admin.logout') : route('logout') }}">
        @csrf
        <button type="submit" class="tb-sidebar-logout" title="Keluar" aria-label="Keluar">
            <x-icon name="box-arrow-right" aria-hidden="true" />
            <span>Keluar</span>
        </button>
    </form>
</div>
