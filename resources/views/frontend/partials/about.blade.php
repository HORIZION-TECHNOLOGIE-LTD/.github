@php
    $lang = selectedLang();
    $system_default    = $default_language_code;
    $about_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::ABOUT_SECTION);
    $about = App\Models\Admin\SiteSections::getData($about_slug)->first();

@endphp

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="about-section pt-120">
    <div class="container">
        <div class="row mb-30-none align-items-center">
            <div class="col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="about-thumb-area scroll-reveal-left">
                    <div class="about-thumb image-reveal parallax-element" data-speed="0.03">
                        <img src="{{ get_image(@$about->value->images->image,'site-section') }}" alt="about" class="float-animation">
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="about-content-area scroll-reveal-right">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="section-header">
                                <span class="section-sub-titel"><i class="fas fa-qrcode"></i>{{ __($about->value->language->$lang->heading ?? $about->value->language->$system_default->heading) }}</span>
                                <h2 class="section-title">{{ __($about->value->language->$lang->sub_heading ?? $about->value->language->$system_default->sub_heading) }}</h2>
                                <p>{{ __($about->value->language->$lang->details ?? $about->value->language->$system_default->details) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="about-item-wrapper stagger-list">
                        @if(isset($about->value->items))
                        @php
                            $numKey = 0;
                        @endphp
                        @foreach($about->value->items ?? [] as $key => $item)
                        @php
                            $numKey += 1;
                        @endphp
                        <div class="about-content-item stagger-item glass-effect" style="animation-delay: {{ ($numKey - 1) * 0.15 }}s">
                            <div class="icon-area {{  $numKey == 1 ?'active':'' }} glow-border">
                                <i class="{{ $item->language->$lang->icon ?? $item->language->$system_default->icon }} "></i>
                            </div>
                            <div class="title-area">
                                <h4 class="title">{{ __($item->language->$lang->title ?? $item->language->$system_default->title )}}</h4>
                                <span class="sub-title">{{__( $item->language->$lang->sub_title ?? $item->language->$system_default->sub_title) }}</span>
                            </div>
                        </div>
                        @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
