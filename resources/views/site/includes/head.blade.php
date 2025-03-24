<!-- Dynamic Breadcrumb -->
<div class="container-fluid bg-breadcrumb {{ $bgClass ?? 'bg-secondary' }}">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white {{ $titleClass ?? 'display-4' }} mb-4 wow fadeInDown" data-wow-delay="0.1s">
            {{ $pageTitle ?? 'Page Title' }}
        </h4>
        <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown {{ $breadcrumbClass ?? '' }}"
            data-wow-delay="0.3s">
            <li class="breadcrumb-item {{ $homeLinkClass ?? '' }}">
                <a href="{{ route('home') }}" class="{{ $homeLinkTextClass ?? '' }}">Home</a>
            </li>
            @if(isset($parentPage))
                <li class="breadcrumb-item {{ $parentPageClass ?? '' }}">
                    <a href="{{ $parentPage['url'] ?? route('home') }}" class="{{ $parentPageLinkClass ?? '' }}">
                        {{ $parentPage['name'] ?? 'Pages' }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active {{ $currentPageClass ?? 'text-primary' }}">
                {{ $currentPage ?? 'Current Page' }}
            </li>
        </ol>
    </div>
</div>