<aside class="sidebar" id="sidebar" data-sidebar aria-label="Menú principal">
    <div class="sidebar__head">
        <a class="brand sidebar__brand" href="{{ route('panel.home') }}">
            <span class="brand__logo"><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
            <span>
                <span class="brand__name">PsicoCMS</span>
                <span class="brand__tagline">Panel de gestión</span>
            </span>
        </a>
        <button type="button" class="sidebar__close" data-sidebar-close aria-label="Cerrar menú">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <a class="btn btn--primary btn--block sidebar__cta" href="{{ route('panel.appointments.create') }}">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
        Nueva cita
    </a>

    <nav class="sidebar__nav" aria-label="Secciones del panel">
        <ul class="sidebar__menu">
            @foreach (config('panel.menu') as $item)
                @if (! empty($item['children']))
                    @php
                        $groupOpen = collect($item['children'])->contains(fn ($child) => request()->routeIs($child['match']));
                        $submenuId = 'submenu-'.$item['id'];
                    @endphp
                    <li class="sidebar__group {{ $groupOpen ? 'is-open' : '' }}">
                        <button type="button" class="sidebar__link sidebar__toggle {{ $groupOpen ? 'is-active-parent' : '' }}"
                            aria-expanded="{{ $groupOpen ? 'true' : 'false' }}" aria-controls="{{ $submenuId }}" data-submenu-toggle>
                            <i class="sidebar__icon {{ $item['icon'] }}" aria-hidden="true"></i>
                            <span class="sidebar__label">{{ $item['label'] }}</span>
                            <i class="sidebar__chevron fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                        <ul class="sidebar__submenu" id="{{ $submenuId }}" @unless ($groupOpen) hidden @endunless>
                            @foreach ($item['children'] as $child)
                                @php($childActive = request()->routeIs($child['match']))
                                <li>
                                    <a class="sidebar__sublink {{ $childActive ? 'is-active' : '' }}" href="{{ route($child['route']) }}" @if ($childActive) aria-current="page" @endif>
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    @php($active = request()->routeIs($item['match']))
                    <li>
                        <a class="sidebar__link {{ $active ? 'is-active' : '' }}" href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif>
                            <i class="sidebar__icon {{ $item['icon'] }}" aria-hidden="true"></i>
                            <span class="sidebar__label">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>

    <div class="sidebar__footer">
        <a class="sidebar__link sidebar__link--web" href="{{ url('/') }}" target="_blank" rel="noopener">
            <i class="sidebar__icon fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            <span class="sidebar__label">Ver mi web</span>
        </a>
        <a class="sidebar__link {{ request()->routeIs('panel.help') ? 'is-active' : '' }}" href="{{ route('panel.help') }}">
            <i class="sidebar__icon fa-regular fa-life-ring" aria-hidden="true"></i>
            <span class="sidebar__label">Ayuda</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar__link sidebar__link--logout">
                <i class="sidebar__icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                <span class="sidebar__label">Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
