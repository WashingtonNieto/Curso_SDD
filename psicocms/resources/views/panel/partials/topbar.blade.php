<header class="topbar">
    <button type="button" class="topbar__icon-btn topbar__menu" data-sidebar-open aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menú">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>

    <form class="topbar__search" role="search" method="GET" action="{{ route('panel.search') }}">
        <label class="sr-only" for="topbar-search">Buscar en el panel</label>
        <i class="topbar__search-icon fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input class="topbar__search-input" type="search" id="topbar-search" name="q" value="{{ request()->routeIs('panel.search') ? request('q') : '' }}" placeholder="Buscar pacientes, citas, artículos…" autocomplete="off" maxlength="100">
    </form>

    <div class="topbar__actions">
        <div class="dropdown" data-dropdown>
            <button type="button" class="topbar__icon-btn" data-dropdown-toggle aria-expanded="false" aria-haspopup="true" aria-controls="notifications-menu" aria-label="Notificaciones">
                <i class="fa-regular fa-bell" aria-hidden="true"></i>
            </button>
            <div class="dropdown__menu dropdown__menu--wide" id="notifications-menu" hidden>
                <p class="dropdown__title">Notificaciones</p>
                <div class="dropdown__empty">
                    <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
                    <span>No tienes notificaciones nuevas.</span>
                </div>
            </div>
        </div>

        <button type="button" class="topbar__icon-btn" data-appearance-toggle aria-label="Apariencia del panel (modo claro u oscuro y color)">
            <i class="fa-solid {{ ($user->panel_mode ?? 'light') === 'dark' ? 'fa-sun' : 'fa-moon' }}" aria-hidden="true"></i>
        </button>

        <a class="topbar__icon-btn topbar__help" href="{{ route('panel.help') }}" aria-label="Ayuda" data-tooltip="Ayuda: aprende a usar tu panel paso a paso">
            <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
        </a>

        <div class="dropdown" data-dropdown>
            <button type="button" class="topbar__user" data-dropdown-toggle aria-expanded="false" aria-haspopup="true" aria-controls="user-menu">
                <x-panel.avatar :user="$user" />
                <span class="topbar__user-name">{{ $user->first_name }}</span>
                <i class="topbar__user-chevron fa-solid fa-chevron-down" aria-hidden="true"></i>
            </button>
            <div class="dropdown__menu" id="user-menu" hidden>
                <div class="dropdown__header">
                    <strong>{{ $user->full_name }}</strong>
                    <span>{{ $user->email }}</span>
                </div>
                <a class="dropdown__item" href="{{ route('panel.profile') }}">
                    <i class="fa-solid fa-user-gear" aria-hidden="true"></i> Mi perfil
                </a>
                <a class="dropdown__item" href="{{ url('/') }}" target="_blank" rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Ver mi web
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown__item dropdown__item--danger">
                        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
