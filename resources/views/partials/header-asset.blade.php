<!-- favicon -->
<link rel="shortcut icon" href="{{ get_fav($basic_settings) }}" type="image/x-icon">
<!-- fontawesome css link -->
   <!-- fontawesome css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/fontawesome-all.min.css">
   <!-- line-awesome-icon css -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/line-awesome.min.css">
   <!-- bootstrap css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/bootstrap.min.css">
   <!-- swipper css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/swiper.min.css">
   <!-- animate css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/animate.css">

   <link rel="stylesheet" href="{{ asset('public/backend/css/select2.min.css') }}">
   <!-- nice select css -->
    <link rel="stylesheet" href="{{ asset('public/frontend/css/nice-select.css') }}">
      <!-- odometer css link -->
      <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/odometer.css">
      <!-- main style css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/style.css">
   <!-- enhanced animations css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/enhanced-animations.css">
   <!-- section enhancements css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/section-enhancements.css">
   <!-- chibank dynamic effects css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/chibank-dynamic-effects.css">
   <!-- chibank premium effects css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/chibank-premium-effects.css">
   <!-- black and white theme css link -->
   <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/bw-theme.css">
   @php
   $color = @$basic_settings->base_color ?? '#000000';
@endphp

<style>
   :root {
       --primary-color: {{$color}};
   }

</style>
