<section id="activities-section" class="actividades mb-10">
    <x-basic
        :title="$attributes['title']"
        :subtitle="$attributes['subtitle']"
        :text="$attributes['text']"
        :subtitleClass="$attributes['alignment']"
        :titleClass="$attributes['alignment']"
        class=""
    />
    <livewire:content-paginated
        :filter="$attributes['filter']"
        :typeContent="$attributes['type']"
        :perPage="$attributes['number']"
        :ids="$attributes['activities_id']??[]"
    />
</section>
