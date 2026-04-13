<!-- resources/views/user/ai-director/movie.blade.php -->
@extends('layouts.app')

@section('css')
<link href="{{ theme_url('custom/aidirector.css') }}" rel="stylesheet" />
<link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
@livewire('movie-producer')
@endsection

@section('js')
<script src="{{URL::asset('plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@livewireScripts
@stack('scripts')
@endsection