@auth
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
        <a href="">STISLA</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
        <a href="">STISLA</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li class="{{ Request::is('home') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('home') }}"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>
            @if (Auth::user()->role == 'superadmin')
            <li class="menu-header">Hak Akses</li>
            <li class="{{ Request::is('hakakses') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('hakakses') }}"><i class="fas fa-user-shield"></i> <span>Hak Akses</span></a>
            </li>
            @endif
            <!-- profile ganti password -->
            <li class="menu-header">Profile</li>
            <li class="{{ Request::is('profile/edit') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('profile/edit') }}"><i class="far fa-user"></i> <span>Profile</span></a>
            </li>
            <li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('profile/change-password') }}"><i class="fas fa-key"></i> <span>Ganti Password</span></a>
            </li>
            <!-- Menu Metabase -->
            <li class="menu-header">Metabase</li>
            <li class="{{ Request::is('metabase') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('metabase.index') }}">
                    <i class="fas fa-database"></i> <span>Data Metabase</span>
                </a>
            </li>
            <!-- Menu Backup -->
            <li class="menu-header">Data Management</li>
            <li class="{{ Request::is('index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('index.index') }}">
                    <i class="fas fa-sync"></i> <span>Backup Data Sicantik</span>
                </a>
            </li>
            <!-- Kategori Metabase Dinamis -->
            @php
                $categories = App\Models\Metabase::select('kategori')
                    ->distinct()
                    ->orderBy('kategori')
                    ->get();
            @endphp

            @if($categories->count() > 0)
                <li class="menu-header">Kategori Dashboard</li>
                @foreach($categories as $category)
                    <li class="{{ Request::is('metabase/kategori/'.$category->kategori) ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('metabase.by.category', $category->kategori) }}">
                            <i class="fas fa-chart-line"></i> <span>{{ $category->kategori }}</span>
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
    </aside>
</div>
@endauth
