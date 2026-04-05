<?php

use App\Http\Controllers\AppUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\MemberPermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout-home', [AuthController::class, 'logoutHome'])->name('logout.home');

Route::view('/index2', 'index2')->name('index2');
Route::redirect('/', '/index2')->name('home');

Route::middleware('app.auth')->group(function (): void {
	Route::get('/user/info', [AuthController::class, 'userInfo'])->name('user.info');
	Route::post('/user/password', [AuthController::class, 'updatePassword'])->name('user.password.update');

	Route::get('/asker/dashboard', [InquiryController::class, 'askerIndex'])->name('dashboard.asker');
	Route::get('/asker/inquiries/create', [InquiryController::class, 'askerCreate'])->name('asker.inquiries.create');
	Route::get('/asker/dashboard/{inquiry}/view', [InquiryController::class, 'askerView'])->name('asker.inquiries.view');
	Route::get('/asker/dashboard/{inquiry}/print', [InquiryController::class, 'askerPrint'])->name('asker.inquiries.print');

	Route::post('/asker/inquiries', [InquiryController::class, 'storeFromAsker'])->name('asker.inquiries.store');
	Route::get('/responder/dashboard', [InquiryController::class, 'responderIndex'])->name('dashboard.responder');
	Route::get('/responder/dashboard/deleted', [InquiryController::class, 'responderDeleted'])->name('responder.inquiries.deleted');
	Route::post('/responder/dashboard/deleted/{inquiryId}/restore', [InquiryController::class, 'responderRestore'])->name('responder.inquiries.restore');
	Route::get('/responder/dashboard/report/print', [InquiryController::class, 'responderPrintReport'])->name('responder.inquiries.report.print');
	Route::get('/responder/dashboard/{inquiry}/view', [InquiryController::class, 'responderView'])->name('responder.inquiries.view');
	Route::get('/responder/dashboard/{inquiry}/print', [InquiryController::class, 'responderPrint'])->name('responder.inquiries.print');
	Route::get('/responder/dashboard/{inquiry}', [InquiryController::class, 'responderShow'])->name('responder.inquiries.show');
	Route::patch('/responder/dashboard/{inquiry}', [InquiryController::class, 'responderAnswer'])->name('responder.inquiries.answer');
	Route::delete('/responder/dashboard/{inquiry}', [InquiryController::class, 'responderDestroy'])->name('responder.inquiries.destroy');

	Route::get('/reviewer/dashboard', [InquiryController::class, 'reviewerIndex'])->name('dashboard.reviewer');
	Route::get('/reviewer/dashboard/{inquiry}', [InquiryController::class, 'reviewerShow'])->name('reviewer.inquiries.show');
	Route::patch('/reviewer/dashboard/{inquiry}', [InquiryController::class, 'reviewerReview'])->name('reviewer.inquiries.review');

	Route::delete('/users', [AppUserController::class, 'destroyAll'])->name('users.destroyAll');
	Route::get('/users/excel', [AppUserController::class, 'excelPage'])->name('users.excel');
	Route::get('/users/excel/template', [AppUserController::class, 'excelTemplate'])->name('users.excel.template');
	Route::post('/users/excel/import', [AppUserController::class, 'excelImport'])->name('users.excel.import');
	Route::get('/users/excel/export', [AppUserController::class, 'excelExport'])->name('users.excel.export');
	Route::resource('users', AppUserController::class);

	Route::get('/permissions/members', [MemberPermissionController::class, 'index'])->name('permissions.members.index');
	Route::get('/permissions/members/create', [MemberPermissionController::class, 'create'])->name('permissions.members.create');
	Route::post('/permissions/members', [MemberPermissionController::class, 'store'])->name('permissions.members.store');
	Route::get('/permissions/members/{user}/edit', [MemberPermissionController::class, 'edit'])->name('permissions.members.edit');
	Route::put('/permissions/members/{user}', [MemberPermissionController::class, 'update'])->name('permissions.members.update');
});

