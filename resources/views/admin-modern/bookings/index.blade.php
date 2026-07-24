@extends('admin-modern.layouts.app')

@section('title', 'Booking')

{{--
    Mirrors admin/bookings/index.blade.php, which is dead/unreachable code:
    no route in routes/admin.php ever calls view('admin.bookings.index')
    (the real "all bookings" page is admin.bookings.all). Kept as a
    structural placeholder rather than porting 200+ lines of the classic
    file's hardcoded mock data for a page nothing can ever navigate to.
--}}

@section('content')
    <div class="page-header">
        <h2 class="mb-1">Bookings Management</h2>
        <p class="text-muted mb-0">This page is not linked from anywhere in the app — see admin.bookings.all.</p>
    </div>
@endsection
