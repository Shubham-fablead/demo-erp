@php
    $user = auth()->user();
@endphp

<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                {{-- Dashboard --}}
                @if (app('hasPermission')(0, 'view'))
                    <li>
                        <a href="{{ route('auth.dashboard') }}"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/dashboard.svg' }}"
                                alt="img"><span>
                                Dashboard</span> </a>
                    </li>
                @endif
                {{-- Products --}}
                @if (app('hasPermission')(1, 'view') || app('hasPermission')(1, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/product.svg' }}"
                                alt="img"><span>
                                Products</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(1, 'view'))
                                <li><a href="{{ route('product.list') }}">All Products</a></li>
                            @endif
                            @if (app('hasPermission')(1, 'add'))
                                <li><a href="{{ route('product.add') }}">New Product</a></li>
                            @endif
                            @if (app('hasPermission')(1, 'view'))
                                <li><a href="{{ route('row_material.list') }}">All Row Materials</a></li>
                            @endif
                            {{-- @if (app('hasPermission')(1, 'add'))
                                <li><a href="{{ route('row_material.add') }}">New Row Material</a></li>
                            @endif --}}
                            @if (app('hasPermission')(6, 'view'))
                                <li><a href="{{ route('category.list') }}">All Categories</a></li>
                                <li><a href="{{ route('category.add') }}">New Category</a></li>
                                <li><a href="{{ route('brand.list') }}">All Brands</a></li>
                                <li><a href="{{ route('brand.add') }}">New Brand</a></li>
                                <li><a href="{{ route('unit.list') }}">All Units</a></li>
                                <li><a href="{{ route('labour_item.all_labour_item') }}">All Labour Items</a></li>
                                <li><a href="{{ route('product.import') }}">Import Products</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Sales and Orders --}}
                @if (app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/sales1.svg' }}"
                                alt="img"><span> Sales & Orders</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(2, 'view'))
                                <li><a href="{{ route('sales.list') }}">All Sales & Orders</a></li>
                            @endif
                            @if (app('hasPermission')(2, 'add'))
                                <li><a href="{{ route('sales.add') }}">New Order</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Purchases --}}
                @if (app('hasPermission')(3, 'view') || app('hasPermission')(3, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/purchase1.svg' }}"
                                alt="img"><span>Purchases</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(3, 'view'))
                                <li><a href="{{ route('purchase.lists') }}">All Purchases</a></li>
                            @endif
                            @if (app('hasPermission')(3, 'add'))
                                <li><a href="{{ route('purchase.add') }}">New Purchase</a></li>
                                <li><a href="{{ route('purchase.row_material.lists') }}">Row Material Purchases</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (app('hasPermission')(17, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-warehouse"></i>
                            <span>Manage Inventory</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="{{ route('inventory.list') }}">Stock Inventory</a></li>
                            <li><a href="{{ route('row_material.inventory') }}">Material Inventory</a></li>
                            <li><a href="{{ route('inventory.bom.list') }}">Bill of Materials</a></li>
                            <li><a href="{{ route('inventory.production.list') }}">Production</a></li>
                        </ul>
                    </li>
                @endif
                @if (app('hasPermission')(16, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            {{-- <img src="{{ env('ImagePath') . '/admin/assets/img/icons/purchase1.svg' }}" alt="img"> --}}
                            <i class="fa fa-calculator"></i>
                            <span>Manage Accounting</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('account_ledger.add') }}">Account Ledger</a></li>
                            @endif

                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('income-statement.index') }}">Income Statement</a></li>
                            @endif
                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('accounting.balance-sheet') }}">Balance Sheet</a></li>
                            @endif
                            @if (app('hasPermission')(16, 'view'))
                                <li><a href="{{ route('banks.index') }}">Banks</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Invoices --}}
                @if (app('hasPermission')(4, 'view') || app('hasPermission')(4, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-clipboard"></i>
                            <span>Invoices</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(4, 'view'))
                                <li><a href="{{ route('custom_invoice.lists') }}">All Invoices</a></li>
                            @endif
                            @if (app('hasPermission')(4, 'add'))
                                <li><a href="{{ route('custom_invoice.add') }}">New Invoice</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Expenses --}}
                @if (app('hasPermission')(5, 'view') || app('hasPermission')(5, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><img
                                src="{{ env('ImagePath') . '/admin/assets/img/icons/expense1.svg' }}"
                                alt="img"><span>
                                Expenses</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(5, 'view'))
                                <li><a href="{{ route('expense.list') }}">All Expenses</a></li>
                            @endif
                            @if (app('hasPermission')(5, 'add'))
                                <li><a href="{{ route('expense.add') }}">New Expense</a></li>
                            @endif
                            @if (app('hasPermission')(5, 'view'))
                                <li><a href="{{ route('expensetype.list') }}">All Expense Type</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- transaction --}}
                @if (app('hasPermission')(27, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fas fa-book"></i>
                            <span>Cash & Bank</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(27, 'view'))
                                <li><a href="{{ route('transaction.bankbook') }}">Bank Book</a></li>
                            @endif
                            @if (app('hasPermission')(27, 'view'))
                                <li><a href="{{ route('transaction.cashbook') }}">Cash Book</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Credit Notes --}}
                @if (app('hasPermission')(27, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-credit-card me-2"></i>



                            <span>Credit/Debit Notes</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(27, 'view'))
                                <li>
                                    <a href="{{ route('credit-notes-items.index') }}">
                                        Credit Notes
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('debit-notes-items.index') }}">
                                        Debit Notes
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('credit-notes.index') }}">
                                        Credit/Debit Notes Type
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif




                {{-- Returns --}}
                @if (
                    (app('hasPermission')(2, 'add') && app('hasPermission')(2, 'edit')) ||
                        (app('hasPermission')(3, 'add') && app('hasPermission')(3, 'edit')))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/return1.svg' }}" alt="img">
                            <span>Returns</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            {{-- Sales Return: show only if user has add & edit permission on module 2 --}}
                            @if (app('hasPermission')(2, 'add') && app('hasPermission')(2, 'edit'))
                                <li><a href="{{ route('salesreturn.list') }}">Sales Return</a></li>
                            @endif

                            {{-- Purchase Return: show only if user has add & edit permission on module 3 --}}
                            @if (app('hasPermission')(3, 'add') && app('hasPermission')(3, 'edit'))
                                <li><a href="{{ route('purchasereturn.list') }}">Purchase Return</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Quotes --}}

                @php

                    $canViewAttendance = app('hasPermission')(26, 'view');

                @endphp

                @if (auth()->user()->role === 'staff')

                    @if ($canViewAttendance)
                        @if ($canViewAttendance)
                            {{-- ✅ Only Attendance permission → show directly --}}
                            <li>
                                <a href="{{ route('attendance.list') }}">
                                    <i class="fa fa-clock"></i><span> Attendance</span>
                                </a>
                            </li>
                        @endif
                    @endif
                @endif

                {{-- Staff --}}
                @if (app('hasPermission')(8, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}" alt="img">
                            <span> Staff</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="{{ route('staff.list') }}">All Staff</a></li>
                            <li><a href="{{ route('staff.add') }}">New Staff</a></li>
                            <li><a href="{{ route('attendance.list') }}">Attendance</a></li>
                            <li><a href="{{ route('salary.list') }}">Salary</a></li>
                        </ul>
                    </li>
                @endif
                <!-- <li class="submenu">
                        <a href="javascript:void(0);"><i class="fa fa-calendar-check"></i><span> Appointment</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(17, 'view'))
<li><a href="{{ route('appointments.index') }}">All Appointment</a></li>
@endif
                            @if (app('hasPermission')(17, 'add'))
<li><a href="{{ route('appointments.create') }}">New Appointment</a></li>
@endif
                        </ul>
                    </li> -->

                @if (auth()->user()->role === 'admin')

                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fa fa-money-bill-wave"></i>
                            <span>Advance Pay</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(23, 'view'))
                                <li><a href="{{ route('advance_pay.index') }}">All Advance Pay</a></li>
                            @endif
                            @if (app('hasPermission')(23, 'add'))
                                <li><a href="{{ route('advance_pay.create') }}">New Advance Pay</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Customers --}}
                @if (app('hasPermission')(9, 'view') || app('hasPermission')(9, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            {{-- <img src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}"
                                alt="img"> --}}
                            <i class="fa fa-users"></i>
                            <span> Customers</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(9, 'view'))
                                <li><a href="{{ route('customer.list') }}">All Customers</a></li>
                            @endif
                            @if (app('hasPermission')(9, 'add'))
                                <li><a href="{{ route('customer.add') }}">New Customer</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Vendors --}}
                @if (app('hasPermission')(10, 'view') || app('hasPermission')(10, 'add'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            {{-- <img src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}"
                                alt="img"> --}}
                            <i class="fa fa-handshake"></i>
                            <span> Vendors</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if (app('hasPermission')(10, 'view'))
                                <li><a href="{{ route('vendor.list') }}">All Vendors</a></li>
                            @endif
                            @if (app('hasPermission')(10, 'add'))
                                <li><a href="{{ route('vendor.add') }}">New Vendor </a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- GST Reports --}}
                @if (app('hasPermission')(20, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fas fa-chart-line"></i>

                            <span> GST Reports</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(20, 'view'))
                                <li><a href="{{ route('gst.sales_list') }}">GST Report</a></li>
                            @endif
                            <!-- @if (app('hasPermission')(20, 'view'))
<li><a href="{{ route('gst.reports.purchase') }}">GST Purchase Report</a></li>
@endif -->

                        </ul>
                    </li>
                @endif
                {{-- Reports --}}
                @if (app('hasPermission')(2, 'view') || app('hasPermission')(3, 'view') || app('hasPermission')(5, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/time.svg' }}" alt="img">
                            <span> Reports</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(2, 'view'))
                                <li><a href="{{ route('sales.report') }}">Sales Report</a></li>
                            @endif

                            @if (app('hasPermission')(3, 'view'))
                                <li><a href="{{ route('purchase.report') }}">Purchase Report</a></li>
                            @endif

                            @if (app('hasPermission')(5, 'view'))
                                <li><a href="{{ route('expense.report') }}">Expenses Report</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Settings --}}
                @if (app('hasPermission')(14, 'view') || app('hasPermission')(15, 'view'))
                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/settings.svg' }}"
                                alt="img">
                            <span> Settings</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.generalsettings') }}">Shop Settings</a></li>
                            @endif
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.smtpsettings') }}">Smtp Settings</a></li>
                            @endif
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.facebookappconfiguration') }}">WhatsApp
                                        Configuration</a></li>
                            @endif
                            @if (app('hasPermission')(15, 'view'))
                                <li><a href="{{ route('auth.taxrates') }}">Tax Rates</a></li>
                            @endif
                            @if (app('hasPermission')(14, 'view'))
                                <li><a href="{{ route('setting.connecteddevices') }}">Connected Devices</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
