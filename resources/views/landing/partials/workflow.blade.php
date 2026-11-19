{{-- Cara Kerja Layanan --}}
<section id="cara-kerja" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24 landing-section">
    <div class="max-w-2xl mb-10 sm:mb-14">
        <span class="text-xs font-semibold uppercase tracking-wider text-[#062072] block mb-2">{{ __('public.workflow.badge') }}</span>
        <h2 class="text-2xl sm:text-3xl font-bold text-[#101828] tracking-tight m-0">
            {{ __('public.workflow.title') }}
        </h2>
        <p class="text-sm sm:text-base text-[#667085] mt-2 m-0">
            {{ __('public.workflow.subtitle') }}
        </p>
    </div>

    {{-- Horizontal / Vertical Animated Timeline (No cards or box borders) --}}
    <div class="workflow-timeline relative" id="workflow-timeline" aria-label="Alur Cara Kerja Layanan">
        {{-- Track Lines --}}
        <div class="workflow-track-bg" aria-hidden="true"></div>
        <div class="workflow-track-fill" id="workflow-track-fill" aria-hidden="true"></div>

        {{-- Steps --}}
        <div class="workflow-steps">
            {{-- Langkah 01 --}}
            <div class="workflow-step" data-step="1">
                <div class="workflow-node-wrapper">
                    <div class="workflow-node" id="workflow-node-1">
                        <span class="workflow-node-num">01</span>
                    </div>
                </div>
                <div class="workflow-content">
                    <h3 class="workflow-step-title">{{ __('public.workflow.step_1') }}</h3>
                    <p class="workflow-step-desc">{{ __('public.workflow.desc_1') }}</p>
                </div>
            </div>

            {{-- Langkah 02 --}}
            <div class="workflow-step" data-step="2">
                <div class="workflow-node-wrapper">
                    <div class="workflow-node" id="workflow-node-2">
                        <span class="workflow-node-num">02</span>
                    </div>
                </div>
                <div class="workflow-content">
                    <h3 class="workflow-step-title">{{ __('public.workflow.step_2') }}</h3>
                    <p class="workflow-step-desc">{{ __('public.workflow.desc_2') }}</p>
                </div>
            </div>

            {{-- Langkah 03 --}}
            <div class="workflow-step" data-step="3">
                <div class="workflow-node-wrapper">
                    <div class="workflow-node" id="workflow-node-3">
                        <span class="workflow-node-num">03</span>
                    </div>
                </div>
                <div class="workflow-content">
                    <h3 class="workflow-step-title">{{ __('public.workflow.step_3') }}</h3>
                    <p class="workflow-step-desc">{{ __('public.workflow.desc_3') }}</p>
                </div>
            </div>

            {{-- Langkah 04 --}}
            <div class="workflow-step" data-step="4">
                <div class="workflow-node-wrapper">
                    <div class="workflow-node" id="workflow-node-4">
                        <span class="workflow-node-num">04</span>
                    </div>
                </div>
                <div class="workflow-content">
                    <h3 class="workflow-step-title">{{ __('public.workflow.step_4') }}</h3>
                    <p class="workflow-step-desc">{{ __('public.workflow.desc_4') }}</p>
                </div>
            </div>

            {{-- Langkah 05 --}}
            <div class="workflow-step" data-step="5">
                <div class="workflow-node-wrapper">
                    <div class="workflow-node" id="workflow-node-5">
                        <span class="workflow-node-num">05</span>
                    </div>
                </div>
                <div class="workflow-content">
                    <h3 class="workflow-step-title">{{ __('public.workflow.step_5') }}</h3>
                    <p class="workflow-step-desc">{{ __('public.workflow.desc_5') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
