{{-- 1. Extend the master layout --}}
@extends('layouts.app')

{{-- 3. Push custom CSS into the layout's header stack --}}
@push('styles')
<style type="text/css">
  .my_ib_link {
    color: #0004FF;
    text-decoration: none;
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 4px;
    transition: 0.3s;
  }
  .my_ib_link:hover {
    background-color: #f0f0f0;
  }
  .search-container {
    margin: 20px 0;
    position: relative;
    max-width: 400px;
  }
  .search-input {
    width: 100%;
    padding: 10px 15px 10px 35px;
    border: 1px solid #ddd;
    border-radius: 25px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
  }
  .search-input:focus {
    border-color: #0004FF;
    box-shadow: 0 0 8px rgba(0, 4, 255, 0.1);
  }
  .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
  }
  .invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background-color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    font-family: sans-serif;
  }
  .invoice-table th {
    background-color: #f8f9fa;
    color: #333;
    text-align: left;
    padding: 12px 15px;
    border-bottom: 2px solid #dee2e6;
    font-size: 13px;
    text-transform: uppercase;
  }
  .invoice-table td {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    color: #555;
    vertical-align: middle;
  }
  .invoice-table tr:hover {
    background-color: #fcfcfc;
  }
  .btn-generate {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    font-size: 12px;
    transition: background 0.2s;
  }
  .btn-generate:hover {
    background-color: #a71d2a;
  }
  .slpn-badge {
    background: #fff0f0;
    color: #dc3545;
    padding: 3px 7px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 12px;
  } 
</style>
@endpush

{{-- 4. Inject structural body content into the core template body yield wrapper --}}
@section('content')
<div class="custom_container" style="padding: 5%;">


  <div class="container">
    <h6 style="text-align: center; font-size: 20px; margin-bottom: 10px;">Generate Credit Notes</h6>

    {{-- Notification Alerts --}}
    @if (session('error_message'))
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> {{ session('error_message') }}
        </div>
    @endif

    @if (session('success_message'))
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> {{ session('success_message') }}
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
      <div class="search-container">
        <i class="fa fa-search search-icon"></i>
        <input type="text" id="creditSearch" onkeyup="filterTable()" placeholder="Search Endorsement or Insured..." class="search-input">
      </div>

      <a class="my_ib_link" href="{{ route('insurance_broking.accounts.credit_notes.show') }}">
        <i class="fa fa-list"></i> View Credit Notes
      </a>
    </div>

    {{-- Data Display Section --}}
    @if ($pendingCancellations->isNotEmpty())
        <table class="invoice-table" id="creditTable">
            <thead>
                <tr>
                    <th>Slip ID</th>
                    <th>Insured / Policy</th>
                    <th>Cancel Date</th>
                    <th>Currency</th>
                    <th>Refund Amount</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingCancellations as $cancellation)
                    <tr>
                        <td><span class="slpn-badge">{{ $cancellation->slip_id }}</span></td>
                        <td>
                            <strong>{{ $cancellation->insured_name }}</strong><br>
                            <small style="color: #888;">{{ $cancellation->insurance_policy }}</small>
                        </td>
                        <td>{{ $cancellation->cancellation_date }}</td>
                        <td>{{ $cancellation->policy_currency }}</td>
                        <td><b style="color: #dc3545;">{{ number_format($cancellation->premium_refund, 2) }}</b></td>
                        
                        <td style="text-align: center;">
                            <form method="POST" action="{{ route('insurance_broking.accounts.credit_notes.store') }}" style="margin:0;">
                                @csrf
                                <input type="hidden" name="slip_id" value="{{ $cancellation->slip_id }}">
                                <button type="submit" class="btn-generate">
                                    <i class="fa fa-file-invoice"></i> Generate Credit Note
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #888; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
            <i class="fa fa-folder-open" style="font-size: 30px; margin-bottom: 10px;"></i>
            <p>No pending credit notes found for the selected criteria.</p>
        </div>
    @endif
  </div>
</div>
@endsection

{{-- 5. Push custom page script into the layout's footer JavaScript execution stack --}}
@push('scripts')
<script>
function filterTable() {
  var input, filter, table, tr, td, i;
  input = document.getElementById("creditSearch");
  filter = input.value.toUpperCase();
  table = document.getElementById("creditTable");
  tr = table.getElementsByTagName("tr");

  for (i = 1; i < tr.length; i++) {
    var tdEndorsement = tr[i].getElementsByTagName("td")[0];
    var tdInsured = tr[i].getElementsByTagName("td")[1];
    
    if (tdEndorsement || tdInsured) {
      var textEnd = tdEndorsement.textContent || tdEndorsement.innerText;
      var textIns = tdInsured.textContent || tdInsured.innerText;
      
      if (textEnd.toUpperCase().indexOf(filter) > -1 || textIns.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}
</script>
@endpush