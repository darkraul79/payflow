@if (! $page->is_home)
    @include('frontend.elements.quotes')

    @include('frontend.elements.title')

    <x-breadcrumbs />
@endif
