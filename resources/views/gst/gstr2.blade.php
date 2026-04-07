@extends('layout.app')

@section('title', 'GSTR-2 (Purchase) Report')

@section('content')
<div class="content">
  <div class="page-header">
    <div class="page-title">
      <h4>GSTR-2 (Purchase) Summary</h4>
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
              <a href="{{ route('gst.gstr2.pdf') }}" class="btn btn-danger btn-sm" target="_blank">Export PDF</a>
            </li>

          </ul>
        </div>
      </div>

      <div id="meta-range" class="mb-2 text-muted"></div>

      <div class="row">
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header"><strong>Registered Vendors</strong></div>
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">Invoices <span id="reg_invoices">0</span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">Taxable Value <span><span class="currency"></span><span id="reg_taxable">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">CGST <span><span class="currency"></span><span id="reg_cgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">SGST <span><span class="currency"></span><span id="reg_sgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">IGST <span><span class="currency"></span><span id="reg_igst">0.00</span></span></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header"><strong>Unregistered Vendors</strong></div>
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">Invoices <span id="unreg_invoices">0</span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">Taxable Value <span><span class="currency"></span><span id="unreg_taxable">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">CGST <span><span class="currency"></span><span id="unreg_cgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">SGST <span><span class="currency"></span><span id="unreg_sgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">IGST <span><span class="currency"></span><span id="unreg_igst">0.00</span></span></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Total Summary</strong></div>
        <div class="card-body">
          <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">Total Invoices <span id="sum_invoices">0</span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Total Taxable Value <span><span class="currency"></span><span id="sum_taxable">0.00</span></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Total CGST <span><span class="currency"></span><span id="sum_cgst">0.00</span></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Total SGST <span><span class="currency"></span><span id="sum_sgst">0.00</span></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Total IGST <span><span class="currency"></span><span id="sum_igst">0.00</span></span></li>
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
    if (btn) { btn.disabled = true; btn.innerText = 'Refreshing...'; }
    const filter = document.getElementById('date-filter').value;

    $.ajax({
      url: '{{ url("/gst/gstr-2/data") }}',
      type: 'GET',
      data: { filter },
      success: function(res){
        setCurrency(res.currency_symbol, res.currency_position);
        const rn=[]; if(res.from_date) rn.push('From: '+res.from_date); if(res.to_date) rn.push('To: '+res.to_date);
        document.getElementById('meta-range').textContent = rn.join(' | ');

        const r = res.registered||{}; const u = res.unregistered||{}; const s=res.summary||{};
        document.getElementById('reg_invoices').textContent = r.invoice_count||0;
        document.getElementById('reg_taxable').textContent = money(r.taxable_value);
        document.getElementById('reg_cgst').textContent = money(r.cgst);
        document.getElementById('reg_sgst').textContent = money(r.sgst);
        document.getElementById('reg_igst').textContent = money(r.igst);

        document.getElementById('unreg_invoices').textContent = u.invoice_count||0;
        document.getElementById('unreg_taxable').textContent = money(u.taxable_value);
        document.getElementById('unreg_cgst').textContent = money(u.cgst);
        document.getElementById('unreg_sgst').textContent = money(u.sgst);
        document.getElementById('unreg_igst').textContent = money(u.igst);

        document.getElementById('sum_invoices').textContent = s.total_invoices || 0;
        document.getElementById('sum_taxable').textContent = money(s.taxable_value);
        document.getElementById('sum_cgst').textContent = money(s.cgst);
        document.getElementById('sum_sgst').textContent = money(s.sgst);
        document.getElementById('sum_igst').textContent = money(s.igst);

        if (btn) { btn.disabled = false; btn.innerText = 'Refresh'; }
      },
      error: function(){ if(btn){btn.disabled=false; btn.innerText='Refresh';} alert('Failed to load GSTR-2'); }
    });
  }

  document.getElementById('btn-refresh').addEventListener('click', load);
  document.getElementById('date-filter').addEventListener('change', load);
  load();
})();
</script>
@endsection
