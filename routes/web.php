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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductionTimelineController;
use App\Http\Controllers\BookMessageController;
use App\Http\Controllers\BookChapterController;
use App\Http\Controllers\LayoutGeneratorController;
use App\Http\Controllers\PublishingPackageController;
use App\Http\Controllers\BookPackageItemController;

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

    return redirect()
        ->route('dashboard');

});

require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {

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
        'role:admin,editor,layouter,isbn,owner'
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

        Route::get(
            '/layout-generator/{book}/generate-template',
            [LayoutGeneratorController::class, 'generateTemplate']
        )->name(
                'layout-generator.generate-template'
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

            // Custom routes
            Route::patch('/{book}/lock-metadata', [BookController::class, 'lockMetadata'])->name('lockMetadata');
            Route::patch('/{book}/next-workflow', [BookController::class, 'nextWorkflow'])->name('nextWorkflow');
            Route::post('/{book}/sync-assignments', [BookController::class, 'syncAssignments'])->name('syncAssignments');
            Route::post('/{book}/approve/{type}', [BookController::class, 'approve'])->name('approve');
            Route::post('/{book}/submit-isbn', [BookController::class, 'submitISBN'])->name('submitISBN');
            Route::patch('/{book}/approve-isbn', [BookController::class, 'approveISBN'])->name('approveISBN');
            Route::post('/{book}/finish', [BookController::class, 'finishBook'])->name('finishBook');
            Route::post('/{book}/author-approval', [BookController::class, 'authorApproval'])->name('authorApproval');
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

        Route::middleware(['role:admin,isbn'])->group(function () {
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

        });
});