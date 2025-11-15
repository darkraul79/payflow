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
            class="fixed inset-0 z-30  items-end sm:justify-center overflow-auto bg-black/70  sm:items-center h-[100vh] m-0 p-0 block"
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
            >
                <div
                    class="z-50 flex w-full flex-col items-end justify-end p-1 sm:p-4 lg:max-w-xl"
                >
                    <button
                        class="sm:my-4 m-0 mb-1 w-fit cursor-pointer rounded-full border border-gray-300 absolute top-3 right-3 sm:top-0 sm:right-0 sm:relative"
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
