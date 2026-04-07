@extends('layout.app')

@section('title', 'GSTR-9 Report')

@section('content')
<div class="content">
  <div class="page-header">
    <div class="page-title">
      <h4>GSTR-9 Summary</h4>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-top d-flex justify-content-between align-items-center mb-3">
        <div class="search-set">
            <select id="date-filter" class="form-select form-select-sm">
                <option value="">All Time</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="last_6_months">Last 6 Months</option>
                <option value="this_year">This Year</option>
                <option value="previous_year">Previous Year</option>
            </select>
        </div>
        <div class="wordset">
          <ul>
            <li>
              <button id="btn-refresh" type="button" class="btn btn-primary btn-sm">Refresh</button>
            </li>
            <li>
  <a id="btn-export" href="{{ route('gst.gstr9.export') }}" class="btn btn-success btn-sm" target="_blank">
    Export PDF
  </a>
</li>

          </ul>
        </div>
      </div>

      <div id="meta-range" class="mb-2 text-muted"></div>

      <div class="row">
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header"><strong>Outward Supplies</strong></div>
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">Invoices <span id="out_inv">0</span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">Taxable Value <span><span class="currency"></span><span id="out_taxable">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">CGST <span><span class="currency"></span><span id="out_cgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">SGST <span><span class="currency"></span><span id="out_sgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">IGST <span><span class="currency"></span><span id="out_igst">0.00</span></span></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header"><strong>Inward Supplies (ITC)</strong></div>
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">Invoices <span id="in_inv">0</span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">Taxable Value <span><span class="currency"></span><span id="in_taxable">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">CGST <span><span class="currency"></span><span id="in_cgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">SGST <span><span class="currency"></span><span id="in_sgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">IGST <span><span class="currency"></span><span id="in_igst">0.00</span></span></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Net Tax Payable</strong></div>
        <div class="card-body">
          <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">Payable CGST <span><span class="currency"></span><span id="net_cgst">0.00</span></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Payable SGST <span><span class="currency"></span><span id="net_sgst">0.00</span></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Payable IGST <span><span class="currency"></span><span id="net_igst">0.00</span></span></li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
(function(){
  function money(v){ return (parseFloat(v||0)).toFixed(2); }
  function setCurrency(symbol, position){ document.querySelectorAll('.currency').forEach(el=> el.textContent = position==='left'?symbol:''); }

  function load(){
    const btn = document.getElementById('btn-refresh');
    if(btn){ btn.disabled = true; btn.innerText='Refreshing...'; }
    const filter = document.getElementById('date-filter').value;

    $.ajax({
      url: '{{ url("/gst/gstr-9/data") }}',
      type: 'GET',
      data: { filter },
      success: function(res){
        setCurrency(res.currency_symbol, res.currency_position);
        const rn=[]; if(res.from_date) rn.push('From: '+res.from_date); if(res.to_date) rn.push('To: '+res.to_date);
        document.getElementById('meta-range').textContent = rn.join(' | ');

        const o = res.outward||{}; const i=res.inward||{}; const n=res.net||{};
        document.getElementById('out_inv').textContent = o.invoice_count || 0;
        document.getElementById('out_taxable').textContent = money(o.taxable_value);
        document.getElementById('out_cgst').textContent = money(o.cgst);
        document.getElementById('out_sgst').textContent = money(o.sgst);
        document.getElementById('out_igst').textContent = money(o.igst);

        document.getElementById('in_inv').textContent = i.invoice_count || 0;
        document.getElementById('in_taxable').textContent = money(i.taxable_value);
        document.getElementById('in_cgst').textContent = money(i.cgst);
        document.getElementById('in_sgst').textContent = money(i.sgst);
        document.getElementById('in_igst').textContent = money(i.igst);

        document.getElementById('net_cgst').textContent = money(n.payable_cgst);
        document.getElementById('net_sgst').textContent = money(n.payable_sgst);
        document.getElementById('net_igst').textContent = money(n.payable_igst);

        if(btn){ btn.disabled=false; btn.innerText='Refresh'; }
      },
      error: function(){ if(btn){ btn.disabled=false; btn.innerText='Refresh'; } alert('Failed to load GSTR-9'); }
    });
  }

  document.getElementById('btn-refresh').addEventListener('click', load);
  document.getElementById('date-filter').addEventListener('change', load);
  load();
})();
</script>
@endsection
