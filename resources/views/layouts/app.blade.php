<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - Synth</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('synth::partials.styles')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
            crossorigin="anonymous"></script>

    @livewireStyles

    @livewireScripts
    
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-md-2 px-0 side-bar">
            <div
                class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                    <span
                        class="d-flex align-items-center pt-1 md-0 md-auto text-white text-decoration-none">
                        <span class="logo d-sm-inline">
                            @include('synth::partials.logo')
                        </span>
                    </span>
                <span
                    class="d-flex flex-column pb-3 pt-1 md-0 md-auto text-synth text-decoration-none border-bottom">
                            @livewire('synth-system-stats')
                        </span>
                @include('synth::partials.navigation')
            </div>
        </div>
        <div class="col py-3 body-area">
            @section('content')@show
        </div>
    </div>
</div>
@section('extra-content')
@show

@livewire('synth-approve-write')


@section('javascript')@show

</body>
</html>
