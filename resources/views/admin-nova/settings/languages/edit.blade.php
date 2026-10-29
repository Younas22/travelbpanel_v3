@extends('admin-nova.layouts.app')

@section('title', 'Edit Language')

@section('content')
@include('admin-nova.settings.languages._form', ['language' => $language])
@endsection
