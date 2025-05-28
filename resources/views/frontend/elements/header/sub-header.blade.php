@includeWhen(hasQuotes(getTypeContent($page)) && ! $page->is_home_page, 'frontend.elements.header.quotes')
@includeWhen(hasActivityTitle(getTypeContent($page)), 'frontend.elements.header.activityTitle')

<x-title-section :model="$page" />

@if (! $page->is_home_page)
    <x-breadcrumbs :page="$page" type="{{getTypeContent($page)}}" />
@endif
