@extends('layouts.app') {{-- Replaces header/footer include --}}

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            {{-- Dynamic Sub-view Component Switcher --}}
            @if($action == 'view_slip_list')
                @include('insurance_broking.partials.slip_list')
                
            @elseif($action == 'view_claim_list')
                @include('insurance_broking.partials.claim_list')
                
            @elseif($action == 'view_vehicle_list')
                @include('insurance_broking.partials.vehicle_list')
                
            {{-- Added this block to map seamlessly to your controller logic --}}
            @elseif($action == 'view_cancelled_slip_list')
                @include('insurance_broking.partials.cancelled_slips')
            @endif

        </div>
    </div>
</div>
@endsection