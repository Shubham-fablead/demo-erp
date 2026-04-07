@extends('layout.app')

@section('title', 'GST Reports')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
<div class="content">
  <div class="page-header">
    <div class="page-title">
      <h4>GST Purchase Reports</h4>
      <h6>Listing of all GST-related reports</h6>
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
      </div> 

      <div class="table-responsive">
        <table class="table datanew">
          <thead>
            <tr>
              <th style="width: 70px;">No</th>
              <th>GST Reports Name</th>
              <th class="text-end">Download PDF/Excel</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
            <td>GSTR-3B Summary</td>
            <td class="text-end">
            <a href="{{ route('gst.gstr3b.export') }}" class="btn btn-primary py-1">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
              </a>
              <!-- <a href="{{ route('gst.gstr3b.exportExcel') }}" class="btn btn-primary py-1">
                <i class="fa-solid fa-file-excel"></i> Excel Download
              </a> -->
              <a href="{{ route('exports.gstr3b.excel') }}" class="btn btn-success">
              <i class="fa fa-file-excel"></i> Export Excel
            </a>

            </td>
            </tr>
            <tr>
              <td>2</td>
              <td>GSTR-1 (Purchase) Summary</td>
              <td class="text-end">
              <a id="dl-gstr1" href="{{ route('gst.gstr1.export') }}" class="btn btn-primary py-1">
                  <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a> 
                 <!-- <a id="dl-gstr1" href="{{ route('exports.gstr1') }}" class="btn btn-primary py-1">
                  <i class="fa-solid fa-file-excel"></i> Excel Download
                </a> -->
                <!-- today -->
                                  
                <a href="{{ route('exports.gstr1.excel') }}" class="btn btn-success">
                  <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>

                 <!-- today -->
              </td>
            </tr>
            <tr>
              <td>3</td>
              <td>GSTR-2 (Purchase) Summary</td>
              <td class="text-end">
              <a id="dl-gstr2" href="{{ route('gst.gstr2.pdf') }}" class="btn btn-primary py-1">
                  <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a> 
                 <!-- <a id="dl-gstr2" href="{{ route('gst.gstr2.exportExcel') }}" class="btn btn-primary py-1">
                  <i class="fa-solid fa-file-excel"></i> Excel Download
                </a> -->
                <a href="{{ route('exports.gstr2.excel') }}" class="btn btn-success">
                  <i class="fa-solid fa-file-excel"></i> Export Excel
              </a>
 
              </td>
            </tr>
            <tr>
              <td>4</td>
              <td>GSTR-9C</td>
              <td class="text-end">
              <a id="dl-gstr9c" href="{{ route('gst.gstr9c.export') }}" class="btn btn-primary py-1">
                  <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a> 
                 <a id="dl-gstr9c" href="{{ route('purchase.gstr9c.export') }}" class="btn btn-success">
                  <i class="fa-solid fa-file-excel"></i> Export Excel
                </a> 
              </td>
            </tr>
            <tr>
              <td>5</td>
              <td>GSTR-9</td>
              <td class="text-end">
              <a id="dl-gstr9" href="{{ route('gst.gstr9.export') }}" class="btn btn-primary py-1">
                  <i class="fa-solid fa-file-pdf"></i> Export PDF
                  </a>                   
                  <a id="dl-gstr9c" href="{{ route('purchase.gstr9c.export') }}" class="btn btn-success">
                  <i class="fa-solid fa-file-excel"></i> Export Excel
                </a> 
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<style>

  .pdf-btn {
  color: #ffffff !important;             /* Text white */
}

.pdf-btn i {
  color: #ffffff !important;            /* Icon white */
}

.pdf-btn:hover {
  background-color: #c82333 !important; /* Hover color (red shade for PDF feel) */
  color: #ffffff !important;            /* Text white on hover */
}

.pdf-btn:hover i {
  color: #ffffff !important;             /* Icon white on hover */
}
.table tbody tr td a {
     color: #ffffff !important;
}
</style>

@endsection
