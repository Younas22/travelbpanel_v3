@extends('admin-nova.layouts.app')

@section('title', 'Edit Currency')

@section('content')
<div class="tbp-admin-page is-narrow" id="crPage">
    <a href="{{ route('admin.currencies.index') }}" class="tbp-admin-back"><i class="bi bi-arrow-left"></i> Back to Currencies</a>
    <header class="tbp-admin-header"><div><h1>Edit Currency</h1><p>Update currency details and exchange rate</p></div></header>
    @include('admin-nova.currencies._form', ['currency' => $currency])
</div>
@endsection
