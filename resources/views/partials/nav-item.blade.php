@php
    $children = ($menu->childrenRecursive ?? collect())->where('active', true);
    $hasChildren = $children->isNotEmpty();
    $isTop = ($level ?? 0) === 0;
    $resolved = $menu->resolved_url;
    $path = parse_url($resolved, PHP_URL_PATH);
    $active = $resolved === url()->current() || ($path && $path !== '/' && request()->is(ltrim($path, '/')));
@endphp
<li class="{{ $isTop ? 'nav-item' : '' }} {{ $hasChildren ? ($isTop ? 'dropdown' : 'dropend dropdown-submenu') : '' }}">
    <a
        class="{{ $isTop ? 'nav-link' : 'dropdown-item' }} {{ $hasChildren ? 'dropdown-toggle' : '' }} {{ $active ? 'active' : '' }}"
        href="{{ $hasChildren && !$menu->url ? '#' : $resolved }}"
        target="{{ $menu->target }}"
        @if($menu->target === '_blank') rel="noopener" @endif
        @if($hasChildren) data-bs-toggle="dropdown" aria-expanded="false" role="button" @endif
    >
        @if($menu->icon)<i class="bi {{ $menu->icon }} me-1"></i>@endif
        {{ $menu->title }}
    </a>
    @if($hasChildren)
        <ul class="dropdown-menu">
            @foreach($children as $child)
                @include('partials.nav-item', ['menu' => $child, 'level' => ($level ?? 0) + 1])
            @endforeach
        </ul>
    @endif
</li>
