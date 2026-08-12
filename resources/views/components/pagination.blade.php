@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span style="opacity:0.4; background:var(--glass-bg); border:1px solid var(--border-color); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:13px; color:var(--text-secondary);">‹</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" style="background:var(--glass-bg); border:1px solid var(--border-color); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:13px; color:var(--text-secondary); text-decoration:none; transition:all 0.25s;" onmouseover="this.style.background='var(--bg-card-hover)'" onmouseout="this.style.background='var(--glass-bg)'">‹</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span style="background:var(--glass-bg); border:1px solid var(--border-color); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:13px; color:var(--text-muted);">...</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span style="background:var(--accent-primary); border:1px solid var(--accent-primary); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:13px; color:#0a0e1a; font-weight:700;">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" style="background:var(--glass-bg); border:1px solid var(--border-color); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:13px; color:var(--text-secondary); text-decoration:none; transition:all 0.25s;" onmouseover="this.style.background='var(--bg-card-hover)'; this.style.color='var(--text-primary)'" onmouseout="this.style.background='var(--glass-bg)'; this.style.color='var(--text-secondary)'">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" style="background:var(--glass-bg); border:1px solid var(--border-color); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:13px; color:var(--text-secondary); text-decoration:none; transition:all 0.25s;" onmouseover="this.style.background='var(--bg-card-hover)'" onmouseout="this.style.background='var(--glass-bg)'">›</a>
    @else
        <span style="opacity:0.4; background:var(--glass-bg); border:1px solid var(--border-color); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:13px; color:var(--text-secondary);">›</span>
    @endif
@endif
