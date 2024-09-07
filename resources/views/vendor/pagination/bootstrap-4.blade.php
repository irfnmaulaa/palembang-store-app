@if ($paginator->hasPages())
    <nav>
        <div class="pagination mb-0 btn-group btn-group-lg shadow-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <a href="#" class="btn btn-outline-primary disabled">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline-primary">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <a href="#" class="btn btn-outline-primary disabled">
                        {{ $element }}
                    </a>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn btn-primary">{{ $page }}</span>
                        @else
                            <a class="btn btn-outline-primary" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline-primary">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <a href="#" class="btn btn-outline-primary disabled">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @endif
        </div>
    </nav>
@endif
