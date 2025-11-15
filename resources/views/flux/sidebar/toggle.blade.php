<flux:button
    :attributes="$attributes->merge([
        'class' => 'shrink-0 hover:text-azul-sea! border-azul-gray border-1 rounded-full! cursor-pointer',
        'variant' => 'subtle',
    ])"
    :square="true"
    :x-data="true"
    x-on:click="
        document.body.hasAttribute('data-show-stashed-sidebar')
            ? document.body.removeAttribute('data-show-stashed-sidebar')
            : document.body.setAttribute('data-show-stashed-sidebar', '')
    "
    :data-flux-sidebar-toggle="true"
    aria-label="{{ __('Toggle sidebar') }}"
/>
