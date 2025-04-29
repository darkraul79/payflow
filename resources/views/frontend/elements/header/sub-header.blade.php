@if (isset($post) || ! $page->is_home)
    @includeWhen($page, 'frontend.elements.header.quotes')
    @includeWhen(isset($post), 'frontend.elements.header.activityTitle')

    @includeWhen($page && ! isset($post), 'frontend.elements.title')

    <x-breadcrumbs :page="$page" :post="$post??null" />
@endif
