
<nav class="navbar navbar-expand-lg navbar-light bg-white navbar-default navbar-static-top">
    <!-- Branding Image -->
    <a class="navbar-brand" href="{{ url('/home') }}">
        {{ config('app.name', 'Museum of Spain') }}
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#app-navbar-collapse" aria-controls="app-navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>


    <div class="collapse navbar-collapse" id="app-navbar-collapse">
        <!-- Left Side Of Navbar -->
        <ul class="nav navbar-nav">
            &nbsp;
        </ul>

        <!-- Right Side Of Navbar -->
        <ul class="navbar-nav ml-auto">
            <!-- Authentication Links -->
            @guest
                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
            @else
                <li class="nav-item"><a class="nav-link" href="/library">Library</a></li>
                <li class="nav-item"><a class="nav-link" href="/gallery">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="/videocall">Video Call</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="/email">Email
                        <span class="badge badge-pill badge-danger">{{ $notifications }}</span>
                    </a>
                </li>
                <navigation></navigation>
                <navigation></navigation>
                <navigation></navigation>
                <navigation></navigation>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/classes">Classes</a>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
            @endguest
        </ul>
    </div>
</nav>

