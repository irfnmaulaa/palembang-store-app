<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
    <!-- Container wrapper -->
    <div class="container-fluid">
        <!-- Navbar brand -->
        <a class="navbar-brand me-2 d-flex align-items-center gap-1" href="{{route('admin.dashboard')}}">
            <img src="{{ asset('/tb-palembang-logo.png') }}" alt="logo" style="height: 35px;"> {{config('app.name')}}
        </a>

        <!-- Toggle button -->
        <button
            data-mdb-collapse-init
            class="navbar-toggler"
            type="button"
            data-mdb-target="#navbarButtonsExample"
            aria-controls="navbarButtonsExample"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <i class="fas fa-bars"></i>
        </button>

        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarButtonsExample">
            <!-- Left links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                @foreach(collect(get_menus())->filter(function ($menu) { return in_array(auth()->user()->role, $menu->allowed_roles); })->all() as $menu)
                    <li class="nav-item">
                        <a class="nav-link {{ collect(explode('.', request()->route()->getName()))->slice(0, 2)->join('.') == collect(explode('.', $menu->link))->slice(0, 2)->join('.') ? 'active' : '' }}" href="{{route($menu->link)}}">
                            {{$menu->label}}
                        </a>
                    </li>
                @endforeach

            </ul>
            <!-- Left links -->

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        {!! auth()->user()->role_display !!} {{auth()->user()->name}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="document.getElementById('form-logout').submit()">Logout</a>
                    <form action="{{route('logout')}}" id="form-logout" method="POST">@csrf</form>
                </li>
            </ul>
        </div>
        <!-- Collapsible wrapper -->
    </div>
    <!-- Container wrapper -->
</nav>
