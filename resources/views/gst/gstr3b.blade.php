@extends('layout.app')

@section('title', 'GSTR-3B Report')

@section('content')
<div class="content">
  <div class="page-header">
    <div class="page-title">
      <h4>GSTR-3B Summary</h4>
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
              <!-- <button id="btn-refresh" type="button" class="btn btn-primary btn-sm">Refresh</button> -->
               <!-- Export button -->
              <a href="{{ route('gst.gstr3b.export') }}" class="btn btn-primary py-1" style="font-size:14px;">
                  Export Report (PDF)
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div id="meta-range" class="mb-2 text-muted"></div>

      <div class="row">
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header pb-0 fs-6"><strong>Details of outward supplies and inward supplies liable to reverse charge</strong></div>
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  a) Outward taxable supplies
                  <span><span class="currency"></span><span id="out_taxable">0.00</span> | CGST: <span class="currency"></span><span id="out_cgst">0.00</span> | SGST: <span class="currency"></span><span id="out_sgst">0.00</span> | IGST: <span class="currency"></span><span id="out_igst">0.00</span></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  b) Outward taxable supplies (zero rated)
                  <span><span class="currency"></span><span id="out_zero">0.00</span></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  c) Other outward supplies (nil rated, exempted)
                  <span><span class="currency"></span><span id="out_exempt">0.00</span></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  d) Inward supplies liable to reverse charge (net of advances & tax paid on earlier RCM)
                  <span><span class="currency"></span><span id="in_rcm">0.00</span></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  e) Non-GST outward supplies
                  <span><span class="currency"></span><span id="out_non_gst">0.00</span></span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header pb-0 fs-6"><strong>Eligible ITC</strong></div>
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">CGST <span><span class="currency"></span><span id="itc_cgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">SGST <span><span class="currency"></span><span id="itc_sgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">IGST <span><span class="currency"></span><span id="itc_igst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">Cess <span><span class="currency"></span><span id="itc_cess">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total ITC</strong> <span><span class="currency"></span><strong id="itc_total">0.00</strong></span></li>
              </ul>
            </div>
          </div>

        </div>
        <div class="col-md-12">
          

          <div class="card mb-3">
            <div class="card-header pb-0 fs-6"><strong>Payment of tax</strong></div>
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">CGST Payable <span><span class="currency"></span><span id="pay_cgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">SGST Payable <span><span class="currency"></span><span id="pay_sgst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">IGST Payable <span><span class="currency"></span><span id="pay_igst">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center">Cess <span><span class="currency"></span><span id="pay_cess">0.00</span></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total Payable</strong> <span><span class="currency"></span><strong id="pay_total">0.00</strong></span></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
(function(){
  function money(val){
    return (parseFloat(val || 0)).toFixed(2);
  }
  function setCurrency(symbol, position){
    const elms = document.querySelectorAll('.currency');
    elms.forEach(el => { el.textContent = position === 'left' ? symbol : ''; });
  }
  function suffixCurrency(value, symbol, position){
    return position === 'right' ? money(value) + symbol : money(value);
  }

  function loadReport(){
    const btn = document.getElementById('btn-refresh');
    if (btn) { btn.disabled = true; btn.innerText = 'Refreshing...'; }
    const filter = document.getElementById('date-filter').value;
    // Prefer session-authenticated web route if available (no token needed)
    const headers = {};
    $.ajax({
      url: '{{ url("/gst/gstr-3b/data") }}',
      type: 'GET',
      data: { filter: filter },
      headers: headers,
      success: function(res){
        setCurrency(res.currency_symbol, res.currency_position);

        // meta
        const rn = [];
        if (res.from_date) rn.push('From: ' + res.from_date);
        if (res.to_date) rn.push('To: ' + res.to_date);
        document.getElementById('meta-range').textContent = rn.join(' | ');

        // 3.1
        const a = res.section_3_1.a_outward_taxable_supplies || {};
        document.getElementById('out_taxable').textContent = money(a.taxable_value);
        document.getElementById('out_cgst').textContent = money(a.cgst);
        document.getElementById('out_sgst').textContent = money(a.sgst);
        document.getElementById('out_igst').textContent = money(a.igst);
        document.getElementById('out_zero').textContent = money(res.section_3_1.b_zero_rated || 0);
        document.getElementById('out_exempt').textContent = money(res.section_3_1.c_other_exempt_nil || 0);
        document.getElementById('in_rcm').textContent = money(res.section_3_1.d_inward_rcm || 0);
        document.getElementById('out_non_gst').textContent = money(res.section_3_1.e_non_gst_outward || 0);

        // ITC
        const itc = res.eligible_itc || {};
        document.getElementById('itc_cgst').textContent = money(itc.cgst);
        document.getElementById('itc_sgst').textContent = money(itc.sgst);
        document.getElementById('itc_igst').textContent = money(itc.igst);
        document.getElementById('itc_cess').textContent = money(itc.cess);
        document.getElementById('itc_total').textContent = money(itc.total);

        // Payment
        const pay = res.net_tax_payable || {};
        document.getElementById('pay_cgst').textContent = money(pay.cgst);
        document.getElementById('pay_sgst').textContent = money(pay.sgst);
        document.getElementById('pay_igst').textContent = money(pay.igst);
        document.getElementById('pay_cess').textContent = money(pay.cess);
        document.getElementById('pay_total').textContent = money(pay.total);
        if (btn) { btn.disabled = false; btn.innerText = 'Refresh'; }
      },
      error: function(xhr){
        if (btn) { btn.disabled = false; btn.innerText = 'Refresh'; }
        var errMsg = 'Unknown error';
        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
          errMsg = xhr.responseJSON.message;
        } else if (xhr && xhr.statusText) {
          errMsg = xhr.statusText;
        }
        alert('Failed to fetch GSTR-3B: ' + errMsg);
      }
    });
  }

  document.getElementById('btn-refresh').addEventListener('click', loadReport);
  document.getElementById('date-filter').addEventListener('change', loadReport);
  loadReport();
})();
</script>
@endsection
