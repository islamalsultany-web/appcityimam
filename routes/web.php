<?php

use App\Http\Controllers\AdminTwoFactorController;
use App\Http\Controllers\AppUserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InquiryAttachmentController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\MemberPermissionController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout-home', [AuthController::class, 'logoutHome'])->name('logout.home');
Route::get('/logout-home', [PortalController::class, 'index'])->name('logout.home.legacy');

Route::get('/', [PortalController::class, 'index'])->name('home');
Route::get('/index2', [PortalController::class, 'index'])->name('index2');

Route::get('/up', fn () => response('OK', 200, [
    'Content-Type' => 'text/plain; charset=UTF-8',
]))->name('health');
Route::get('/robots.txt', function () {
	return response("User-agent: *\nDisallow:\n", 200, [
		'Content-Type' => 'text/plain; charset=UTF-8',
	]);
})->name('robots');

Route::get('/sitemap.xml', function () {
	$xml = '<?xml version="1.0" encoding="UTF-8"?>'
		. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
		. '<url><loc>' . e(url('/index2')) . '</loc></url>'
		. '</urlset>';

	return response($xml, 200, [
		'Content-Type' => 'application/xml; charset=UTF-8',
	]);
})->name('sitemap');

Route::get('/decorative-image', function () {
	if (! file_exists(base_path('a.jpg'))) {
		abort(404);
	}

	return response()->file(base_path('a.jpg'));
})->name('decorative.image');

Route::redirect('/index2.html', '/index2');
Route::redirect('/users-index.html', '/users');
Route::redirect('/users-create.html', '/users/create');
Route::redirect('/users-excel.html', '/users/excel');

Route::middleware('app.auth')->group(function (): void {
	Route::get('/user/info', [AuthController::class, 'userInfo'])->name('user.info');
	Route::post('/user/password', [AuthController::class, 'updatePassword'])->name('user.password.update');
	Route::get('/user/credentials', [AuthController::class, 'showCredentialsSetup'])->name('user.credentials.setup');
	Route::post('/user/credentials', [AuthController::class, 'updateCredentials'])->name('user.credentials.update');
	Route::get('/user/two-factor/setup', [AdminTwoFactorController::class, 'showSetup'])->name('user.two-factor.setup');
	Route::post('/user/two-factor/confirm', [AdminTwoFactorController::class, 'confirmSetup'])->name('user.two-factor.confirm');
	Route::get('/user/two-factor/verify', [AdminTwoFactorController::class, 'showVerify'])->name('user.two-factor.verify');
	Route::post('/user/two-factor/verify', [AdminTwoFactorController::class, 'submitVerify'])->name('user.two-factor.verify.submit');

	Route::middleware('admin.two.factor')->group(function (): void {
	Route::middleware('secure.credentials')->group(function (): void {
		Route::get('/security/audit-logs', [AuditLogController::class, 'index'])
			->middleware('super.admin')
			->name('security.audit-logs');

		Route::get('/inquiries/{inquiry}/attachments/{field}', [InquiryAttachmentController::class, 'download'])
			->whereIn('field', ['attachment', 'response'])
			->name('inquiries.attachments.download');
		Route::get('/asker/dashboard', [InquiryController::class, 'askerIndex'])
			->middleware('permission:inquiries.asker.view')
			->name('dashboard.asker');

		Route::get('/asker/inquiries/create', [InquiryController::class, 'askerCreate'])
			->middleware('permission:inquiries.asker.create_page|inquiries.asker.create')
			->name('asker.inquiries.create');

		Route::get('/asker/dashboard/{inquiry}/view', [InquiryController::class, 'askerView'])
			->middleware('permission:inquiries.asker.view_details|inquiries.asker.view')
			->name('asker.inquiries.view');

		Route::get('/asker/dashboard/{inquiry}/print', [InquiryController::class, 'askerPrint'])
			->middleware('permission:inquiries.asker.print|inquiries.asker.view')
			->name('asker.inquiries.print');

		Route::post('/asker/inquiries', [InquiryController::class, 'storeFromAsker'])
			->middleware('permission:inquiries.asker.create')
			->name('asker.inquiries.store');

		Route::get('/responder/dashboard', [InquiryController::class, 'responderIndex'])
			->middleware('permission:inquiries.responder.view|inquiries.responder.manage')
			->name('dashboard.responder');

		Route::get('/responder/dashboard/deleted', [InquiryController::class, 'responderDeleted'])
			->middleware('permission:inquiries.responder.deleted|inquiries.responder.manage')
			->name('responder.inquiries.deleted');

		Route::post('/responder/dashboard/deleted/{inquiryId}/restore', [InquiryController::class, 'responderRestore'])
			->middleware('permission:inquiries.responder.restore|inquiries.responder.manage')
			->name('responder.inquiries.restore');

		Route::get('/responder/dashboard/report/print', [InquiryController::class, 'responderPrintReport'])
			->middleware('permission:inquiries.responder.report.print|inquiries.responder.manage')
			->name('responder.inquiries.report.print');

		Route::get('/responder/dashboard/{inquiry}/view', [InquiryController::class, 'responderView'])
			->middleware('permission:inquiries.responder.view_details|inquiries.responder.manage')
			->name('responder.inquiries.view');

		Route::get('/responder/dashboard/{inquiry}/print', [InquiryController::class, 'responderPrint'])
			->middleware('permission:inquiries.responder.print|inquiries.responder.manage')
			->name('responder.inquiries.print');

		Route::get('/responder/dashboard/{inquiry}', [InquiryController::class, 'responderShow'])
			->middleware('permission:inquiries.responder.show_answer_page|inquiries.responder.manage')
			->name('responder.inquiries.show');

		Route::patch('/responder/dashboard/{inquiry}', [InquiryController::class, 'responderAnswer'])
			->middleware('permission:inquiries.responder.answer')
			->name('responder.inquiries.answer');

		Route::delete('/responder/dashboard/{inquiry}', [InquiryController::class, 'responderDestroy'])
			->middleware('permission:inquiries.responder.delete|inquiries.responder.manage')
			->name('responder.inquiries.destroy');

		Route::get('/reviewer/dashboard', [InquiryController::class, 'reviewerIndex'])
			->middleware('permission:inquiries.reviewer.view|inquiries.reviewer.manage')
			->name('dashboard.reviewer');

		Route::get('/reviewer/dashboard/{inquiry}', [InquiryController::class, 'reviewerShow'])
			->middleware('permission:inquiries.reviewer.review_page|inquiries.reviewer.manage')
			->name('reviewer.inquiries.show');

		Route::patch('/reviewer/dashboard/{inquiry}', [InquiryController::class, 'reviewerReview'])
			->middleware('permission:inquiries.reviewer.review')
			->name('reviewer.inquiries.review');

		Route::delete('/users', [AppUserController::class, 'destroyAll'])
			->middleware(['permission:users.bulk_delete', 'throttle:3,1'])
			->name('users.destroyAll');

		Route::get('/users/excel', [AppUserController::class, 'excelPage'])
			->middleware('permission:users.excel.page')
			->name('users.excel');

		Route::get('/users/excel/template', [AppUserController::class, 'excelTemplate'])
			->middleware('permission:users.excel.template')
			->name('users.excel.template');

		Route::post('/users/excel/import', [AppUserController::class, 'excelImport'])
			->middleware(['permission:users.excel.import', 'throttle:5,1'])
			->name('users.excel.import');

		Route::get('/users/excel/export', [AppUserController::class, 'excelExport'])
			->middleware('permission:users.excel.export')
			->name('users.excel.export');

		Route::resource('users', AppUserController::class)->middleware([
			'index' => 'permission:users.index|users.view',
			'create' => 'permission:users.create',
			'store' => 'permission:users.store',
			'show' => 'permission:users.show',
			'edit' => 'permission:users.edit',
			'update' => 'permission:users.update',
			'destroy' => 'permission:users.delete',
		]);

		Route::get('/permissions/members', [MemberPermissionController::class, 'index'])
			->middleware('permission:permissions.members.view|permissions.members.edit')
			->name('permissions.members.index');

		Route::get('/permissions/members/create', [MemberPermissionController::class, 'create'])
			->middleware('permission:permissions.members.create')
			->name('permissions.members.create');

		Route::post('/permissions/members', [MemberPermissionController::class, 'store'])
			->middleware('permission:permissions.members.store')
			->name('permissions.members.store');

		Route::get('/permissions/members/{user}/edit', [MemberPermissionController::class, 'edit'])
			->middleware('permission:permissions.members.edit')
			->name('permissions.members.edit');

		Route::put('/permissions/members/{user}', [MemberPermissionController::class, 'update'])
			->middleware('permission:permissions.members.update')
			->name('permissions.members.update');
	});
	});
});
