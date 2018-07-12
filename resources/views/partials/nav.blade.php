
<nav class="navbar navbar-expand-lg navbar-dark bg-grey navbar-default navbar-static-top">
    <!-- Branding Image -->
    <a class="navbar-brand" href="{{ url('/home') }}">
        <img src="img/logo/test-icon2.png" alt="logo" id="logo">
        <span>{{ config('app.name', 'Cybermatics') }}</span>
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

                <navigation
                        :title="'Library'"
                        :link="'/library'">
                </navigation>
                <navigation
                        :title="'Chat'"
                        :link="'/chat'">
                </navigation>
                <navigation
                        :title="'Terminal'"
                        :link="'/terminal'">
                </navigation>
                <navigation
                        :title="'Email'"
                        :link="'/email'"
                        :notifications="$store.state.notifications.newEmails">
                </navigation>
                <li class="nav-item dropdown" style="text-transform: uppercase !important; font-family: 'Lato', sans-serif; font-weight: 200;">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu">
                        @if(Auth::user()->role == 2)
                            <a class="dropdown-item" href="/classes">Classes</a>
                        @endif
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