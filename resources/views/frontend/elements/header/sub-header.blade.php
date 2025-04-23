@if (isset($post) || ! $page->is_home)
    @includeWhen($page, 'frontend.elements.header.quotes')
    @includeWhen(isset($post), 'frontend.elements.header.activityTitle')

    @includeUnless(isset($page), 'frontend.elements.title')

    <x-breadcrumbs />
@endif
