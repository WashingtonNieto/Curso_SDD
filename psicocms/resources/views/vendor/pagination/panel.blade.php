@if ($paginator->total() > 0)
    <nav class="pagination" aria-label="Paginación">
        <p class="pagination__info">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>

        @if ($paginator->hasPages())
            <ul class="pagination__list">
                <li>
                    @if ($paginator->onFirstPage())
                        <span class="pagination__link is-disabled" aria-disabled="true">
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Anterior
                        </span>
                    @else
                        <a class="pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Anterior
                        </a>
                    @endif
                </li>

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span class="pagination__dots" aria-hidden="true">…</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li class="pagination__page">
                                @if ($page == $paginator->currentPage())
                                    <span class="pagination__link is-current" aria-current="page">{{ $page }}</span>
                                @else
                                    <a class="pagination__link" href="{{ $url }}" aria-label="Ir a la página {{ $page }}">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach

                <li>
                    @if ($paginator->hasMorePages())
                        <a class="pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            Siguiente <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <span class="pagination__link is-disabled" aria-disabled="true">
                            Siguiente <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                        </span>
                    @endif
                </li>
            </ul>
        @endif
    </nav>
@endif
