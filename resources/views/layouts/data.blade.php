@extends('layouts.app')


@section('content')
    <div class="container">
        @include('flash::message')

        @yield('data-content')
    </div>
@endsection

@section('script')
    <script>
        $('div.alert').not('.alert-important').delay(3000).fadeOut(350);
    </script>

    @yield('data-script')
@endsection
