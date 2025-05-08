<section id="activities-section" class="actividades">
    <x-basic
        :title="$attributes['title']"
        :subtitle="$attributes['subtitle']"
        :text="$attributes['text']"
        subtitleClass="text-center"
        titleClass="text-center"
        class=""
    />

    <livewire:content-paginated
        :filter="$attributes['filter']"
        :typeContent="$attributes['type']"
        :perPage="$attributes['number']"
        :ids="$attributes['activities_id']??[]"
    />
</section>
