    <link rel="stylesheet" href="{{ env('ImagePath') . '/admin/assets/css/style.css' }}">
 
 <div class="card">
            <div class="card-body">
                <input type="hidden" name="expense_id" id="expense_id" value="">
        
                <tr class="top">
                    <td colspan="2">
                        <div class="row pdf-header purchase_report_head">
                            <div class="col-6">
                                <img src="{{ $settings->logo ? asset('/public/storage/' . $settings->logo) : asset('/public/admin/assets/img/logso.png') }}"
                                    style="max-width: 150px;">
                            </div>
                            <div class="col-6 mt-4" style="text-align: end;">
                                <h2>Expense Report</h2>
                            </div>
                        </div>
                        <hr>
                    </td>
                </tr>
                <div class="download_pdf">
                    <div class="invoice-box table-height"
                        style="max-width: 1600px; width:100%; margin:15px auto; padding: 0; font-size: 14px; line-height: 24px; color: #555;">
                        <!-- First Table: Company Info & Report Info -->
                        <table style="width: 100%; line-height: inherit; text-align: left;" class="purchase_report_table1">

                            <tr>
                                <td style="padding: 10px; vertical-align: top;">
                                    <strong style="font-size:14px; color:#7367F0; font-weight:600;">Company
                                        Info</strong><br>
                                    {{ $settings->name ?? 'Company Name' }}<br>
                                    {{ $settings->email ?? 'N/A' }}<br>
                                    {{ $settings->phone ?? 'N/A' }}<br>
                                    {{ $settings->address ?? 'N/A' }}<br>
                                    GST: {{ $settings->gst_num ?? 'N/A' }}
                                </td>

                                <td style="padding: 10px; text-align: right; vertical-align: top;">
                                    <strong style="font-size:14px; color:#7367F0; font-weight:600;">Report Info</strong><br>
                                    Total Expense: {{ $expenses->count() }}<br> 
                                    Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}
                                </td>
                            </tr>
                        </table>

                        <br>

                        <!-- Second Table: Expense Details -->
                        <table style="width: 100%; line-height: inherit; text-align: left; border-collapse: collapse;">
                            <tr class="heading" style="background: #F3F2F7;">
                                <td style="padding: 10px;"><strong>Expense Name</strong></td>
                                <td style="padding: 10px;"><strong>Amount</strong></td>
                                <td style="padding: 10px;"><strong>Date</strong></td>
                                <td style="padding: 10px;"><strong>Expense For</strong></td>
                            </tr>

                            @foreach($expenses as $expense)
                                <tr class="details" style="border-bottom: 1px solid #E9ECEF;">
                                    <td style="padding: 10px;">{{ $expense->expense_name }}</td>
                                    <td style="padding: 10px;">
                                        {{ $currencyPosition === 'left' ? $currencySymbol . number_format($expense->amount, 2) : number_format($expense->amount, 2) . $currencySymbol }}
                                    </td>

                                    <td style="padding: 10px;">
                                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                    <td style="padding: 10px; white-space: normal; word-break: break-word; max-width: 400px;">
                                        {{ $expense->description ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </table>



                        <div class="row mt-3">
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                    <ul>
                                        <li>
                                            <h4>Total Amount</h4>
                                            <h5>
                                                {{ $currencyPosition === 'left' ? $currencySymbol . number_format($expenses->sum('amount'), 2) : number_format($expenses->sum('amount'), 2) . $currencySymbol }}
                                            </h5>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>