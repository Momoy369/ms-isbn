<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookFileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ISBNQueueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductionDashboardController;
use App\Http\Controllers\AssignmentHistoryController;
use App\Http\Controllers\ProductionReportController;
use App\Http\Controllers\AuthorDashboardController;
use App\Http\Controllers\AuthorReviewController;
use App\Http\Controllers\AuthorInvoiceController;
use App\Http\Controllers\FinanceInvoiceController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\InvoiceDocumentController;
use App\Http\Controllers\AdminFinanceReportController;
use App\Http\Controllers\AdditionalServiceController;
use App\Http\Controllers\AdminPrintPriceController;
use App\Http\Controllers\AdminExternalSalesController;
use App\Http\Controllers\AdminLegacyBookController;
use App\Http\Controllers\AdminRoyaltyPayoutController;
use App\Http\Controllers\AdminStoreCatalogController;
use App\Http\Controllers\AdminStoreOrderController;
use App\Http\Controllers\AdminStoreVoucherController;
use App\Http\Controllers\AdminStorePackageConsultationController;
use App\Http\Controllers\AuthorOrderController;
use App\Http\Controllers\AuthorBookClaimController;
use App\Http\Controllers\AdminBookClaimController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductionTimelineController;
use App\Http\Controllers\BookMessageController;
use App\Http\Controllers\BookChapterController;
use App\Http\Controllers\RoleFileController;
use App\Http\Controllers\PrintingWorkspaceController;
use App\Http\Controllers\AuthorFinalFileController;
use App\Http\Controllers\AuthorRoyaltyController;
use App\Http\Controllers\LayoutGeneratorController;
use App\Http\Controllers\PublishingPackageController;
use App\Http\Controllers\BookPackageItemController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\EbookPublishingWorkspaceController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerEbookLibraryController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\AdminAuthorUpgradeRequestController;

Route::get('/', function () {

    if (!auth()->check()) {

        return redirect()
            ->route('login');
    }

    $user = auth()->user();

    if ($user->role === 'author') {

        return redirect()
            ->route('author.dashboard');
    }

    if (in_array($user->role, ['editor', 'layouter', 'designer'], true)) {
        return redirect()->route('assignments.my');
    }

    if (in_array($user->role, ['customer', 'reader'], true)) {
        return redirect()->route('customer.dashboard');
    }

    return redirect()
        ->route('dashboard');

});

require __DIR__ . '/auth.php';

Route::get('/store', [StorefrontController::class, 'index'])
    ->name('store.index');

Route::get('/store/track', [StorefrontController::class, 'trackForm'])
    ->name('store.track.form');

Route::post('/store/track', [StorefrontController::class, 'trackLookup'])
    ->name('store.track.lookup');

Route::get('/store/track/{orderNumber}', [StorefrontController::class, 'trackShow'])
    ->name('store.track.show');

Route::post('/store/track/{orderNumber}/reader', [StorefrontController::class, 'reader'])
    ->name('store.reader');

Route::get('/store/track/{orderNumber}/reader', [StorefrontController::class, 'readerView'])
    ->name('store.reader.view');

Route::get('/store/shipping/cities', [StorefrontController::class, 'shippingCities'])
    ->name('store.shipping.cities');

Route::view('/store/policies', 'store.policies')
    ->name('store.policies');

Route::get('/store/paket/configurator', [StorefrontController::class, 'packageConfigurator'])
    ->name('store.package-configurator');

Route::post('/store/paket/configurator', [StorefrontController::class, 'submitPackageConfigurator'])
    ->name('store.package-configurator.submit');

Route::get('/store/{slug}', [StorefrontController::class, 'show'])
    ->name('store.show');

Route::post('/store/{item}/order', [StorefrontController::class, 'placeOrder'])
    ->name('store.order');

Route::post('/payments/ipaymu/callback', [PaymentGatewayController::class, 'callback'])
    ->name('payments.ipaymu.callback');

Route::middleware('auth')->group(function () {

    Route::middleware(['role:admin,editor,layouter,designer,isbn,owner,finance,superadmin,author'])->group(function () {
        Route::get('/role-files', [RoleFileController::class, 'index'])
            ->name('role-files.index');

        Route::post('/role-files', [RoleFileController::class, 'store'])
            ->name('role-files.store');

        Route::get('/role-files/{roleFile}/preview', [RoleFileController::class, 'preview'])
            ->name('role-files.preview');

        Route::get('/role-files/{roleFile}/download', [RoleFileController::class, 'download'])
            ->name('role-files.download');

        Route::post('/role-files/{roleFile}/share', [RoleFileController::class, 'share'])
            ->name('role-files.share');

        Route::delete('/role-files/{roleFile}', [RoleFileController::class, 'destroy'])
            ->name('role-files.destroy');

        Route::post('/role-files/{roleFile}/rename', [RoleFileController::class, 'rename'])
            ->name('role-files.rename');

        Route::post('/role-files/{roleFile}/move', [RoleFileController::class, 'move'])
            ->name('role-files.move');

        Route::post('/role-files/{roleFile}/access', [RoleFileController::class, 'updateAccess'])
            ->name('role-files.access');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/files/{file}/download', [DocumentController::class, 'download'])
        ->name('files.download');

    Route::get(
        '/notifications',
        [NotificationController::class, 'index']
    )->name('notifications.index');

    Route::post(
        '/notifications/{notification}/read',
        [
            NotificationController::class,
            'read'
        ]
    )->name(
            'notifications.read'
        );

    Route::post(
        '/notifications/read-all',
        [
            NotificationController::class,
            'readAll'
        ]
    )->name(
            'notifications.read-all'
        );

    Route::middleware([
        'role:admin,editor,layouter,designer,isbn,owner,finance,superadmin'
    ])->group(function () {

        Route::get(
            '/layout-generator',
            [LayoutGeneratorController::class, 'index']
        )->name(
                'layout-generator.index'
            );

        Route::get(
            '/layout-generator/{book}',
            [LayoutGeneratorController::class, 'show']
        )->name(
                'layout-generator.show'
            );

        Route::post(
            '/layout-generator/{book}/sections',
            [LayoutGeneratorController::class, 'storeSection']
        )->name(
                'layout-generator.section.store'
            );

        Route::get(
            '/layout-generator/section/{section}/edit',
            [LayoutGeneratorController::class, 'editSection']
        )->name(
                'layout-generator.section.edit'
            );

        Route::put(
            '/layout-generator/section/{section}',
            [LayoutGeneratorController::class, 'updateSection']
        )->name(
                'layout-generator.section.update'
            );

        Route::delete(
            '/layout-generator/section/{section}',
            [LayoutGeneratorController::class, 'deleteSection']
        )->name(
                'layout-generator.section.delete'
            );

        Route::post(
            '/layout-generator/section/{section}/up',
            [LayoutGeneratorController::class, 'moveUp']
        )->name(
                'layout-generator.section.up'
            );

        Route::post(
            '/layout-generator/section/{section}/down',
            [LayoutGeneratorController::class, 'moveDown']
        )->name(
                'layout-generator.section.down'
            );

        Route::get(
            '/layout-generator/{book}/generate',
            [LayoutGeneratorController::class, 'generate']
        )->name(
                'layout-generator.generate'
            );

        Route::get(
            '/layout-generator/{book}/generate-template',
            [
                LayoutGeneratorController::class,
                'generateTemplate'
            ]
        )->name(
                'layout-generator.generate-template'
            );

        Route::get(
            '/layout-generator/{book}/preview',
            [
                LayoutGeneratorController::class,
                'preview'
            ]
        )->name(
                'layout-generator.preview'
            );

        Route::post(
            '/books/{book}/message',
            [BookMessageController::class, 'store']
        )->name(
                'books.message.store'
            );

        Route::prefix('books')->name('books.')->group(function () {
            // Resource routes (menangani index, create, store, show, edit, update, destroy)
            Route::get('/', [BookController::class, 'index'])->name('index');
            Route::get('/create', [BookController::class, 'create'])->name('create');
            Route::post('/', [BookController::class, 'store'])->name('store');
            Route::get('/{book}', [BookController::class, 'show'])->name('show');
            Route::get('/{book}/edit', [BookController::class, 'edit'])->name('edit');
            Route::put('/{book}', [BookController::class, 'update'])->name('update');
            Route::delete('/{book}', [BookController::class, 'destroy'])->name('destroy');
        });

        // FITUR PRODUKSI

        Route::post('/books/{book}/files', [BookFileController::class, 'store'])
            ->name('books.files.store');

        Route::post('/books/{book}/generate/title-page', [DocumentController::class, 'titlePage'])
            ->name('books.generate.title-page');

        Route::post('/books/{book}/generate/request-letter', [DocumentController::class, 'requestLetter'])
            ->name('books.generate.request-letter');

        Route::post('/books/{book}/generate/copyright', [DocumentController::class, 'copyright'])
            ->name('books.generate.copyright');

        Route::post('/books/{book}/generate/attachment', [DocumentController::class, 'attachment'])
            ->name('books.generate.attachment');

        Route::post('/books/{book}/audit', [DocumentController::class, 'audit'])
            ->name('books.audit');

        Route::post('/books/{book}/metadata-analyze', [DocumentController::class, 'analyzeMetadata'])
            ->name('books.metadata.analyze');

        Route::post('/books/{book}/manuscript-analyze', [DocumentController::class, 'analyzeManuscript'])
            ->name('books.manuscript.analyze');

        Route::post('/books/{book}/generate-all', [DocumentController::class, 'generateAll'])
            ->name('books.generate-all');

        Route::post('/files/{file}/restore', [BookFileController::class, 'restore'])
            ->name('files.restore');

        Route::post('/books/{book}/lock-metadata', [BookController::class, 'lockMetadata'])
            ->name('books.lock-metadata');

        Route::post('/books/{book}/next-workflow', [BookController::class, 'nextWorkflow'])
            ->name('books.next-workflow');

        Route::post('/books/{book}/sync-assignment', [BookController::class, 'syncAssignments'])
            ->name('books.sync-assignment');

        Route::post('/books/{book}/approve/{type}', [BookController::class, 'approve'])
            ->name('books.approve');

        // BATAS FITUR PRODUKSI

        Route::get('/assignments', [AssignmentController::class, 'index'])
            ->name('assignments.index');

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name(
                'dashboard'
            );

        Route::get(
            '/production',
            [
                ProductionDashboardController::class,
                'index'
            ]
        )->name(
                'production.dashboard'
            );

        Route::get(
            '/production/export',
            [
                ProductionDashboardController::class,
                'exportCsv'
            ]
        )->name(
                'production.dashboard.export'
            );

        Route::get(
            '/assignment-history',
            [
                AssignmentHistoryController::class,
                'index'
            ]
        )->name(
                'assignments.history'
            );

        Route::get('/isbn-queue', [ISBNQueueController::class, 'index'])
            ->name('isbn.queue');

        Route::resource('publishing-packages', PublishingPackageController::class)
            ->except(['show']);

        Route::post('/books/{book}/package-items/sync', [BookPackageItemController::class, 'sync'])->name('books.package-items.sync');
        Route::post('/book-package-items/{item}/toggle', [BookPackageItemController::class, 'toggle'])->name('book-package-items.toggle');

        Route::post('/books/{book}/generate-package', [DocumentController::class, 'generatePackage'])
            ->name('books.generate-package');

        Route::middleware(['role:admin,isbn,superadmin'])->group(function () {
            Route::post('/books/{book}/submit-isbn', [BookController::class, 'submitISBN'])
                ->name('books.submit-isbn');

            Route::post('/books/{book}/approve-isbn', [BookController::class, 'approveISBN'])
                ->name('books.approve-isbn');

            Route::post(

                '/books/{book}/auto-assign',

                [
                    BookController::class,
                    'autoAssign'
                ]

            )->name(
                    'books.auto-assign'
                );

            Route::resource(
                'users',
                UserController::class
            );

            Route::get('/book-claims', [AdminBookClaimController::class, 'index'])
                ->name('book-claims.index');

            Route::post('/book-claims/{claim}/approve', [AdminBookClaimController::class, 'approve'])
                ->name('book-claims.approve');

            Route::post('/book-claims/{claim}/reject', [AdminBookClaimController::class, 'reject'])
                ->name('book-claims.reject');

            Route::get('/admin/author-upgrades', [AdminAuthorUpgradeRequestController::class, 'index'])
                ->name('admin.author-upgrades.index');

            Route::get('/admin/author-upgrades/export', [AdminAuthorUpgradeRequestController::class, 'exportCsv'])
                ->name('admin.author-upgrades.export');

            Route::get('/admin/author-upgrades/{upgradeRequest}/attachment', [AdminAuthorUpgradeRequestController::class, 'downloadAttachment'])
                ->name('admin.author-upgrades.attachment');

            Route::get('/admin/author-upgrades/{upgradeRequest}/attachment/preview', [AdminAuthorUpgradeRequestController::class, 'previewAttachment'])
                ->name('admin.author-upgrades.attachment.preview');

            Route::post('/admin/author-upgrades/{upgradeRequest}/approve', [AdminAuthorUpgradeRequestController::class, 'approve'])
                ->name('admin.author-upgrades.approve');

            Route::post('/admin/author-upgrades/{upgradeRequest}/reject', [AdminAuthorUpgradeRequestController::class, 'reject'])
                ->name('admin.author-upgrades.reject');

            Route::get(
                '/reports/production',
                [
                    ProductionReportController::class,
                    'index'
                ]
            )->name(
                    'reports.production'
                );
        });

        Route::middleware(['role:admin,owner,finance,superadmin'])->group(function () {
            Route::get('/finance/invoices', [FinanceInvoiceController::class, 'index'])
                ->name('finance.invoices.index');

            Route::post('/finance/invoices/{invoice}/mark-paid', [FinanceInvoiceController::class, 'markPaid'])
                ->name('finance.invoices.mark-paid');

            Route::post('/finance/invoices/{invoice}/mark-pending', [FinanceInvoiceController::class, 'markPending'])
                ->name('finance.invoices.mark-pending');

            Route::post('/finance/books/{book}/create-final-invoice', [FinanceInvoiceController::class, 'createFinalInvoice'])
                ->name('finance.books.create-final-invoice');

            Route::post('/finance/books/{book}/delivery-links', [FinanceInvoiceController::class, 'updateBookLinks'])
                ->name('finance.books.delivery-links');

            Route::post('/finance/reminders/run', [FinanceInvoiceController::class, 'runReminders'])
                ->name('finance.reminders.run');

            Route::get('/print-prices', [AdminPrintPriceController::class, 'index'])
                ->name('print-prices.index');

            Route::post('/print-prices', [AdminPrintPriceController::class, 'store'])
                ->name('print-prices.store');

            Route::put('/print-prices/{printPrice}', [AdminPrintPriceController::class, 'update'])
                ->name('print-prices.update');

            Route::delete('/print-prices/{printPrice}', [AdminPrintPriceController::class, 'destroy'])
                ->name('print-prices.destroy');

            Route::get('/external-sales', [AdminExternalSalesController::class, 'index'])
                ->name('external-sales.index');

            Route::get('/finance/royalties', [AdminRoyaltyPayoutController::class, 'index'])
                ->name('finance.royalties.index');

            Route::post('/finance/royalties/{request}/approve', [AdminRoyaltyPayoutController::class, 'approve'])
                ->name('finance.royalties.approve');

            Route::post('/finance/royalties/{request}/pay', [AdminRoyaltyPayoutController::class, 'pay'])
                ->name('finance.royalties.pay');

            Route::post('/finance/royalties/{request}/reject', [AdminRoyaltyPayoutController::class, 'reject'])
                ->name('finance.royalties.reject');

            Route::get('/legacy-books', [AdminLegacyBookController::class, 'index'])
                ->name('legacy-books.index');

            Route::post('/legacy-books', [AdminLegacyBookController::class, 'store'])
                ->name('legacy-books.store');

            Route::put('/legacy-books/{legacyBook}', [AdminLegacyBookController::class, 'update'])
                ->name('legacy-books.update');

            Route::get('/finance/store/catalog', [AdminStoreCatalogController::class, 'index'])
                ->name('finance.store.catalog.index');

            Route::post('/finance/store/catalog', [AdminStoreCatalogController::class, 'store'])
                ->name('finance.store.catalog.store');

            Route::put('/finance/store/catalog/{item}', [AdminStoreCatalogController::class, 'update'])
                ->name('finance.store.catalog.update');

            Route::get('/finance/store/vouchers', [AdminStoreVoucherController::class, 'index'])
                ->name('finance.store.vouchers.index');

            Route::post('/finance/store/vouchers', [AdminStoreVoucherController::class, 'store'])
                ->name('finance.store.vouchers.store');

            Route::put('/finance/store/vouchers/{voucher}', [AdminStoreVoucherController::class, 'update'])
                ->name('finance.store.vouchers.update');

            Route::delete('/finance/store/vouchers/{voucher}', [AdminStoreVoucherController::class, 'destroy'])
                ->name('finance.store.vouchers.destroy');

            Route::get('/finance/store/orders', [AdminStoreOrderController::class, 'index'])
                ->name('finance.store.orders.index');

            Route::get('/finance/store/package-consultations', [AdminStorePackageConsultationController::class, 'index'])
                ->name('finance.store.package-consultations.index');

            Route::put('/finance/store/package-consultations/{consultation}/status', [AdminStorePackageConsultationController::class, 'updateStatus'])
                ->name('finance.store.package-consultations.update-status');

            Route::put('/finance/store/orders/{order}', [AdminStoreOrderController::class, 'update'])
                ->name('finance.store.orders.update');

            Route::post('/finance/store/orders/{order}/refund/approve', [AdminStoreOrderController::class, 'approveRefund'])
                ->name('finance.store.orders.refund.approve');

            Route::post('/finance/store/orders/{order}/refund/reject', [AdminStoreOrderController::class, 'rejectRefund'])
                ->name('finance.store.orders.refund.reject');

            Route::post('/external-sales', [AdminExternalSalesController::class, 'store'])
                ->name('external-sales.store');

            Route::post('/external-sales/update-book-price', [AdminExternalSalesController::class, 'updateBookPrice'])
                ->name('external-sales.update-book-price');

            Route::post('/external-sales/royalty-program', [AdminExternalSalesController::class, 'updateRoyaltyProgram'])
                ->name('external-sales.royalty-program');

            Route::get('/additional-services', [AdditionalServiceController::class, 'index'])
                ->name('additional-services.index');

            Route::post('/additional-services', [AdditionalServiceController::class, 'store'])
                ->name('additional-services.store');

            Route::put('/additional-services/{additionalService}', [AdditionalServiceController::class, 'update'])
                ->name('additional-services.update');

            Route::get('/finance/export/invoices', [AdminFinanceReportController::class, 'exportInvoicesCsv'])
                ->name('finance.export.invoices');

            Route::get('/finance/export/sales', [AdminFinanceReportController::class, 'exportSalesCsv'])
                ->name('finance.export.sales');

            Route::get('/finance/export/store-sales', [AdminFinanceReportController::class, 'exportStoreSalesCsv'])
                ->name('finance.export.store-sales');

            Route::get('/invoices/{invoice}/pdf', [InvoiceDocumentController::class, 'download'])
                ->name('invoices.pdf');

        });

        Route::middleware(['role:admin,owner,finance,designer,layouter,editor,superadmin'])->group(function () {
            Route::get('/printing/workspace', [PrintingWorkspaceController::class, 'index'])
                ->name('printing.workspace.index');

            Route::get('/printing/workspace/orders/{order}', [PrintingWorkspaceController::class, 'show'])
                ->name('printing.workspace.show');

            Route::post('/printing/workspace/orders/{order}/status', [PrintingWorkspaceController::class, 'updateStatus'])
                ->name('printing.workspace.update-status');

            Route::post('/printing/workspace/orders/{order}/request-revision', [PrintingWorkspaceController::class, 'requestRevision'])
                ->name('printing.workspace.request-revision');

            Route::post('/printing/workspace/orders/{order}/upload-final-file', [PrintingWorkspaceController::class, 'uploadFinalFile'])
                ->name('printing.workspace.upload-final-file');

            Route::get('/ebook/workspace', [EbookPublishingWorkspaceController::class, 'index'])
                ->name('ebook.workspace.index');

            Route::get('/ebook/workspace/orders/{order}', [EbookPublishingWorkspaceController::class, 'show'])
                ->name('ebook.workspace.show');

            Route::post('/ebook/workspace/orders/{order}/status', [EbookPublishingWorkspaceController::class, 'updateStatus'])
                ->name('ebook.workspace.update-status');

            Route::post('/ebook/workspace/orders/{order}/request-revision', [EbookPublishingWorkspaceController::class, 'requestRevision'])
                ->name('ebook.workspace.request-revision');
        });

        Route::get('/my-assignments', [AssignmentController::class, 'myAssignments'])
            ->name('assignments.my');

        Route::post('/books/{book}/finish', [BookController::class, 'finishBook'])
            ->name('books.finish');

        Route::post('/assignments/{assignment}/complete', [AssignmentController::class, 'complete'])
            ->name('assignments.complete');

        Route::get(
            '/production/timeline',
            [ProductionTimelineController::class, 'index']
        )->name('production.timeline');
    });

    Route::middleware([
        'auth',
        'role:customer,reader,author'
    ])->group(function () {
        Route::get('/customer', [CustomerDashboardController::class, 'index'])
            ->name('customer.dashboard');

        Route::get('/customer/ebooks', [CustomerEbookLibraryController::class, 'index'])
            ->name('customer.ebooks.index');

        Route::post('/customer/ebooks/{order}/open', [CustomerEbookLibraryController::class, 'open'])
            ->name('customer.ebooks.open');

        Route::get('/customer/orders', [CustomerOrderController::class, 'index'])
            ->name('customer.orders.index');

        Route::get('/customer/orders/{order}', [CustomerOrderController::class, 'show'])
            ->name('customer.orders.show');

        Route::post('/customer/orders/{order}/refund', [CustomerOrderController::class, 'requestRefund'])
            ->name('customer.orders.refund');
    });

    Route::middleware([
        'auth',
        'role:author'
    ])

        ->group(function () {

            Route::get(

                '/author',

                [
                    AuthorDashboardController::class,
                    'index'
                ]

            )->name(
                    'author.dashboard'
                );



            Route::post(

                '/books/{book}/author-approval',

                [
                    BookController::class,
                    'authorApproval'
                ]

            )->name(
                    'books.author-approval'
                );

            Route::post(
                '/books/{book}/review/approve',
                [
                    AuthorReviewController::class,
                    'approve'
                ]
            )->name(
                    'author.review.approve'
                );

            Route::post(
                '/books/{book}/review/revision',
                [
                    AuthorReviewController::class,
                    'revision'
                ]
            )->name(
                    'author.review.revision'
                );

            // ── Invoice author ────────────────────────────────────────────
            Route::get('/author/invoices', [AuthorInvoiceController::class, 'index'])
                ->name('author.invoices.index');

            Route::get('/author/invoices/{invoice}', [AuthorInvoiceController::class, 'show'])
                ->name('author.invoices.show');

            Route::post('/author/invoices/{invoice}/upload-proof', [AuthorInvoiceController::class, 'uploadProof'])
                ->name('author.invoices.upload-proof');

            Route::post('/author/invoices/{invoice}/pay-now', [AuthorInvoiceController::class, 'payNow'])
                ->name('author.invoices.pay-now');

            Route::get('/author/invoices/{invoice}/checkout-ipaymu', [PaymentGatewayController::class, 'checkout'])
                ->name('payments.ipaymu.checkout');

            Route::get('/author/orders', [AuthorOrderController::class, 'index'])
                ->name('author.orders.index');

            Route::post('/author/orders/buy-package', [AuthorOrderController::class, 'buyPackage'])
                ->name('author.orders.buy-package');

            Route::post('/author/orders/reprint', [AuthorOrderController::class, 'reorderPrint'])
                ->name('author.orders.reprint');

            Route::post('/author/orders/service', [AuthorOrderController::class, 'orderService'])
                ->name('author.orders.service');

            Route::get('/author/orders/cities', [AuthorOrderController::class, 'cities'])
                ->name('author.orders.cities');

            Route::get('/author/royalties', [AuthorRoyaltyController::class, 'index'])
                ->name('author.royalties.index');

            Route::get('/author/royalties/export', [AuthorRoyaltyController::class, 'export'])
                ->name('author.royalties.export');

            Route::post('/author/royalties/bank', [AuthorRoyaltyController::class, 'updateBank'])
                ->name('author.royalties.bank.update');

            Route::post('/author/royalties/payout', [AuthorRoyaltyController::class, 'requestPayout'])
                ->name('author.royalties.payout.request');

            Route::get('/author/royalties/{book}/documents/{type}', [AuthorRoyaltyController::class, 'downloadDocument'])
                ->name('author.royalties.document')
                ->where('type', 'agreement|contract');

            Route::post('/author/royalties/{book}/contract/accept', [AuthorRoyaltyController::class, 'acceptContract'])
                ->name('author.royalties.contract.accept');

            Route::post('/author/royalties/{book}/contract/reject', [AuthorRoyaltyController::class, 'rejectContract'])
                ->name('author.royalties.contract.reject');

            Route::get('/author/claims', [AuthorBookClaimController::class, 'index'])
                ->name('author.claims.index');

            Route::post('/author/claims/books/{book}', [AuthorBookClaimController::class, 'store'])
                ->name('author.claims.store');

            Route::get('/author/books/{book}/final-files', [AuthorFinalFileController::class, 'index'])
                ->name('author.books.final-files.index');

            Route::get('/author/books/{book}/final-files/{file}', [AuthorFinalFileController::class, 'download'])
                ->name('author.books.final-files.download');

        });
});

Route::get('/shared/role-files/{token}', [RoleFileController::class, 'shared'])
    ->name('role-files.shared');