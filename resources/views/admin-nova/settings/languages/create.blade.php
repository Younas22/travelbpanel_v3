@extends('admin-nova.layouts.app')

@section('title', 'Add Language')

@section('content')
@include('admin-nova.settings.languages._form', ['language' => null])
@endsection
