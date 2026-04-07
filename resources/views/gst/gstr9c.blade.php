@extends('layout.app')

@section('title', 'GSTR-9C Report')

@section('content')
<div class="content">
  <div class="page-header">
    <div class="page-title">
      <h4>GSTR-9C Reconciliation (Summary)</h4>
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
  <a href="{{ route('gst.gstr9c.export') }}" class="btn btn-success btn-sm" target="_blank">
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
            <div class="card-header"><strong>Outward Supplies (Turnover)</strong></div>
            <div class="card-body">
              <ul class="list-group">
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
        <div class="card-header"><strong>Reconciliation</strong></div>
        <div class="card-body">
          <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">Payable CGST <span><span class="currency"></span><span id="rec_cgst">0.00</span></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Payable SGST <span><span class="currency"></span><span id="rec_sgst">0.00</span></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Payable IGST <span><span class="currency"></span><span id="rec_igst">0.00</span></span></li>
            <li class="list-group-item"><small class="text-muted" id="rec_note"></small></li>
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
    if(btn){ btn.disabled=true; btn.innerText='Refreshing...'; }
    const filter = document.getElementById('date-filter').value;

    $.ajax({
      url: '{{ url("/gst/gstr-9c/data") }}',
      type: 'GET',
      data: { filter },
      success: function(res){
        setCurrency(res.currency_symbol, res.currency_position);
        const rn=[]; if(res.from_date) rn.push('From: '+res.from_date); if(res.to_date) rn.push('To: '+res.to_date);
        document.getElementById('meta-range').textContent = rn.join(' | ');

        const t=res.turnover||{}; const i=res.itc||{}; const r=res.reconciliation||{};
        document.getElementById('out_taxable').textContent = money(t.taxable_value);
        document.getElementById('out_cgst').textContent = money(t.cgst);
        document.getElementById('out_sgst').textContent = money(t.sgst);
        document.getElementById('out_igst').textContent = money(t.igst);

        document.getElementById('in_taxable').textContent = money(i.taxable_value);
        document.getElementById('in_cgst').textContent = money(i.cgst);
        document.getElementById('in_sgst').textContent = money(i.sgst);
        document.getElementById('in_igst').textContent = money(i.igst);

        document.getElementById('rec_cgst').textContent = money(r.payable_cgst);
        document.getElementById('rec_sgst').textContent = money(r.payable_sgst);
        document.getElementById('rec_igst').textContent = money(r.payable_igst);
        document.getElementById('rec_note').textContent = r.note || '';

        if(btn){ btn.disabled=false; btn.innerText='Refresh'; }
      },
      error: function(){ if(btn){ btn.disabled=false; btn.innerText='Refresh'; } alert('Failed to load GSTR-9C'); }
    });
  }

  document.getElementById('btn-refresh').addEventListener('click', load);
  document.getElementById('date-filter').addEventListener('change', load);
  load();
})();
</script>
@endsection
