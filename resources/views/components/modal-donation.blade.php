<div>
    <div
        x-data="{ modalIsOpen: false }"
        @openmodaldonation.window=" modalIsOpen = true"
    >
        <div
            x-cloak
            x-show="modalIsOpen"
            x-transition.opacity.duration.200ms
            x-trap.inert.noscroll="modalIsOpen"
            x-on:keydown.esc.window="(modalIsOpen = false), $dispatch('resetDonation')"
            x-on:click.self="(modalIsOpen = false), $dispatch('resetDonation')"
            class="fixed inset-0 z-30 flex items-end justify-center overflow-auto bg-black/70 p-4 pb-8 sm:items-center lg:p-8"
            {{-- x-on:wire:click.self="" --}}
            role="dialog"
            aria-modal="true"
            aria-labelledby="defaultModalTitle"
        >
            <!-- Modal Dialog -->
            <div
                id="modalDonation"
                x-show="modalIsOpen"
                x-transition:enter="transition delay-100 duration-200 ease-out motion-reduce:transition-opacity"
                x-transition:enter-start="scale-110 opacity-0"
                x-transition:enter-end="scale-100 opacity-100"
                class="top-[100px] lg:top-0"
            >
                <div
                    class="z-50 flex w-full flex-col items-end justify-end p-4 lg:max-w-xl"
                >
                    <button
                        class="my-4 w-fit cursor-pointer rounded-full border border-gray-300"
                        x-on:click="$dispatch('resetDonation')"
                        @click="modalIsOpen = false"
                        data-test="DonacionButtonModalClose"
                    >
                        <x-heroicon-c-x-mark class="h-8 w-8" />
                    </button>
                    <livewire:donacion-banner prefix="modal" wire:key="modal" />
                </div>
            </div>
        </div>
    </div>
</div>
