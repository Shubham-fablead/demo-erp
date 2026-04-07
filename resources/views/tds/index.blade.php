@extends('layout.app')

@section('title', 'TDS Report')

@section('content')
<div class="content">
  <div class="page-header">
    <div class="page-title">
      <h4>TDS Module</h4>
      <small class="text-muted">Maintain TDS entries separately from GST. Auto-rate: P/H = 1%, C/F = 2% (or set manual).</small>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-end gap-2 flex-wrap">
          <div>
            <label class="form-label mb-1">From</label>
            <input type="date" id="filter_from" class="form-control form-control-sm" />
          </div>
          <div>
            <label class="form-label mb-1">To</label>
            <input type="date" id="filter_to" class="form-control form-control-sm" />
          </div>
          <div class="mb-0">
            <button id="btn-filter" class="btn btn-primary btn-sm mt-4">Filter</button>
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm mt-4">Reset</button>
            <button id="btn-export" class="btn btn-success btn-sm mt-4">Export CSV</button>
          </div>
        </div>
        <div>
          <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#tdsFormWrap" aria-expanded="false">Add TDS Entry</button>
        </div>
      </div>
    </div>
  </div>

  <div id="tdsFormWrap" class="collapse">
    <div class="card mb-3">
      <div class="card-body">
        <form id="tdsForm" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Vendor Name<span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="vendor_name" required />
          </div>
          <div class="col-md-3">
            <label class="form-label">Vendor PAN<span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="vendor_pan" maxlength="10" required />
          </div>
          <div class="col-md-2">
            <label class="form-label">Section</label>
            <input type="text" class="form-control" id="section" placeholder="e.g., 194C" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Base Amount<span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="base_amount" step="0.01" min="0" required />
          </div>
          <div class="col-md-2">
            <label class="form-label">Payment Date<span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="payment_date" required />
          </div>

          <div class="col-md-2">
            <label class="form-label">Reference No</label>
            <input type="text" class="form-control" id="reference_no" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Narration</label>
            <input type="text" class="form-control" id="narration" />
          </div>
          <div class="col-md-6">
            <label class="form-label d-block">Rate Mode</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rate_mode" id="rate_auto" value="auto" checked>
              <label class="form-check-label" for="rate_auto">Auto (by PAN type)</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="rate_mode" id="rate_manual" value="manual">
              <label class="form-check-label" for="rate_manual">Manual</label>
            </div>
            <div id="manualRateWrap" class="mt-2" style="display:none;">
              <input type="number" class="form-control" id="tds_rate" step="0.01" min="0" placeholder="Enter TDS % e.g., 1 or 2" />
            </div>
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary">Save TDS</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">TDS Entries</h6>
        <div id="totals" class="text-end small text-muted"></div>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Vendor</th>
              <th>PAN</th>
              <th>Section</th>
              <th class="text-end">Base Amount</th>
              <th class="text-end">Rate (%)</th>
              <th class="text-end">TDS Amount</th>
              <th>Payment Date</th>
              <th>Ref No</th>
              <th>Narration</th>
            </tr>
          </thead>
          <tbody id="tdsRows">
            <tr><td colspan="9" class="text-center">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
(function(){
  const token = localStorage.getItem('authToken') || '';
  const headers = token ? { 'Authorization': 'Bearer ' + token } : {};

  function fmtMoney(v){ v = parseFloat(v||0); return v.toFixed(2); }
  function cur(symbol, position, v){ const m = fmtMoney(v); return position==='left' ? symbol + m : m + symbol; }

  function setTotals(symbol, position, totalBase, totalTds){
    const html = `Total Base: <strong>${cur(symbol, position, totalBase)}</strong> | Total TDS: <strong>${cur(symbol, position, totalTds)}</strong>`;
    document.getElementById('totals').innerHTML = html;
  }

  function loadList(){
    const from = document.getElementById('filter_from').value;
    const to   = document.getElementById('filter_to').value;

    $('#tdsRows').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');
    $.ajax({
      url: '{{ url('/api/tds/list') }}',
      type: 'GET',
      data: { from_date: from, to_date: to },
      headers: headers,
      success: function(res){
        const cs = res.currency_symbol || '₹';
        const cp = res.currency_position || 'left';
        setTotals(cs, cp, res.total_base||0, res.total_tds||0);

        const rows = res.data || [];
        if(rows.length === 0){
          $('#tdsRows').html('<tr><td colspan="9" class="text-center">No entries found.</td></tr>');
          return;
        }
        let html='';
        rows.forEach(function(e){
          html += `<tr>
            <td>${e.vendor_name||''}</td>
            <td>${e.vendor_pan||''}</td>
            <td>${e.section||''}</td>
            <td class="text-end">${cur(cs, cp, e.base_amount||0)}</td>
            <td class="text-end">${fmtMoney(e.tds_rate||0)}</td>
            <td class="text-end">${cur(cs, cp, e.tds_amount||0)}</td>
            <td>${e.payment_date||''}</td>
            <td>${e.reference_no||''}</td>
            <td>${e.narration||''}</td>
          </tr>`;
        });
        $('#tdsRows').html(html);
      },
      error: function(xhr){
        $('#tdsRows').html('<tr><td colspan="9" class="text-center text-danger">Failed to load entries</td></tr>');
      }
    });
  }

  function exportCsv(){
    const from = document.getElementById('filter_from').value;
    const to   = document.getElementById('filter_to').value;
    const q = $.param({ from_date: from, to_date: to });

    $.ajax({
      url: '{{ url('/api/tds/export') }}' + (q ? ('?'+q) : ''),
      type: 'GET',
      headers: headers,
      success: function(res){
        if(res && res.download_url){
          const a = document.createElement('a');
          a.href = res.download_url; a.download = res.filename || 'tds_report.csv';
          document.body.appendChild(a); a.click(); document.body.removeChild(a);
        } else {
          alert('No file to download.');
        }
      },
      error: function(){ alert('Export failed'); }
    });
  }

  // Form interactions
  $(document).on('change', 'input[name="rate_mode"]', function(){
    const mode = $('input[name="rate_mode"]:checked').val();
    $('#manualRateWrap').toggle(mode === 'manual');
  });

  $('#tdsForm').on('submit', function(e){
    e.preventDefault();
    const payload = {
      vendor_name: $('#vendor_name').val(),
      vendor_pan:  $('#vendor_pan').val(),
      section:     $('#section').val(),
      base_amount: $('#base_amount').val(),
      payment_date:$('#payment_date').val(),
      reference_no:$('#reference_no').val(),
      narration:   $('#narration').val(),
      rate_mode:   $('input[name="rate_mode"]:checked').val(),
    };
    if(payload.rate_mode === 'manual'){
      payload.tds_rate = $('#tds_rate').val();
    }

    $.ajax({
      url: '{{ url('/api/tds/store') }}',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      headers: Object.assign({ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, headers),
      success: function(){
        // Clear form and refresh
        $('#tdsForm')[0].reset();
        $('#manualRateWrap').hide();
        loadList();
      },
      error: function(xhr){
        let msg = 'Failed to save TDS';
        if(xhr && xhr.responseJSON && xhr.responseJSON.message){ msg = xhr.responseJSON.message; }
        alert(msg);
      }
    });
  });

  $('#btn-filter').on('click', loadList);
  $('#btn-reset').on('click', function(){ $('#filter_from').val(''); $('#filter_to').val(''); loadList(); });
  $('#btn-export').on('click', exportCsv);

  // initial
  loadList();
})();
</script>
@endsection
