<?php

use App\Http\Controllers\api\AdvancePaymentController;
use App\Http\Controllers\api\AppointmentController;
use App\Http\Controllers\api\AttendanceController;
use App\Http\Controllers\api\SalaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Laravel\Passport\Http\Controllers\AccessTokenController;

use App\Http\Controllers\api\LoginController;
use App\Http\Controllers\api\ManufacturingController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\SupplierController;
use App\Http\Controllers\api\TaxRateController;
use App\Http\Controllers\api\CurrencyController;
use App\Http\Controllers\api\CreditNotesTypeController;
use App\Http\Controllers\api\CreditNoteItemApiController;
use App\Http\Controllers\api\DebitNoteItemApiController;
use App\Http\Controllers\api\GeneralSettingController;
use App\Http\Controllers\api\PurchaseController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\BrandController;
use App\Http\Controllers\api\SalesController;
use App\Http\Controllers\api\ExpenseController;
use App\Http\Controllers\api\ExpenseTypeController;
use App\Http\Controllers\api\SaleReturnController;
use App\Http\Controllers\api\CustomInvoiceController;
use App\Http\Controllers\api\StaffController;
use App\Http\Controllers\api\GstController;
use App\Http\Controllers\api\TdsController;
use App\Http\Controllers\api\LabourItemController;
use App\Http\Controllers\api\SubBranchController;
use App\Http\Controllers\api\AccountLedgerController;
use App\Http\Controllers\api\InventoryController;
use App\Http\Controllers\api\PurchaseReturnController;
use App\Http\Controllers\api\IncomeSheetController;
use App\Http\Controllers\api\BalanceSheetController;
use App\Http\Controllers\api\FacebookAppConfigurationController;
use App\Http\Controllers\api\BankMasterController;
use App\Http\Controllers\api\GSTR_1Controller;
use App\Http\Controllers\api\GstSalesReportController;
use App\Http\Controllers\api\TransactionApiController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\ConnectedDevicesController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\RowMaterialController;
use App\Http\Controllers\api\RowMaterialInventoryController;
use App\Http\Controllers\api\SmtpSettingController;
use App\Http\Controllers\api\UnitController as ApiUnitController;
use App\Http\Controllers\UnitController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])->middleware(['throttle', 'passport.client']);
Route::post('/sales/pdf-download', [SalesController::class, 'view_sales_report'])->name('sales.report.html');
// Route::get('sales/report/view-page', [SalesController::class, 'show_sales_report_page']);
Route::post('purchase/report/view', [PurchaseController::class, 'view_purchase_report']);
Route::post('/expense/report/view', [ExpenseController::class, 'view_expense_report']);

Route::post('login', [LoginController::class, 'login'])->name('login');
Route::middleware('auth:api')->get('/dashboard-api', [LoginController::class, 'dashboardApi']);

Route::middleware(['auth.api'])->group(function () {
    Route::get('/sales/invoice/pdf/download/{id}', [SalesController::class, 'salse_invoice_pdf_download'])->name('sales.invoice.pdf.download');
    Route::get('/purchase/invoice/pdf/download/{id}', [PurchaseController::class, 'purchase_invoice_pdf_download'])->name('purchase.invoice.pdf.download');
    Route::get('/custom-invoice/pdf/download/{id}', [CustomInvoiceController::class, 'custom_invoice_pdf_download'])->name('custom_invoice.pdf.download');


    // Route::get('/dashboard-api', [LoginController::class, 'dashboardApi']);


    Route::get('/gstr1/export-excel', [GSTR_1Controller::class, 'exportExcel']);
    // Route::get('/gstr2b/export-excel', [GstSalesReportController::class, 'exportExcel']);
    Route::get('/gstr3b/export-excel', [GstSalesReportController::class, 'exportGstr3b']);



    Route::get('logout', [LoginController::class, 'logoutapi'])->name('logout');
    Route::get('getAllProduct', [ProductController::class, 'getAllProduct'])->name('getAllProduct');
    Route::post('createProduct', [ProductController::class, 'createProduct'])->name('createProduct');
    Route::post('updateProduct', [ProductController::class, 'updateProduct'])->name('updateProduct');
    Route::post('deleteProduct/{id}', [ProductController::class, 'deleteProduct'])->name('deleteProduct');
    Route::post('importProducts', [ProductController::class, 'importProducts'])->name('importProducts');
    Route::post('remove-product-image', [ProductController::class, 'removeProductImage'])->name('removeProductImage');
    Route::get('/product-quantity-history/{productId}', [ProductController::class, 'getQuantityHistory']);
    Route::get('/export-product', [ProductController::class, 'export_product']);
    Route::get('/export-product-pdf', [ProductController::class, 'export_product_pdf']);


    Route::get('/get-row-materials', [RowMaterialController::class, 'getAllRowMaterials'])->name('getAllRowMaterial');
    Route::post('/create-row-material', [RowMaterialController::class, 'createRowMaterial'])->name('createRowMaterial');
    Route::post('/update-row-material', [RowMaterialController::class, 'updateRowMaterial'])->name('updateRowMaterial');
    Route::post('/update_row_material', [RowMaterialController::class, 'updateRowMaterial']); // Alias for blade compatibility
    Route::post('/delete-row-material/{id}', [RowMaterialController::class, 'deleteRowMaterial'])->name('deleteRowMaterial');
    Route::get('/export-row-material', [RowMaterialController::class, 'export_row_material']);
    Route::get('/export-row-material-pdf', [RowMaterialController::class, 'export_row_material_pdf']);
    Route::get('/edit-row-material/{id}', [RowMaterialController::class, 'edit_row_material']);
    Route::get('/edit_row_material/{id}', [RowMaterialController::class, 'edit_row_material']); // Alias for blade compatibility
    Route::get('/rowmaterial-view/{id}', [RowMaterialController::class, 'getRowMaterial']); // For detail view


    Route::get('income-statement', [IncomeSheetController::class, 'GetAll']);
    Route::get('balance-sheet', [BalanceSheetController::class, 'getBalanceSheet']);

    Route::get('/get-category', [ProductController::class, 'getCategory']);
    Route::get('/get-brand', [ProductController::class, 'getBrand']);
    Route::get('/get-tax-rates', [ProductController::class, 'getTaxRates']);
    Route::get('/edit_product/{id}', [ProductController::class, 'edit_product']);
    Route::get('/get-units', [ProductController::class, 'getUnits']);

    Route::get('/order-report', [SalesController::class, 'orderReport']);

    //Purchase Return
    Route::get('get-vendor-name/{invoiceId}', [PurchaseReturnController::class, 'getVendorName']);
    Route::get('get-invoice-products/{invoiceId}', [PurchaseReturnController::class, 'getInvoiceProducts']);
    Route::post('return-purchase', [PurchaseReturnController::class, 'returnPurchase']);
    Route::get('/purchase-return-list', [PurchaseReturnController::class, 'purchaseReturnList'])->name('purchaseReturnList');


    Route::get('getProfile', [ProfileController::class, 'getProfile'])->name('getProfile');
    Route::post('updateProfile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
    Route::get('getProductById/{num:}', [ProductController::class, 'getProductById'])->name('getProductById');
    Route::get('get_subadmin', [ProfileController::class, 'get_subadmin'])->name('get_subadmin');

    Route::post('createCustomer', [CustomerController::class, 'createCustomer'])->name('createCustomer');
    Route::post('deleteCustomer/{num:}', [CustomerController::class, 'deleteCustomer'])->name('deleteCustomer');
    Route::get('/getCustomer/{id}', [CustomerController::class, 'getCustomer'])->name('getCustomer');
    Route::post('/updateCustomer/{id}', [CustomerController::class, 'updateCustomer'])->name('updateCustomer');
    Route::get('getAllCustomer', [CustomerController::class, 'getAllCustomer'])->name('getAllCustomer');
    // Route::get('/customer-view/{id}/data', [CustomerController::class, 'getCustomerData']);
    Route::get('customer/{id}', [CustomerController::class, 'getCustomerProfile']);

    Route::post('/fetch-gst-details', [SupplierController::class, 'fetch'])->name('gst.fetch');
    Route::post('createSupplier', [SupplierController::class, 'createSupplier'])->name('createSupplier');
    Route::post('deleteSupplier/{num:}', [SupplierController::class, 'deleteSupplier'])->name('deleteSupplier');
    Route::get('/getSupplier/{id}', [SupplierController::class, 'getSupplier'])->name('getSupplier');
    Route::post('/updateSupplier/{id}', [SupplierController::class, 'updateSupplier'])->name('updateSupplier');
    Route::get('getAllSupplier', [SupplierController::class, 'getAllSupplier'])->name('getAllSupplier');
    Route::get('vendor/{id}', [SupplierController::class, 'getVendorProfile']);

    Route::get('getAllCategory', [CategoryController::class, 'getAllCategory'])->name('getAllCategory');
    Route::post('addcategory', [CategoryController::class, 'addcategory'])->name('addcategory');
    Route::post('deleteCategory/{id}', [CategoryController::class, 'deleteCategory'])->name('deleteCategory');
    Route::post('updatecategory', [CategoryController::class, 'updatecategory'])->name('updatecategory');

    Route::get('getAllBrand', [BrandController::class, 'getAllBrand'])->name('getAllBrand');
    Route::post('addBrand', [BrandController::class, 'addBrand'])->name('addBrand');

    Route::get('/tax-rates', [TaxRateController::class, 'index'])->name('tax-rates.index');
    Route::post('/tax-rates/store', [TaxRateController::class, 'store'])->name('tax-rates.store');
    Route::get('/tax-rates/{id}', [TaxRateController::class, 'edit'])->name('tax-rates.edit');
    Route::post('/tax-rates/update/{id}', [TaxRateController::class, 'update'])->name('tax-rates.update');
    Route::post('/tax-rates/delete/{id}', [TaxRateController::class, 'destroy'])->name('tax-rates.destroy');

    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
    Route::post('/currency/store', [TaxRateController::class, 'store'])->name('currency.store');
    Route::get('/currency/{id}', [TaxRateController::class, 'edit'])->name('currency.edit');
    Route::post('/currency/update/{id}', [TaxRateController::class, 'update'])->name('currency.update');
    Route::post('/currency/delete/{id}', [TaxRateController::class, 'destroy'])->name('currency.destroy');


     // credit notes type
    Route::get('/credit-notes-types', [CreditNotesTypeController::class, 'index'])->name('credit-notes-types.index');
    Route::post('/credit-notes-types/store', [CreditNotesTypeController::class, 'store'])->name('credit-notes-types.store');
    Route::get('/credit-notes-types/{id}', [CreditNotesTypeController::class, 'show'])->name('credit-notes-types.show');
    Route::post('/credit-notes-types/update/{id}', [CreditNotesTypeController::class, 'update'])->name('credit-notes-types.update');
    Route::post('/credit-notes-types/delete/{id}', [CreditNotesTypeController::class, 'destroy'])->name('credit-notes-types.destroy');

    // credit notes items
    Route::get('/credit-note-items-api', [CreditNoteItemApiController::class, 'index'])->name('credit-note-items.api.index');
    Route::get('/getPurchaseDetails/{id}', [CreditNoteItemApiController::class, 'getPurchaseDetails'])->name('getPurchaseDetails');
    Route::post('/credit-note-items-api/store', [CreditNoteItemApiController::class, 'store'])->name('credit-note-items.api.store');
    Route::get('/credit-note-items-api/{id}', [CreditNoteItemApiController::class, 'show'])->name('credit-note-items.api.show');
    Route::post('/credit-note-items-api/update/{id}', [CreditNoteItemApiController::class, 'update'])->name('credit-note-items.api.update');
    Route::post('/credit-note-items-api/delete/{id}', [CreditNoteItemApiController::class, 'destroy'])->name('credit-note-items.api.destroy');

    // debit note items
    Route::get('/debit-note-items', [DebitNoteItemApiController::class, 'index'])->name('debit-note-items.index');
    Route::post('/debit-note-items/store', [DebitNoteItemApiController::class, 'store'])->name('debit-note-items.store');
    Route::get('/debit-note-items/create-data', [DebitNoteItemApiController::class, 'getCreateData'])->name('debit-note-items.create-data');
    Route::get('/get-invoice-details/{invoice_number}', [DebitNoteItemApiController::class, 'getInvoiceDetails'])->name('get-invoice-details');
    Route::get('/get-order-details/{order_number}', [DebitNoteItemApiController::class, 'getOrderDetails'])->name('get-order-details');
    Route::get('/debit-note-items/{id}', [DebitNoteItemApiController::class, 'show']);
    Route::post('/debit-note-items/update/{id}', [DebitNoteItemApiController::class, 'update']);
    Route::post('/debit-note-items/delete/{id}', [DebitNoteItemApiController::class, 'destroy']);





    Route::get('/general-settings', [GeneralSettingController::class, 'show'])->name('general-settings.show');
    Route::post('/general-settings/update', [GeneralSettingController::class, 'update'])->name('general-settings.update');
    Route::post('/company-rules/update', [GeneralSettingController::class, 'updateCompanyRules'])->name('general-company-settings.update');

    Route::post('/connect-device', [ConnectedDevicesController::class, 'connect']);
    Route::get('/check-device/{code}', [ConnectedDevicesController::class, 'check']);
    Route::get('/disconnect-device/{code}', [ConnectedDevicesController::class, 'disconnect']);
    Route::post('/submit-device-scan', [ConnectedDevicesController::class, 'submitScan']);
     Route::get('/smtp-settings', [SmtpSettingController::class, 'show'])->name('smtp-settings.show');
    Route::post('/smtp-settings/update', [SmtpSettingController::class, 'update'])->name('smtp-settings.update');
    // Route::get('/generate-device-code', [ConnectedDevicesController::class, 'generateCode']);
    // Route::post('/connect-device', [ConnectedDevicesController::class, 'connect']);
    // Route::get('/check-device/{code}', [ConnectedDevicesController::class, 'check']);
    // Route::get('/disconnect-device/{code}', [ConnectedDevicesController::class, 'disconnect']);
    // Route::get('/get-session-device', [ConnectedDevicesController::class, 'getSessionDevice']);

     Route::get   ('/notifications',                [NotificationController::class, 'index']);
    Route::post  ('/notifications/read-all',       [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/delete-all',     [NotificationController::class, 'deleteAll']);
    Route::post  ('/notifications/{id}/read',      [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}/delete',    [NotificationController::class, 'delete']);

    Route::get('/facebook-app-configurations', [FacebookAppConfigurationController::class, 'index'])->name('facebook-app-configurations.index');
    Route::post('/facebook-app-configurations', [FacebookAppConfigurationController::class, 'store'])->name('facebook-app-configurations.store');
    // Specific routes must come before parameterized routes
    Route::get('/facebook-app-configurations/message-templates', [FacebookAppConfigurationController::class, 'getMessageTemplates'])->name('facebook-app-configurations.message-templates');
    Route::post('/facebook-app-configurations/store-templates', [FacebookAppConfigurationController::class, 'storeTemplates'])->name('facebook-app-configurations.store-templates');
    Route::get('/facebook-app-configurations/stored-templates', [FacebookAppConfigurationController::class, 'getStoredTemplates'])->name('facebook-app-configurations.stored-templates');
    Route::put('/facebook-app-configurations/templates/{templateId}/toggle-status', [FacebookAppConfigurationController::class, 'toggleTemplateStatus'])->name('facebook-app-configurations.toggle-template-status');
    Route::put('/facebook-app-configurations/templates/{templateId}/update-use-for', [FacebookAppConfigurationController::class, 'updateTemplateUseFor'])->name('facebook-app-configurations.update-template-use-for');
    // Parameterized routes come after specific routes
    Route::get('/facebook-app-configurations/{id}', [FacebookAppConfigurationController::class, 'show'])->name('facebook-app-configurations.show');
    Route::put('/facebook-app-configurations/{id}', [FacebookAppConfigurationController::class, 'update'])->name('facebook-app-configurations.update');
    Route::delete('/facebook-app-configurations/{id}', [FacebookAppConfigurationController::class, 'destroy'])->name('facebook-app-configurations.destroy');

    // bankbook
    Route::get('/bankbook', [TransactionApiController::class, 'bankBook'])->name('bankbook');
    Route::get('/export-bankbook-pdf', [TransactionApiController::class, 'export_bankbook_pdf']);
    Route::get('/export-bankbook-excel', [TransactionApiController::class, 'export_bankbook_excel']);



    Route::post('/purchase_order', [PurchaseController::class, 'purchase_order'])->name('purchase_order');
    Route::post('/row-material-purchase_order', [PurchaseController::class, 'row_material_purchase_order'])->name('row_material_purchase_order');
    Route::get('/row-material-purchase-list', [PurchaseController::class, 'row_material_purchase_list'])->name('row_material_purchase_list');
    Route::get('/purchase_list', [PurchaseController::class, 'purchase_list'])->name('purchase_list');
    Route::post('/purchase_delete', [PurchaseController::class, 'purchase_delete'])->name('purchase_delete');
    Route::get('/purchase_get/{id}', [PurchaseController::class, 'showPurchase'])->name('purchase_get');
    Route::post('/purchase_update/{id}', [PurchaseController::class, 'purchase_update'])->name('purchase_update');
    Route::get('/purchase-report-data', [PurchaseController::class, 'fetch_purchase_report']);
    Route::post('/make-payment-purchase', [PurchaseController::class, 'make_payment'])->name('make-payment.purchase');
    Route::get('/purchase/payment-history/{id}', [PurchaseController::class, 'getHistory1'])->name('purchase.payment_history');
    Route::post('/purchase-export-excel', [PurchaseController::class, 'export_purchase_excel']);
    Route::get('/export-purchase', [PurchaseController::class, 'export_purchase']);
    Route::get('/export-purchase-pdf', [PurchaseController::class, 'export_purchase_pdf']);
    Route::post('/purchases/report/export-pdf-api', [PurchaseController::class, 'export_purchases_report_pdf_api'])->name('purchase.reportapi.exportPdf');
    Route::post('/sales/report/export-pdf-api', [SalesController::class, 'export_sales_report_pdf_api'])->name('sale.reportapi.exportPdf');
    Route::post('/expense/report/export-pdf-api', [ExpenseController::class, 'expense_report_pdf_api'])->name('expense.reportapi.exportPdf');
    Route::get('/export-invoice-pdf-api', [CustomInvoiceController::class, 'exportInvoicePDFAPI'])->name('custom_invoice.pdfapi.export');
    Route::get('/export-invoice-excel-api', [CustomInvoiceController::class, 'exportCustom_invoice_api'])->name('custom_invoice.export');

    Route::post('deleteBrand/{id}', [BrandController::class, 'deleteBrand'])->name('deleteBrand');
    Route::post('updateBrand', [BrandController::class, 'updateBrand'])->name('updateBrand');

    Route::get('getProductsByCategory/{id}', [SalesController::class, 'getProductsByCategory'])->name('getProductsByCategory');

    Route::get('/product-view/{id}', [ProductController::class, 'getProductDetails']);
    // Route::get('/edit_product/{id}', [ProductController::class, 'getProductedit']);

    Route::post('order_sale', [SalesController::class, 'order_sale'])->name('order_sale');
    Route::get('get_orders', [SalesController::class, 'get_orders'])->name('get_orders');
    Route::get('getsalseById/{id}', [SalesController::class, 'getsalseById'])->name('getsalseById');
    Route::post('delete/{id}', [SalesController::class, 'delete'])->name('delete');
    Route::post('update_sale', [SalesController::class, 'update_sale'])->name('update_sale');
    Route::post('convert-quotation-to-sale/{id}', [SalesController::class, 'convertQuotationToSale'])->name('convert_quotation_to_sale');
    Route::get('/orders/filter', [SalesController::class, 'getFilteredOrders']);
    Route::get('/order/payment-history/{id}', [SalesController::class, 'getHistory1'])->name('order.payment_history');
    Route::get('/pdf-orders', [SalesController::class, 'exportOrdersPDF'])->name('pdf.export');
    Route::get('/export-order', [SalesController::class, 'exportOrders']);
    Route::get('/product-by-barcode/{barcode}', [SalesController::class, 'getByBarcode'])->name('getByBarcode');

    Route::get('/purchase-product-chart', [PurchaseController::class, 'purchaseProductChart']);


    Route::post('/expenses/store', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/list', [ExpenseController::class, 'getExpenses'])->name('expenses.list');
    Route::post('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/expenses/{id}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::get('/expenses/report', [ExpenseController::class, 'getExpensesReport']);

    // Expense-Type
    Route::post('/expense-type/store', [ExpenseTypeController::class, 'store'])->name('expense-type.store');
    Route::get('/expense-types', [ExpenseTypeController::class, 'list']);
    Route::get('/expense-types/{id}', [ExpenseTypeController::class, 'show']);
    Route::put('/expense-types/{id}', [ExpenseTypeController::class, 'update']);
    Route::post('/expenses-type/{id}', [ExpenseTypeController::class, 'destroy'])->name('expenses-type.destroy');

    Route::get('/getSaleDetails/{id}', [SaleReturnController::class, 'getSaleDetails'])->name('getSaleDetails');
    Route::get('/sales-return-list', [SaleReturnController::class, 'salesReturnList'])->name('sales_return_list');
    Route::post('/return_sale', [SaleReturnController::class, 'return_sale'])->name('return_sale');

    // GST / GSTR-3B report endpoint
    Route::get('/gstr-3b', [GstController::class, 'gstr3b']);

    Route::post('/customer_invoice/store', [CustomInvoiceController::class, 'store'])->name('/customer_invoice/store');
    Route::get('/custom_invoice_list', [CustomInvoiceController::class, 'list'])->name('/customer_invoice/list');
    Route::post('/custom_invoice_delete', [CustomInvoiceController::class, 'delete'])->name('/customer_invoice/delete');
    Route::get('/edit-invoice/{id}', [CustomInvoiceController::class, 'getInvoiceData']);
    Route::post('/custom-invoice/make-payment', [CustomInvoiceController::class, 'makePaymentSubmit'])->name('custom_invoice.make_payment_submit');

    Route::get('/custom_invoice_get/{id}', [CustomInvoiceController::class, 'get'])->name('/customer_invoice/get');
    Route::post('/custom_invoice_update/{id}', [CustomInvoiceController::class, 'update'])->name('/customer_invoice/update');
    Route::get('/custom_invoice/payment-history/{id}', [CustomInvoiceController::class, 'getHistory1'])->name('custom_invoice.payment_history');

    Route::post('createStaff', [StaffController::class, 'createStaff'])->name('createCustomer');
    Route::post('deleteStaff/{num:}', [StaffController::class, 'deleteStaff'])->name('deleteCustomer');
    Route::get('/getStaff/{id}', [StaffController::class, 'getStaff'])->name('getCustomer');
    Route::post('/updateStaff/{id}', [StaffController::class, 'updateStaff'])->name('updateCustomer');
    Route::get('getAllStaff', [StaffController::class, 'getAllStaff'])->name('getAllCustomer');
    // Route::get('/customer-view/{id}/data', [CustomerController::class, 'getCustomerData']);
    Route::get('staff/{id}', [StaffController::class, 'getStaffProfile']);
    Route::get('modules', [StaffController::class, 'index']);

    // TDS API (separate module)
    Route::post('/tds/store', [TdsController::class, 'store']);
    Route::get('/tds/list', [TdsController::class, 'list']);
    Route::get('/tds/export', [TdsController::class, 'export']);

    Route::post('createSubbranch', [SubBranchController::class, 'createSubbranch'])->name('createSubbranch');
    Route::post('deleteSubbranch/{num:}', [SubBranchController::class, 'deleteSubbranch'])->name('deleteSubbranch');
    Route::get('/getSubbranch/{id}', [SubBranchController::class, 'getSubbranch'])->name('getSubbranch');
    Route::post('/updateSubbranch', [SubBranchController::class, 'updateSubbranch'])->name('updateSubbranch');
    Route::get('getAllSubbranch', [SubBranchController::class, 'getAllSubbranch'])->name('getAllSubbranch');
    // Route::get('/customer-view/{id}/data', [CustomerController::class, 'getCustomerData']);
    Route::get('subbranch/{id}', [SubBranchController::class, 'getSubbranchProfile']);

    Route::get('/account-ledger', [AccountLedgerController::class, 'getAccountLedger'])->name('account_ledger.list');
    Route::get('/get-payment-details', [AccountLedgerController::class, 'getPaymentDetails'])->name('get.payment.details');
    Route::get('/export-account-ledger', [AccountLedgerController::class, 'exportAccountLedgerExcel']);
    Route::post('/account-ledger/download-pdf', [AccountLedgerController::class, 'downloadAccountLedgerPDF']);


    Route::get('/inventory-list', [InventoryController::class, 'index'])->name('inventory.list');
    Route::get('/products_edit_inventroy/{id}', [InventoryController::class, 'products_edit_inventroy'])->name('inventory.edit');
    Route::post('/inventory_update/{id}', [InventoryController::class, 'inventory_update'])->name('inventory.update');
    Route::get('/getQuantityHistory_inventory/{id}', [InventoryController::class, 'getQuantityHistory_inventory'])->name('inventory.getQuantityHistory_inventory');
    Route::get('/row-material-inventory-list', [RowMaterialInventoryController::class, 'index'])->name('row_material.inventory.list');
    Route::get('/row-materials_edit_inventory/{id}', [RowMaterialInventoryController::class, 'products_edit_inventroy'])->name('row_material.inventory.edit');
    Route::post('/row-material-inventory_update/{id}', [RowMaterialInventoryController::class, 'inventory_update'])->name('row_material.inventory.update');
    Route::get('/row-material/getQuantityHistory_inventory/{id}', [RowMaterialInventoryController::class, 'getQuantityHistory_inventory'])->name('row_material.inventory.history');

    Route::get('/products/filter', [InventoryController::class, 'filter']);
    Route::get('/manufacturing/products', [ManufacturingController::class, 'productOptions']);
    Route::get('/manufacturing/materials', [ManufacturingController::class, 'materialOptions']);
    Route::get('/manufacturing/boms', [ManufacturingController::class, 'bomList']);
    Route::get('/manufacturing/boms/{id}', [ManufacturingController::class, 'bomDetails']);
    Route::post('/manufacturing/boms', [ManufacturingController::class, 'storeBom']);
    Route::put('/manufacturing/boms/{id}', [ManufacturingController::class, 'updateBom']);
    Route::get('/manufacturing/bom-for-product/{productId}', [ManufacturingController::class, 'bomForProduct']);
    Route::post('/manufacturing/production-preview', [ManufacturingController::class, 'productionPreview']);
    Route::get('/manufacturing/productions', [ManufacturingController::class, 'productionList']);
    Route::get('/manufacturing/productions/{id}', [ManufacturingController::class, 'productionDetails']);
    Route::post('/manufacturing/productions', [ManufacturingController::class, 'storeProduction']);
    Route::put('/manufacturing/productions/{id}', [ManufacturingController::class, 'updateProduction']);

    Route::get('/sales/payment-history/{id}', [SalesController::class, 'getHistory'])->name('sales.payment_history');
    Route::post('/sales/make-payment', [SalesController::class, 'makePaymentSubmit'])->name('sales.make_payment_submit');
    // Salary
    Route::get('/salaries', [SalaryController::class, 'index']);
    Route::post('/salaries/pay', [SalaryController::class, 'store']);
    Route::put('/salaries/update/{id}', [SalaryController::class, 'update']);
    Route::get('/salaries/years', [SalaryController::class, 'getSalaryYears']);
    Route::get('/staff', [SalaryController::class, 'getActiveStaff']);
    Route::get('/salaries/export', [SalaryController::class, 'export']);
    Route::get('/salaries/pdf', [SalaryController::class, 'generatePDF']);
    // Route::get('/salaries/staff/pdf', [SalaryController::class, 'generateStaffPDF']);
    Route::get('/salaries/staff/pdf', [SalaryController::class, 'generateStaffPDF'])->name('salary.staff_pdf');

    Route::get('/api-attendance', [AttendanceController::class, 'index']);

    //advance pay
    Route::post('/advance-payments/{id}', [AdvancePaymentController::class, 'update']);

        //Labour Item
    Route::get('/get-all-labour-items', [LabourItemController::class, 'getAllLabourItems']);
    Route::post('/add-labour-item', [LabourItemController::class, 'addLabourItem']);
    Route::post('/update-labour-item/{id}', [LabourItemController::class, 'updateLabourItem']);
    Route::post('/delete-labour-item/{id}', [LabourItemController::class, 'deleteLabourItem']);

    Route::apiResource('advance-payments', AdvancePaymentController::class);
    // routes/api.php
    Route::get('/advance-payment-filters', [AdvancePaymentController::class, 'getFilters']);
    Route::get('/export-advance-payments', [AdvancePaymentController::class, 'exportAdvancePayments'])
        ->name('export.advance-payments');
    Route::get('/advance-history', [SalaryController::class, 'getAdvanceHistory']);


    //banks


    Route::get('/all-banks', [BankMasterController::class, 'index'])->name('banks.index');
    Route::get('/banks/create', [BankMasterController::class, 'create'])->name('banks.create');
    Route::get('/banks/data', [BankMasterController::class, 'getData'])->name('banks.data');
    Route::post('/banks', [BankMasterController::class, 'store'])->name('banks.store');
    Route::get('/banks/{id}', [BankMasterController::class, 'show'])->name('banks.show');
    Route::get('/banks/{id}/edit', [BankMasterController::class, 'edit'])->name('banks.edit');
    Route::put('/banks/{id}', [BankMasterController::class, 'update'])->name('banks.update');
    Route::delete('/delete/banks/{id}', [BankMasterController::class, 'destroy'])->name('banks.destroy');

    Route::get('/cashbook/data', [TransactionController::class, 'getCashbookData'])->name('cashbook.data');
    Route::get('/export-cashbook-pdf', [TransactionController::class, 'exportCashbookPdf'])->name('cashbook.export_pdf');
    Route::get('/export-cashbook-excel', [TransactionController::class, 'exportCashbookExcel']);

    Route::get('/appointments', [AppointmentController::class, 'getAllAppointments']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::post('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    Route::get('/export-appointments', [AppointmentController::class, 'exportAppointments']);

    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/filter', [AttendanceController::class, 'filter'])->name('attendance.filter');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::post('/attendance/bulk-store', [AttendanceController::class, 'bulkStore'])->name('attendance.bulk-store');

    Route::get('/gst/gstr-3b/export', [GstController::class, 'exportGstr3b'])->name('gst.gstr3b.export');
    Route::get('/gstr3b/export', [GstSalesReportController::class, 'exportGstr3b']);

    Route::post('/add-units', [ApiUnitController::class, 'store'])->name('units.store');
    Route::get('/units', [ApiUnitController::class, 'index'])->name('units.index');
    Route::post('/update-unit/{id}', [ApiUnitController::class, 'update'])->name('units.update');
    Route::delete('/delete-unit/{id}', [ApiUnitController::class, 'destroy'])->name('units.destroy');
    });



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
