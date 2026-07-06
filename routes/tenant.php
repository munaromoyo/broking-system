<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use App\Http\Controllers\ClientStatementController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TenantProfileController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\DebtorController;
use App\Http\Controllers\RemittanceController;

// =========================================================================
// THE MASTER TENANT ROUTE ENVELOPE (Everything lives inside here)
// =========================================================================
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // --- PUBLIC/HOME ROUTE ---
    Route::get('/', function () {
        return view('home'); 
    });

    // --- GUEST ROUTES (Login/Register) ---
    Route::middleware('guest')->group(function () {
        Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [RegisterController::class, 'register']);

        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });

    // --- PROTECTED TENANT ROUTES (Requires Auth) ---
    Route::middleware('auth')->group(function () {
        
        // --- EMAIL VERIFICATION SYSTEM ---
        Route::get('/email/verify', function () {
            return view('auth.verify-email');
        })->name('verification.notice');

        Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            $request->fulfill();
            return redirect('/dashboard')->with('status', 'Account activated!');
        })->middleware(['signed'])->name('verification.verify');

        Route::post('/email/verification-notification', function (Request $request) {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('message', 'Verification link sent!');
        })->middleware(['throttle:6,1'])->name('verification.send');

        // --- DASHBOARDS ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/insurance_broking/dashboard/{action?}', [InsuranceController::class, 'dashboard'])->name('insurance_broking.dashboard');
            
        Route::post('/logout', function () {
            auth()->logout();
            return redirect('/');
        })->name('logout');

        // --- USER MANAGEMENT ---
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // --- FINANCE OPERATIONS & STATEMENTS ---
        Route::get('/finance/vouchers', [FinanceController::class, 'index'])->name('finance.vouchers.show');
        Route::post('/finance/vouchers/{id}/{action}', [PaymentVoucherController::class, 'updateStatus'])->name('finance.vouchers.update-status');
        Route::get('/finance/income-statement', [FinanceController::class, 'incomeStatement'])->name('finance.income');
        Route::get('/finance/ledger-accounts', [FinanceController::class, 'ledgerAccounts'])->name('finance.ledger');
        Route::get('/finance/vouchers/download/{id}', [PaymentVoucherController::class, 'download'])->name('finance.vouchers.download');

        // --- BALANCE SHEET ---
        Route::get('/finance/balance-sheet', [BalanceSheetController::class, 'index'])->name('finance.balance-sheet');
        Route::post('/finance/balance-sheet/store', [BalanceSheetController::class, 'store'])->name('finance.balance-sheet.store');

        // --- CORE VOUCHER MANAGEMENT ---
        Route::get('/vouchers', [PaymentVoucherController::class, 'index'])->name('vouchers');
        Route::post('/vouchers/register', [PaymentVoucherController::class, 'store'])->name('vouchers.store');
        Route::post('/vouchers/store', [InsuranceController::class, 'storeVoucher'])->name('vouchers.store_insurance_voucher');
        Route::get('/vouchers/{id}/{action}', [InsuranceController::class, 'approveOrRejectVoucher'])
            ->name('vouchers.status')
            ->where('action', 'approve|reject');

        // --- JOURNAL VOUCHERS ---
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/journal-voucher', [JournalController::class, 'create'])->name('journal-voucher');
            Route::post('/journal-voucher', [JournalController::class, 'store'])->name('journal-voucher.store');
        });

        // --- INSURANCE PLACEMENT REGISTRIES ---
        Route::get('/insurance_broking/register/{action?}', [InsuranceController::class, 'index'])->name('insurance_broking.register');
        Route::post('/insurance_broking/store', [InsuranceController::class, 'store'])->name('insurance_broking.store'); 
        Route::get('/insurance_broking/list/{action?}', [InsuranceController::class, 'index'])->name('insurance_broking.view_list.index');

        // --- CANCELLATIONS ---
        Route::get('/cancellations/{id}', [InsuranceController::class, 'showCancelledSlip'])->name('insurance_broking.cancelled_slips.show');
        Route::get('/cancellations/{id}/edit', [InsuranceController::class, 'editCancelledSlip'])->name('insurance_broking.cancelled_slips.edit');
        Route::put('/cancellations/{id}', [InsuranceController::class, 'updateCancelledSlip'])->name('insurance_broking.cancelled_slips.update');

        // --- INVOICES GENERATION & REPORTS ---
        Route::get('/finance/generate_invoices', [InsuranceController::class, 'generateInvoices'])->name('insurance_broking.accounts.invoices.generate_invoice');
        Route::post('/finance/create_invoice', [InsuranceController::class, 'createInvoice'])->name('insurance.create_invoice');
        Route::get('/finance/invoices/view_list', [InsuranceController::class, 'viewInvoice_list'])->name('insurance_broking.accounts.invoices.view_list');
        Route::get('/insurance-broking/accounts/monthly-business-report', [InsuranceController::class, 'monthlyBusiness_report'])->name('insurance_broking.accounts.invoices.monthly_business_done');
        Route::get('/insurance-broking/accounts/download{id}', [InsuranceController::class, 'downloadInvoice'])->name('insurance_broking.accounts.invoices.generate_pdf');
       
        // --- INSURER REMITTANCES (FIXED: Safely within tenancy context) ---
        Route::get('/insurance-broking/accounts/remittance', [RemittanceController::class, 'index'])->name('insurance_broking.accounts.insurer_remittances.show');
        Route::get('/remittance/export-summary', [RemittanceController::class, 'exportFullSummary'])->name('insurance_broking.accounts.insurer_remittances.summary');

        // --- CLIENT RECORDS MANAGEMENT ---
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.list');
        Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::post('/clients/store', [ClientController::class, 'store'])->name('clients.store');
        Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
        Route::post('/client-register', [InsuranceController::class, 'registerClient'])->name('client.register');
        Route::post('/potential-clients', [QuotationController::class, 'storePotentialClient'])->name('potential_client.store');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/trash', [ClientController::class, 'trash'])->name('trash');
            Route::patch('/{id}/restore', [ClientController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [ClientController::class, 'forceDelete'])->name('force-delete');
        });

        // --- INSURER REGISTRY UPLOADS ---
        Route::post('/insurers/store', [ClientController::class, 'storeInsurer'])->name('insurers.store');

        // --- GENERAL BANK IMPORTS ---
        Route::post('/bank/import', [BankController::class, 'importBankTransactions'])->name('bank.import');

        // --- CLAIMS MODULE ---
        Route::get('/insurance_broking/claims/{id}', [InsuranceController::class, 'showClaim'])->name('insurance_broking.claims.show');
        Route::get('/insurance_broking/claims/{id}/edit', [InsuranceController::class, 'editClaim'])->name('insurance_broking.claims.edit');
        Route::put('/insurance_broking/claims/{id}/update', [InsuranceController::class, 'updateClaim'])->name('insurance_broking.claims.update');
        Route::post('/claims/{id}/delete-file', [InsuranceController::class, 'deleteFile'])->name('insurance_broking.claims.delete_file');

        // --- ACCOUNTS SUMMARY PANELS ---
        Route::prefix('insurance_broking/accounts')->group(function () {
            Route::get('/payment_voucher', [InsuranceController::class, 'paymentVoucherIndex']);
            Route::get('/statements', [InsuranceController::class, 'statementsIndex']);
        });
        // Ensure this is Route::post to match your fetch request payload exactly
        Route::post('/insurance-broking/accounts/payment-vouchers/{id}/delete-file', [PaymentVoucherController::class, 'delete_file'])->name('insurance_broking.accounts.payment_vouchers.delete_file');

        // --- SLIP UTILITIES ---
        Route::get('/insurance/edit/slip/{id}', [InsuranceController::class, 'editSlip'])->name('insurance_broking.placement_slips.edit');
        Route::post('/insurance/edit/slip/{id}', [InsuranceController::class, 'updateSlip'])->name('insurance_broking.placement_slips.update');
        Route::post('/insurance/slip/cancel', [InsuranceController::class, 'cancelSlip'])->name('insurance_broking.placement_slips.cancel');
        Route::post('/insurance_broking/placement-slips/renew', [InsuranceController::class, 'renew'])->name('insurance_broking.placement_slips.renew');
        Route::get('/insurance/register/{action?}', [InsuranceController::class, 'index'])->name('insurance_broking.register');
        Route::get('/insurance/register-clone', [InsuranceController::class, 'cloneSlip'])->name('insurance_broking.register_clone');

        // --- DOCUMENT EXPORTS ---
        Route::get('/insurance/slip/{id}/pdf', [InsuranceController::class, 'generateSlipPdf'])->name('insurance_broking.placement_slips.pdf_slip');
        Route::get('/insurance/kfs/{id}/pdf', [InsuranceController::class, 'generateKfsPdf'])->name('insurance_broking.placement_slips.pdf_kfs');
        Route::get('/insurance/slip/{id}', [InsuranceController::class, 'showSlip'])->name('insurance_broking.placement_slips.show');

        // --- FLEET / VEHICLE BULK LOADING ---
        Route::get('/vehicle-upload/download', [InsuranceController::class, 'downloadTemplate'])->name('insurance_broking.vehicle_upload.download_template');
        Route::post('/insurance/bulk-store', [InsuranceController::class, 'bulkStore'])->name('insurance.bulk_store');

        // --- QUOTATION CONTEXTS ---
        Route::prefix('quotation')->name('insurance_broking.quotations.')->group(function () {
            Route::get('/register', [QuotationController::class, 'index'])->name('create');
            Route::post('/register/store', [QuotationController::class, 'storeQuote'])->name('store');
            Route::get('/list', [QuotationController::class, 'quotationRegistry'])->name('list');
            Route::get('/view/{id}', [QuotationController::class, 'showQuote'])->name('show');
            Route::get('/{id}/edit', [QuotationController::class, 'editQuote'])->name('edit');
            Route::put('/{id}', [QuotationController::class, 'updateQuote'])->name('update');
            Route::get('/{id}/pdf', [QuotationController::class, 'downloadPdf'])->name('pdf');
        });

        // --- USER PERSONAL SETTINGS ---
        Route::get('/account/edit', [AccountController::class, 'edit'])->name('account.edit');
        Route::post('/account/edit', [AccountController::class, 'update'])->name('account.update');

        // --- ACCOUNT RECEIPTS INFRASTRUCTURE ---
        Route::prefix('insurance-broking/accounts')->name('insurance_broking.accounts.')->group(function () {
            Route::get('receipts', [ReceiptController::class, 'showReceipt'])->name('receipts.show');
            Route::post('receipts/store', [ReceiptController::class, 'storePayment'])->name('receipts.store-payment');
            Route::post('receipts/remit', [ReceiptController::class, 'remitPremium'])->name('receipts.remit');
            Route::post('receipts/allocate', [ReceiptController::class, 'allocateReceipt'])->name('receipts.show');
            Route::post('receipts/cancel', [ReceiptController::class, 'cancelReceipt'])->name('receipts.cancel');
            Route::get('receipts/pdf/{id}', [ReceiptController::class, 'generatePdf_receipt'])->name('receipts.generate_pdf');
            Route::get('receipts/template/download', [ReceiptController::class, 'bankReconTemplate'])->name('receipts.bank_recon_template');
            Route::get('receipts/import', [ReceiptController::class, 'importView'])->name('receipts.import');
            Route::post('receipts/import', [ReceiptController::class, 'importStore'])->name('receipts.import.store');
            Route::post('accounts/receipts/store-payment', [ReceiptController::class, 'postPayment'])->name('receipts.store_receipt');
            Route::post('/premium/remit', [ReceiptController::class, 'remitPremium'])->name('receipts.remit');
            Route::post('/receipts/allocate', [ReceiptController::class, 'allocateReceipt'])->name('receipts.allocate');
            Route::post('/receipts/cancel', [ReceiptController::class, 'handleCancelRequest'])->name('receipts.cancel');
        });

        // --- CLIENT STATEMENTS GENERATION ---
        Route::get('/insurance-broking/client/statements/view', [ClientStatementController::class, 'index'])->name('insurance_broking.accounts.client_statements.index');
        Route::post('/insurance-broking/client/statements/view', [ClientStatementController::class, 'index']);
        Route::post('/insurance-broking/statements/print', [ClientStatementController::class, 'printStatement'])->name('insurance_broking.accounts.client_statements.pdf');

        // --- CREDIT NOTES ROUTING ---
        Route::get('/credit-notes/generate', [CreditNoteController::class, 'generateCreditNote'])->name('insurance_broking.accounts.credit_notes.generate');
        Route::post('/credit-notes/generate', [CreditNoteController::class, 'storeCreditNote'])->name('insurance_broking.accounts.credit_notes.store');
        Route::get('/credit-notes', [CreditNoteController::class, 'index'])->name('insurance_broking.accounts.credit_notes.show');
        Route::get('/credit-notes/{slip_id}/pdf', [CreditNoteController::class, 'downloadPdf'])->name('insurance_broking.accounts.credit_notes.pdf');

        // --- BACK-OFFICE PAYMENT VOUCHERS ---
        Route::get('/vouchers', [PaymentVoucherController::class, 'index_handler'])->name('insurance_broking.accounts.payment_vouchers.show');
        Route::post('/vouchers', [PaymentVoucherController::class, 'store_handler'])->name('insurance_broking.accounts.payment_vouchers.store');
        Route::put('/vouchers/update', [PaymentVoucherController::class, 'update_handler'])->name('insurance_broking.accounts.payment_vouchers.update');
        Route::get('/vouchers/{id}/print', [PaymentVoucherController::class, 'print_handler'])->name('insurance_broking.accounts.payment_vouchers.print');

        // --- DEBTORS AGING SCHEDULES ---
        Route::get('/insurance-broking/accounts/debtors', [DebtorController::class, 'index'])->name('insurance_broking.accounts.debtors.index');
        Route::get('/insurance-broking/accounts/debtors/download', [DebtorController::class, 'downloadPdf'])->name('insurance_broking.accounts.debtors.download');
        Route::post('/insurance-broking/accounts/statements/view', [ClientStatementController::class, 'view'])->name('insurance_broking.accounts.debtors.view_statements');

    });

    // --- TENANT ADMINISTRATORS ISOLATED PANEL (Nested safely inside the tenant stack) ---
    Route::middleware(['auth', 'tenant.admin'])->group(function () {
        Route::get('/admin/profile', [TenantProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/admin/profile', [TenantProfileController::class, 'update'])->name('admin.profile.update');
    });

    // --- FALLBACK INTERCEPTOR ---
    Route::fallback(function () {
        return "You are on the TENANT site, but the specific URL is wrong.";
    });
});