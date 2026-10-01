<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallationWizardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentHistoryController;
use App\Http\Controllers\Admin\StaffHistoryController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffAttendanceController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\AcademicController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\RoutineController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\GradeRuleController;
use App\Http\Controllers\Admin\MarkController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PayrollHistoryController;
use App\Http\Controllers\Admin\LibraryController;
use App\Http\Controllers\Admin\HostelController;
use App\Http\Controllers\Admin\TransportController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\Food\FoodDashboardController;
use App\Http\Controllers\Admin\Food\MealController;
use App\Http\Controllers\Admin\Food\FoodPlanController;
use App\Http\Controllers\Admin\Food\FoodAttendanceController;
use App\Http\Controllers\Admin\Food\FoodReportController;
use App\Http\Controllers\Admin\Food\FoodSettingController;
use App\Http\Controllers\Admin\Food\FoodAllocationController;
use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\Admin\FeeReportController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Student\StudentController as StudentStudentController;
use App\Http\Controllers\Parent\ParentController;
use App\Http\Controllers\Api\BiometricSyncController;
use App\Http\Controllers\Admin\BiometricDeviceController;
use App\Http\Controllers\Admin\DeviceSyncLogController;
use App\Http\Controllers\Api\AiAnalyticsController;

// System Commands
Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Application cache cleared successfully.';
});

Route::get('/storage-link', function() {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'Storage linked successfully.';
});

// Installation Wizard
Route::get('/install', [InstallationWizardController::class, 'showWizard'])->name('install.wizard');
Route::post('/install', [InstallationWizardController::class, 'install'])->name('install.run');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:5,10')
    ->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Frontend Routes (Attendance Software Redirect)
Route::get('/', function() {
    return auth()->check()
        ? redirect()->route('admin.attendance-suite.dashboard')
        : redirect()->route('login');
})->name('home');

// Authenticated Redirects (for users hitting / after login)
Route::get('/dashboard/redirect', [AuthController::class, 'dashboardRedirect'])->name('dashboard.redirect');

// Authenticated Routes
Route::middleware(['auth', 'log_activity'])->group(function () {

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Admin Group
    Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\CheckAdminAccess::class])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/clear-cache', [MaintenanceController::class, 'clearCache'])->name('clear.cache');
        
        // Shifts / Time Settings
        Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class)->except(['create', 'show', 'edit']);

        // Students
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/history', [StudentHistoryController::class, 'index'])->name('students.history.index');
        Route::get('/students/{id}/history', [StudentHistoryController::class, 'show'])->name('students.history.show');
        Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
        
        // Student Promotion System
        Route::get('/students-promotion', [\App\Http\Controllers\Admin\StudentPromotionController::class, 'index'])->name('students.promotion.index');
        Route::post('/students-promotion', [\App\Http\Controllers\Admin\StudentPromotionController::class, 'store'])->name('students.promotion.store');
        Route::post('/students-promotion/rollback/{log}', [\App\Http\Controllers\Admin\StudentPromotionController::class, 'rollback'])->name('students.promotion.rollback');

        // Admission Applications
        Route::get('/admissions', [\App\Http\Controllers\Admin\AdmissionApplicationController::class, 'index'])->name('admissions.index');
        Route::get('/admissions/{id}', [\App\Http\Controllers\Admin\AdmissionApplicationController::class, 'show'])->name('admissions.show');
        Route::post('/admissions/{id}/merge', [\App\Http\Controllers\Admin\AdmissionApplicationController::class, 'merge'])->name('admissions.merge');

        // Staff History
        Route::get('/staff/history', [StaffHistoryController::class, 'index'])->name('staff.history.index');
        Route::get('/staff/{id}/history', [StaffHistoryController::class, 'show'])->name('staff.history.show');

        // Staff
        Route::resource('staff', StaffController::class);

        // Staff Attendance
        Route::get('/staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
        Route::get('/staff-attendance/calendar/{staffId}', [StaffAttendanceController::class, 'calendar'])->name('staff-attendance.calendar');
        Route::get('/staff-attendance/day/{staffId}/{date}', [StaffAttendanceController::class, 'getDay'])->name('staff-attendance.get-day');
        Route::post('/staff-attendance/day', [StaffAttendanceController::class, 'saveDay'])->name('staff-attendance.save-day');
        Route::get('/staff-attendance/payroll-sidebar/{staffId}', [StaffAttendanceController::class, 'payrollSidebar'])->name('staff-attendance.payroll-sidebar');

        // Global Holidays
        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays/toggle', [HolidayController::class, 'toggle'])->name('holidays.toggle');
        Route::post('/holidays/sync', [HolidayController::class, 'syncGoogle'])->name('holidays.sync');

        // Academics (Sessions, Classes, Sections, Subjects, Routines)
        Route::get('/class', [AcademicController::class, 'index'])->name('academics.index');
        Route::post('/academics/sessions', [AcademicController::class, 'storeSession'])->name('academics.sessions.store');
        Route::post('/academics/settings', [AcademicController::class, 'updateSettings'])->name('academics.settings.update');
        Route::post('/academics/classes', [AcademicController::class, 'storeClass'])->name('academics.classes.store');
        Route::delete('/academics/classes/{id}', [AcademicController::class, 'destroyClass'])->name('academics.classes.destroy');
        Route::post('/academics/sections', [AcademicController::class, 'storeSection'])->name('academics.sections.store');
        Route::post('/academics/subjects', [AcademicController::class, 'storeSubject'])->name('academics.subjects.store');
        Route::post('/academics/timetable', [AcademicController::class, 'storeTimetable'])->name('academics.timetable.store');

        // Shifts
        Route::resource('shifts', ShiftController::class)->except(['create', 'show', 'edit']);

        // Subjects
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        // Class Routines
        Route::get('/teacher-timetable', [RoutineController::class, 'teacherTimetable'])->name('routines.teacher');
        Route::get('/routines', [RoutineController::class, 'index'])->name('routines.index');
        Route::get('/routines/create', [RoutineController::class, 'create'])->name('routines.create');
        Route::post('/routines', [RoutineController::class, 'store'])->name('routines.store');
        Route::get('/routines/{id}/edit', [RoutineController::class, 'edit'])->name('routines.edit');
        Route::put('/routines/{id}', [RoutineController::class, 'update'])->name('routines.update');
        Route::delete('/routines/{id}', [RoutineController::class, 'destroy'])->name('routines.destroy');

        // Classrooms
        Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
        Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::put('/classrooms/{id}', [ClassroomController::class, 'update'])->name('classrooms.update');
        Route::delete('/classrooms/{id}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');

        // Exams
        Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
        Route::post('/exams/types', [ExamController::class, 'storeExamType'])->name('exams.types.store');
        Route::put('/exams/types/{id}', [ExamController::class, 'updateExamType'])->name('exams.types.update');
        Route::post('/exams/schedules', [ExamController::class, 'storeSchedule'])->name('exams.schedules.store');
        Route::delete('/exams/types/{id}', [ExamController::class, 'destroyExamType'])->name('exams.types.destroy');
        Route::post('/exams/grade-rules', [GradeRuleController::class, 'store'])->name('exams.grade-rules.store');
        Route::delete('/exams/grade-rules/{gradeRule}', [GradeRuleController::class, 'destroy'])->name('exams.grade-rules.destroy');

        // Marks
        Route::get('/marks', [MarkController::class, 'index'])->name('marks.index');
        Route::post('/marks/store', [MarkController::class, 'storeMarks'])->name('marks.store');

        // Results
        Route::get('/results', [ResultController::class, 'index'])->name('results.index');

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
        Route::get('/attendance/biometric-logs', [AttendanceController::class, 'biometricLogs'])->name('attendance.biometric-logs');
        Route::post('/attendance/biometric-logs/clear', [AttendanceController::class, 'clearBiometricLogs'])->name('attendance.biometric-logs.clear');
        
        // SMS Configuration
        Route::get('/sms-configuration', [\App\Http\Controllers\SmsSettingController::class, 'index'])->name('sms-configuration.index');
        Route::post('/sms-configuration', [\App\Http\Controllers\SmsSettingController::class, 'update'])->name('sms-configuration.update');
        
        // Payment Configuration
        Route::get('/settings/payment', [\App\Http\Controllers\Admin\SettingsController::class, 'paymentConfig'])->name('settings.payment');
        Route::post('/settings/payment', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePaymentConfig'])->name('settings.payment.update');
        
        // Custom SMS Send
        Route::get('/sms/custom', [\App\Http\Controllers\SmsSettingController::class, 'customSmsForm'])->name('sms.custom.form');
        Route::post('/sms/custom', [\App\Http\Controllers\SmsSettingController::class, 'sendCustomSms'])->name('sms.custom.send');
        Route::get('/sms/search-student', [\App\Http\Controllers\SmsSettingController::class, 'searchStudent'])->name('sms.search-student');

        Route::post('/attendance/biometric-direct-sync', [AttendanceController::class, 'directZkSync'])->name('attendance.biometric-direct-sync');
        Route::get('/attendance/live-monitor', [AttendanceController::class, 'liveMonitor'])->name('attendance.live-monitor');
        Route::get('/attendance/live-feed', [AttendanceController::class, 'liveFeed'])->name('attendance.live-feed');
        Route::get('/attendance/calendar/{studentId}', [AttendanceController::class, 'calendar'])->name('attendance.calendar');
        Route::get('/attendance/day/{studentId}/{date}', [AttendanceController::class, 'getDay'])->name('attendance.get-day');
        Route::post('/attendance/day', [AttendanceController::class, 'saveDay'])->name('attendance.save-day');
        Route::post('/attendance/bulk-save', [AttendanceController::class, 'bulkSave'])->name('attendance.bulk-save');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        Route::get('/attendance/student/{id}/history', [AttendanceController::class, 'studentHistory'])->name('attendance.student.history');

        Route::get('/attendance/unmapped', [AttendanceController::class, 'unmapped'])->name('admin.attendance.unmapped');
        Route::post('/attendance/map-user', [AttendanceController::class, 'mapBiometricUser'])->name('admin.attendance.map-user');

        // Biometric Devices Management
        Route::get('/device-sync-logs', [DeviceSyncLogController::class, 'index'])->name('admin.device-sync-logs');
        Route::post('/biometric-devices/sync-all', [BiometricDeviceController::class, 'syncAll'])->name('biometric-devices.sync-all');
        Route::post('/biometric-devices/{device}/clear-logs', [BiometricDeviceController::class, 'clearDeviceLogs'])->name('biometric-devices.clear-logs');
        Route::post('/biometric-devices/{device}/test', [BiometricDeviceController::class, 'testConnection'])->name('biometric-devices.test');
        Route::post('/biometric-devices/{device}/sync', [BiometricDeviceController::class, 'sync'])->name('biometric-devices.sync');
        Route::resource('biometric-devices', BiometricDeviceController::class)->parameters(['biometric-devices' => 'device'])->except(['create', 'edit'])->names('biometric-devices');

        // Fees
        Route::get('/fees/setup', [FeeController::class, 'setup'])->name('fees.setup');
        Route::get('/fees/collection', [FeeController::class, 'collection'])->name('fees.collection');
        Route::get('/fees/search-students', [FeeController::class, 'searchStudents'])->name('fees.search-students');
        Route::get('/fees/due-report', [FeeController::class, 'dueReport'])->name('fees.due-report');

        Route::post('/fees/categories', [FeeController::class, 'storeCategory'])->name('fees.categories.store');
        Route::get('/fees/categories/{id}/edit', [FeeController::class, 'editCategory'])->name('fees.categories.edit');
        Route::put('/fees/categories/{id}', [FeeController::class, 'updateCategory'])->name('fees.categories.update');
        Route::delete('/fees/categories/{id}', [FeeController::class, 'destroyCategory'])->name('fees.categories.destroy');
        Route::post('/fees/categories/{id}/amounts', [FeeController::class, 'updateAmounts'])->name('fees.categories.update_amounts');
        Route::post('/fees/categories/{id}/installments/generate', [FeeController::class, 'generateInstallments'])->name('fees.installments.generate');
        Route::put('/fees/installments/{id}', [FeeController::class, 'updateInstallment'])->name('fees.installments.update');
        Route::delete('/fees/installments/{id}', [FeeController::class, 'destroyInstallment'])->name('fees.installments.destroy');
        Route::post('/fees/installments/{id}/toggle', [FeeController::class, 'toggleInstallment'])->name('fees.installments.toggle');
        Route::post('/fees/structures', [FeeController::class, 'storeStructure'])->name('fees.structures.store');
        Route::post('/fees/invoices', [FeeController::class, 'generateInvoices'])->name('fees.invoices.generate');
        Route::get('/fees/invoices/{id}', [FeeController::class, 'showInvoice'])->name('fees.invoices.show');
        Route::post('/fees/invoices/{id}/pay', [FeeController::class, 'collectPayment'])->name('fees.invoices.pay');
        Route::delete('/fees/payments/{id}', [FeeController::class, 'destroyPayment'])->name('fees.payments.destroy');
        Route::post('/fees/direct-collect', [FeeController::class, 'directCollect'])->name('fees.direct_collect');
        Route::post('/fees/invoices/{id}/refund-advance', [FeeController::class, 'refundAdvance'])->name('fees.advance.refund');
        Route::get('/fees/invoices/{id}/receipt', [FeeController::class, 'printReceipt'])->name('fees.invoices.receipt');

        // Accounts
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::post('/accounts/transactions', [AccountController::class, 'storeTransaction'])->name('accounts.transactions.store');
        Route::post('/accounts/ledgers', [AccountController::class, 'storeLedger'])->name('accounts.ledgers.store');

        // Payroll
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/settings', [PayrollController::class, 'settings'])->name('payroll.settings');
        Route::post('/payroll/settings', [PayrollController::class, 'saveSettings'])->name('payroll.settings.save');
        Route::post('/payroll/bulk-generate', [PayrollController::class, 'bulkGenerate'])->name('payroll.bulk-generate');
        Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
        Route::get('/payroll/employee/{staffId}', [PayrollController::class, 'show'])->name('payroll.employee.show');
        Route::post('/payroll/salary-structure', [PayrollController::class, 'updateSalaryStructure'])->name('payroll.salary-structure.update');
        Route::post('/payroll/advance-salary', [PayrollController::class, 'takeAdvanceSalary'])->name('payroll.advance-salary.store');
        Route::post('/payroll/payment', [PayrollController::class, 'makePayment'])->name('payroll.payment.store');
        Route::get('/payroll/slip/{salaryId}', [PayrollController::class, 'generateSlip'])->name('payroll.slip');
        Route::post('/payroll/recalculate/{salaryId}', [PayrollController::class, 'recalculate'])->name('payroll.recalculate');
        Route::post('/payroll/lock/{salaryId}', [PayrollController::class, 'lock'])->name('payroll.lock');
        Route::post('/payroll/unlock/{salaryId}', [PayrollController::class, 'unlock'])->name('payroll.unlock');
        Route::post('/payroll/{id}/pay', [PayrollController::class, 'pay'])->name('payroll.pay');
        Route::get('/payroll/history', [PayrollHistoryController::class, 'index'])->name('payroll.history.index');
        Route::get('/payroll/history/{id}', [PayrollHistoryController::class, 'show'])->name('payroll.history.show');

        // Library
        Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
        Route::get('/library/books', [LibraryController::class, 'books'])->name('library.books');
        Route::get('/library/issues', [LibraryController::class, 'issues'])->name('library.issues');
        Route::post('/library/books', [LibraryController::class, 'storeBook'])->name('library.books.store');
        Route::post('/library/issue', [LibraryController::class, 'issueBook'])->name('library.issue.store');
        Route::post('/library/return/{id}', [LibraryController::class, 'returnBook'])->name('library.return');

        // Finance & Donations Module
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\Finance\DashboardController::class, 'index'])->name('dashboard');

            Route::get('/expenses', [\App\Http\Controllers\Admin\Finance\ExpenseController::class, 'index'])->name('expenses.index');
            Route::get('/expenses/create', [\App\Http\Controllers\Admin\Finance\ExpenseController::class, 'create'])->name('expenses.create');
            Route::post('/expenses', [\App\Http\Controllers\Admin\Finance\ExpenseController::class, 'store'])->name('expenses.store');
            Route::delete('/expenses/{id}', [\App\Http\Controllers\Admin\Finance\ExpenseController::class, 'destroy'])->name('expenses.destroy');
            Route::get('/expenses/categories', [\App\Http\Controllers\Admin\Finance\ExpenseController::class, 'categories'])->name('expenses.categories');
            Route::post('/expenses/categories', [\App\Http\Controllers\Admin\Finance\ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
            Route::delete('/expenses/categories/{id}', [\App\Http\Controllers\Admin\Finance\ExpenseController::class, 'destroyCategory'])->name('expenses.categories.destroy');

            Route::get('/donations', [\App\Http\Controllers\Admin\Finance\DonationController::class, 'index'])->name('donations.index');
            Route::post('/donations', [\App\Http\Controllers\Admin\Finance\DonationController::class, 'store'])->name('donations.store');
            Route::delete('/donations/{id}', [\App\Http\Controllers\Admin\Finance\DonationController::class, 'destroy'])->name('donations.destroy');
            Route::get('/donations/campaigns', [\App\Http\Controllers\Admin\Finance\DonationController::class, 'campaigns'])->name('donations.campaigns');
            Route::post('/donations/campaigns', [\App\Http\Controllers\Admin\Finance\DonationController::class, 'storeCampaign'])->name('donations.campaigns.store');
            Route::put('/donations/campaigns/{id}', [\App\Http\Controllers\Admin\Finance\DonationController::class, 'updateCampaign'])->name('donations.campaigns.update');
            Route::delete('/donations/campaigns/{id}', [\App\Http\Controllers\Admin\Finance\DonationController::class, 'destroyCampaign'])->name('donations.campaigns.destroy');

            Route::get('/reports/expenses', [\App\Http\Controllers\Admin\Finance\ReportController::class, 'expenses'])->name('reports.expenses');
            Route::get('/reports/donations', [\App\Http\Controllers\Admin\Finance\ReportController::class, 'donations'])->name('reports.donations');
        });

        // Advanced Inventory Module
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index'); 
            Route::get('/suppliers', [\App\Http\Controllers\Admin\InventoryController::class, 'suppliers'])->name('suppliers.index');
            Route::post('/suppliers', [\App\Http\Controllers\Admin\InventoryController::class, 'storeSupplier'])->name('suppliers.store');
            Route::post('/items', [\App\Http\Controllers\Admin\InventoryController::class, 'storeItem'])->name('items.store');
            Route::get('/purchases', [\App\Http\Controllers\Admin\Inventory\PurchaseController::class, 'index'])->name('purchases.index');
            Route::post('/purchases', [\App\Http\Controllers\Admin\Inventory\PurchaseController::class, 'store'])->name('purchases.store');

            Route::get('/stock', [\App\Http\Controllers\Admin\Inventory\StockController::class, 'index'])->name('stock.index');
            Route::post('/stock', [\App\Http\Controllers\Admin\Inventory\StockController::class, 'store'])->name('stock.store');
        });

        // Hostel Module (Halls/Houses, Rooms, Beds, Allocations, Hostel Report & AJAX)
        Route::get('/hostel', [HostelController::class, 'hallsIndex'])->name('hostel.index');
        Route::get('/hostel/halls', [HostelController::class, 'hallsIndex'])->name('hostel.halls.index');
        Route::post('/hostel/halls', [HostelController::class, 'hallStore'])->name('hostel.halls.store');
        Route::put('/hostel/halls/{id}', [HostelController::class, 'hallUpdate'])->name('hostel.halls.update');
        Route::delete('/hostel/halls/{id}', [HostelController::class, 'hallDestroy'])->name('hostel.halls.destroy');

        Route::get('/hostel/rooms', [HostelController::class, 'roomsIndex'])->name('hostel.rooms.index');
        Route::post('/hostel/rooms', [HostelController::class, 'roomStore'])->name('hostel.rooms.store');
        Route::put('/hostel/rooms/{id}', [HostelController::class, 'roomUpdate'])->name('hostel.rooms.update');
        Route::delete('/hostel/rooms/{id}', [HostelController::class, 'roomDestroy'])->name('hostel.rooms.destroy');

        Route::get('/hostel/beds', [HostelController::class, 'bedsIndex'])->name('hostel.beds.index');
        Route::post('/hostel/beds', [HostelController::class, 'bedStore'])->name('hostel.beds.store');
        Route::put('/hostel/beds/{id}', [HostelController::class, 'bedUpdate'])->name('hostel.beds.update');
        Route::delete('/hostel/beds/{id}', [HostelController::class, 'bedDestroy'])->name('hostel.beds.destroy');

        Route::get('/hostel/allocations', [HostelController::class, 'allocationsIndex'])->name('hostel.allocations.index');
        Route::post('/hostel/allocations', [HostelController::class, 'allocateBed'])->name('hostel.allocate');
        Route::put('/hostel/allocations/{id}', [HostelController::class, 'updateAllocation'])->name('hostel.allocation.update');
        Route::delete('/hostel/allocations/{id}', [HostelController::class, 'deallocateBed'])->name('hostel.deallocate');

        Route::get('/hostel/report', [HostelController::class, 'report'])->name('hostel.report');
        Route::get('/hostel/available-rooms/{hostelId}', [HostelController::class, 'getAvailableRooms'])->name('hostel.available-rooms');
        Route::get('/hostel/available-beds/{roomId}', [HostelController::class, 'getAvailableBeds'])->name('hostel.available-beds');

        // Transport Routes
        Route::prefix('transport')->name('transport.')->group(function () {
            Route::get('/', [TransportController::class, 'dashboard'])->name('dashboard');

            // Route Management
            Route::get('/routes', [TransportController::class, 'routes'])->name('routes');
            Route::post('/routes', [TransportController::class, 'storeRoute'])->name('routes.store');
            Route::put('/routes/{id}', [TransportController::class, 'updateRoute'])->name('routes.update');
            Route::delete('/routes/{id}', [TransportController::class, 'destroyRoute'])->name('routes.destroy');

            // Stop Management
            Route::get('/routes/{route_id}/stops', [TransportController::class, 'stops'])->name('routes.stops');
            Route::post('/routes/{route_id}/stops', [TransportController::class, 'storeStop'])->name('routes.stops.store');
            Route::put('/stops/{id}', [TransportController::class, 'updateStop'])->name('stops.update');
            Route::delete('/stops/{id}', [TransportController::class, 'destroyStop'])->name('stops.destroy');

            // API endpoints for UI
            Route::get('/route-stops/{route_id}', [TransportController::class, 'getRouteStops'])->name('api.route-stops');

            // Allocations (List & History)
            Route::get('/allocations', [TransportController::class, 'allocations'])->name('allocations');
            Route::post('/allocations', [TransportController::class, 'allocateRoute'])->name('allocate');
            Route::put('/allocations/{id}', [TransportController::class, 'updateAllocation'])->name('allocations.update');
            Route::post('/allocations/{id}/release', [TransportController::class, 'releaseAllocation'])->name('allocations.release');
            Route::get('/history', [TransportController::class, 'history'])->name('history');
        });

        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/purchases', [InventoryController::class, 'storePurchase'])->name('inventory.purchases.store');

        // Noticeboard & Events
        Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
        Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');
        Route::post('/events', [NoticeController::class, 'storeEvent'])->name('events.store');

        // Certificates & IDs
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::post('/certificates', [CertificateController::class, 'storeTemplate'])->name('certificates.store');
        Route::post('/certificates/issue', [CertificateController::class, 'issueCertificate'])->name('certificates.issue');

        // Roles & Permissions
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::post('roles/permissions', [RoleController::class, 'storePermission'])->name('roles.permissions.store');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');

        // CMS / Website Settings
        Route::prefix('cms')->name('cms.')->group(function () {
            Route::get('/home', [CMSController::class, 'home'])->name('home');
            Route::post('/home', [CMSController::class, 'updateHome'])->name('home.update');

            Route::get('/about', [CMSController::class, 'about'])->name('about');
            Route::post('/about', [CMSController::class, 'updateAbout'])->name('about.update');

            Route::get('/principal-message', [CMSController::class, 'principalMessage'])->name('principal-message');
            Route::post('/principal-message', [CMSController::class, 'updatePrincipalMessage'])->name('principal-message.update');

            Route::get('/academics', [CMSController::class, 'academics'])->name('academics');
            Route::post('/academics', [CMSController::class, 'updateAcademics'])->name('academics.update');

            Route::get('/departments', [CMSController::class, 'departments'])->name('departments');
            Route::post('/departments', [CMSController::class, 'updateDepartments'])->name('departments.update');

            Route::get('/calendar', [CMSController::class, 'calendar'])->name('calendar');
            Route::post('/calendar', [CMSController::class, 'updateCalendar'])->name('calendar.update');

            Route::get('/campus', [CMSController::class, 'campus'])->name('campus');
            Route::post('/campus', [CMSController::class, 'updateCampus'])->name('campus.update');

            Route::get('/gallery', [CMSController::class, 'gallery'])->name('gallery');
            Route::post('/gallery', [CMSController::class, 'updateGallery'])->name('gallery.update');

            Route::get('/contact', [CMSController::class, 'contact'])->name('contact');
            Route::post('/contact', [CMSController::class, 'updateContact'])->name('contact.update');
            Route::get('/contact-messages', [CMSController::class, 'contactMessages'])->name('contact.messages');
            Route::get('/contact-messages/{id}', [CMSController::class, 'showContactMessage'])->name('contact.messages.show');
            Route::delete('/contact-messages/{id}', [CMSController::class, 'destroyContactMessage'])->name('contact.messages.destroy');

            Route::get('/footer', [CMSController::class, 'footer'])->name('footer');
            Route::post('/footer', [CMSController::class, 'updateFooter'])->name('footer.update');
        });

        // Food / Meal Management System
        Route::prefix('food')->name('food.')->group(function () {
            Route::get('/dashboard', [FoodDashboardController::class, 'index'])->name('dashboard');
            Route::resource('meals', MealController::class)->except(['create', 'edit', 'show']);
            Route::resource('plans', FoodPlanController::class)->except(['create', 'edit', 'show']);

            Route::get('/attendance', [FoodAttendanceController::class, 'index'])->name('attendance.index');
            Route::post('/attendance/mark', [FoodAttendanceController::class, 'mark'])->name('attendance.mark');
            Route::post('/attendance/mark-all', [FoodAttendanceController::class, 'markAll'])->name('attendance.mark-all');

            Route::get('/reports', [FoodReportController::class, 'index'])->name('reports.index');

            Route::get('/settings', [FoodSettingController::class, 'index'])->name('settings.index');
            Route::get('/settings/manual', [FoodSettingController::class, 'downloadManual'])->name('settings.manual');
            Route::post('/settings', [FoodSettingController::class, 'update'])->name('settings.update');

            Route::get('/allocations', [FoodAllocationController::class, 'index'])->name('allocations.index');
            Route::post('/allocations/allocate', [FoodAllocationController::class, 'allocate'])->name('allocations.allocate');
            Route::put('/allocations/{id}', [FoodAllocationController::class, 'update'])->name('allocations.update');
            Route::post('/allocations/{id}/release', [FoodAllocationController::class, 'release'])->name('allocations.release');
        });
        
        // Homework & Study Materials for Admin
        Route::get('homework', [\App\Http\Controllers\Admin\HomeworkController::class, 'index'])->name('homework.index');
        Route::delete('homework/{homework}', [\App\Http\Controllers\Admin\HomeworkController::class, 'destroy'])->name('homework.destroy');
        Route::get('study-materials', [\App\Http\Controllers\Admin\StudyMaterialController::class, 'index'])->name('study-materials.index');
        Route::delete('study-materials/{studyMaterial}', [\App\Http\Controllers\Admin\StudyMaterialController::class, 'destroy'])->name('study-materials.destroy');

        // Leave Management
        Route::resource('leave-types', \App\Http\Controllers\Admin\LeaveTypeController::class)->except(['create', 'show', 'edit']);
        Route::resource('leave-applications', \App\Http\Controllers\Admin\LeaveApplicationController::class)->only(['index', 'update']);

        // Reports
        Route::get('/reports/finance', [\App\Http\Controllers\Admin\ReportController::class, 'financeReport'])->name('reports.finance');
        Route::get('/reports/attendance', [\App\Http\Controllers\Admin\ReportController::class, 'attendanceReport'])->name('reports.attendance');
        
        // Fee Reports Submenu Routes
        Route::get('/reports/fees/academic', [FeeReportController::class, 'academicFeeReport'])->name('reports.fees.academic');
        Route::get('/reports/fees/hostel', [FeeReportController::class, 'hostelFeeReport'])->name('reports.fees.hostel');
        Route::get('/reports/fees/food', [FeeReportController::class, 'foodFeeReport'])->name('reports.fees.food');
        Route::get('/reports/fees/transport', [FeeReportController::class, 'transportFeeReport'])->name('reports.fees.transport');
    });

    // Teacher Group
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/timetable', [TeacherController::class, 'timetable'])->name('timetable');
        Route::get('/attendance', [TeacherController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [TeacherController::class, 'storeAttendance'])->name('attendance.store');
        Route::get('/marks', [TeacherController::class, 'marks'])->name('marks');
        Route::post('/marks', [TeacherController::class, 'storeMarks'])->name('marks.store');
        
        // Homework & Study Materials
        Route::resource('homework', \App\Http\Controllers\Teacher\HomeworkController::class);
        Route::get('homework/{homework}/submissions', [\App\Http\Controllers\Teacher\HomeworkController::class, 'submissions'])->name('homework.submissions');
        Route::post('homework/submissions/{submission}/evaluate', [\App\Http\Controllers\Teacher\HomeworkController::class, 'evaluate'])->name('homework.evaluate');
        Route::resource('study-materials', \App\Http\Controllers\Teacher\StudyMaterialController::class);
        
        // Leaves
        Route::resource('leaves', \App\Http\Controllers\Teacher\LeaveController::class)->only(['index', 'create', 'store']);
        Route::resource('student-leaves', \App\Http\Controllers\Teacher\StudentLeaveController::class)->only(['index', 'update']);
    });

    // Staff Group (For teachers, accountants, librarians, hr, receptionist, generic staff)
    Route::prefix('staff')->name('staff.')->middleware('role:teacher,accountant,librarian,hr,receptionist,staff')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Staff\StaffDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendance', [\App\Http\Controllers\Staff\StaffDashboardController::class, 'attendance'])->name('attendance');
        Route::get('/salary', [\App\Http\Controllers\Staff\StaffDashboardController::class, 'salary'])->name('salary');
    });

    // Student Group
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentStudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/routine', [StudentStudentController::class, 'routine'])->name('routine');
        Route::get('/attendance', [StudentStudentController::class, 'attendance'])->name('attendance');
        Route::get('/fees', [StudentStudentController::class, 'fees'])->name('fees');
        Route::get('/fees/pay/{id}', [StudentStudentController::class, 'showPaymentScreen'])->name('fees.pay');
        Route::post('/fees/pay/{id}', [StudentStudentController::class, 'processPayment'])->name('fees.pay.process');
        Route::post('/fees/pay/{id}/success', [StudentStudentController::class, 'sslSuccess'])->name('fees.pay.success');
        Route::post('/fees/pay/{id}/fail', [StudentStudentController::class, 'sslFail'])->name('fees.pay.fail');
        Route::post('/fees/pay/{id}/cancel', [StudentStudentController::class, 'sslCancel'])->name('fees.pay.cancel');
        Route::get('/results', [StudentStudentController::class, 'results'])->name('results');
        Route::get('/transport', [StudentStudentController::class, 'transport'])->name('transport');
        Route::get('/hostel', [StudentStudentController::class, 'hostel'])->name('hostel');
        Route::get('/food', [StudentStudentController::class, 'food'])->name('food');
        Route::get('/books', [StudentStudentController::class, 'books'])->name('books');
        
        // Homework & Study Materials
        Route::get('/homework', [\App\Http\Controllers\Student\HomeworkController::class, 'index'])->name('homework.index');
        Route::get('/homework/{homework}', [\App\Http\Controllers\Student\HomeworkController::class, 'show'])->name('homework.show');
        Route::post('/homework/{homework}/submit', [\App\Http\Controllers\Student\HomeworkController::class, 'submit'])->name('homework.submit');
        Route::get('/study-materials', [\App\Http\Controllers\Student\HomeworkController::class, 'materials'])->name('study-materials.index');
        Route::get('/study-materials/{id}', [\App\Http\Controllers\Student\HomeworkController::class, 'showMaterial'])->name('study-materials.show');
        
        // Leaves
        Route::resource('leaves', \App\Http\Controllers\Student\LeaveController::class)->only(['index', 'create', 'store']);
    });

    // Parent Group
    Route::prefix('parent')->name('parent.')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
        Route::get('/child/{student_id}', [ParentController::class, 'childDetails'])->name('child.details');
        
        // Leaves
        Route::resource('leaves', \App\Http\Controllers\Parent\LeaveController::class)->only(['index', 'create', 'store']);
    });

    // Attendance Software Suite Routes (Admin)
    Route::prefix('admin/attendance-suite')->name('admin.attendance-suite.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AttendanceSoftwareDashboardController::class, 'index'])->name('dashboard');

        Route::get('/employees', [\App\Http\Controllers\Admin\EmployeeManagementController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [\App\Http\Controllers\Admin\EmployeeManagementController::class, 'create'])->name('employees.create');
        Route::post('/employees', [\App\Http\Controllers\Admin\EmployeeManagementController::class, 'store'])->name('employees.store');
        Route::get('/employees/{id}', [\App\Http\Controllers\Admin\EmployeeManagementController::class, 'show'])->name('employees.show');
        Route::get('/employees/{id}/edit', [\App\Http\Controllers\Admin\EmployeeManagementController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{id}', [\App\Http\Controllers\Admin\EmployeeManagementController::class, 'update'])->name('employees.update');
        Route::post('/employees/{id}/toggle-status', [\App\Http\Controllers\Admin\EmployeeManagementController::class, 'toggleStatus'])->name('employees.toggle-status');

        Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceManagementController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/manual', [\App\Http\Controllers\Admin\AttendanceManagementController::class, 'markAttendance'])->name('attendance.mark-manual');
        Route::get('/attendance/missing-punches', [\App\Http\Controllers\Admin\AttendanceManagementController::class, 'missingPunches'])->name('attendance.missing-punches');
        Route::get('/attendance/corrections', [\App\Http\Controllers\Admin\AttendanceManagementController::class, 'corrections'])->name('attendance.corrections');
        Route::post('/attendance/approve-correction/{id}', [\App\Http\Controllers\Admin\AttendanceManagementController::class, 'approveCorrection'])->name('attendance.approve-correction');
        Route::get('/attendance/history', [\App\Http\Controllers\Admin\AttendanceManagementController::class, 'history'])->name('attendance.history');

        Route::get('/shifts', [\App\Http\Controllers\Admin\AttendanceShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts', [\App\Http\Controllers\Admin\AttendanceShiftController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/{id}', [\App\Http\Controllers\Admin\AttendanceShiftController::class, 'update'])->name('shifts.update');
        Route::post('/shifts/assign', [\App\Http\Controllers\Admin\AttendanceShiftController::class, 'assignShift'])->name('shifts.assign');
        Route::delete('/shifts/{id}', [\App\Http\Controllers\Admin\AttendanceShiftController::class, 'destroy'])->name('shifts.destroy');

        Route::get('/leaves', [\App\Http\Controllers\Admin\AttendanceLeaveController::class, 'index'])->name('leaves.index');
        Route::post('/leaves/store-type', [\App\Http\Controllers\Admin\AttendanceLeaveController::class, 'storeType'])->name('leaves.store-type');
        Route::post('/leaves/store-application', [\App\Http\Controllers\Admin\AttendanceLeaveController::class, 'storeApplication'])->name('leaves.store-application');
        Route::post('/leaves/update-status/{id}', [\App\Http\Controllers\Admin\AttendanceLeaveController::class, 'updateStatus'])->name('leaves.update-status');

        Route::get('/holidays', [\App\Http\Controllers\Admin\AttendanceHolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [\App\Http\Controllers\Admin\AttendanceHolidayController::class, 'store'])->name('holidays.store');
        Route::post('/holidays/sync-bd', [\App\Http\Controllers\Admin\AttendanceHolidayController::class, 'syncBdHolidays'])->name('holidays.sync-bd');
        Route::delete('/holidays/{id}', [\App\Http\Controllers\Admin\AttendanceHolidayController::class, 'destroy'])->name('holidays.destroy');

        Route::get('/branches', [\App\Http\Controllers\Admin\BranchDepartmentController::class, 'index'])->name('branches.index');
        Route::post('/branches/store-branch', [\App\Http\Controllers\Admin\BranchDepartmentController::class, 'storeBranch'])->name('branches.store');
        Route::post('/branches/store-department', [\App\Http\Controllers\Admin\BranchDepartmentController::class, 'storeDepartment'])->name('departments.store');
        Route::post('/branches/store-designation', [\App\Http\Controllers\Admin\BranchDepartmentController::class, 'storeDesignation'])->name('designations.store');
        Route::post('/branches/store-transfer', [\App\Http\Controllers\Admin\BranchDepartmentController::class, 'storeTransfer'])->name('transfers.store');

        Route::get('/payroll', [\App\Http\Controllers\Admin\AttendancePayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/slip/{staffId}', [\App\Http\Controllers\Admin\AttendancePayrollController::class, 'generatePayslip'])->name('payroll.slip');

        Route::get('/reports', [\App\Http\Controllers\Admin\AttendanceReportController::class, 'index'])->name('reports.index');

        Route::get('/notifications', [\App\Http\Controllers\Admin\AttendanceNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/send', [\App\Http\Controllers\Admin\AttendanceNotificationController::class, 'send'])->name('notifications.send');
    });

    // Employee Panel Routes
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'dashboard'])->name('dashboard');
        
        Route::get('/attendance', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'attendance'])->name('attendance');
        Route::post('/check-in', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'checkOut'])->name('check-out');
        
        Route::get('/leaves', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'leaves'])->name('leaves');
        Route::post('/submit-leave', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'submitLeave'])->name('submit-leave');
        
        Route::get('/holidays', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'holidays'])->name('holidays');
        Route::get('/salary', [\App\Http\Controllers\Employee\EmployeePortalController::class, 'salary'])->name('salary');
    });
});

// API routes (biometric sync & AI analytics & general fetchers)
Route::prefix('api/v1')->group(function () {
    Route::post('/biometric-sync', [BiometricSyncController::class, 'sync']);
    Route::get('/ai/risk-analysis/{student_id}', [AiAnalyticsController::class, 'analyzeRisk']);
    Route::get('/ai/performance-prediction/{student_id}', [AiAnalyticsController::class, 'predictPerformance']);
});

// ZKTeco Hardware Push ADMS Protocols
Route::get('/iclock/cdata', [BiometricSyncController::class, 'admsHandshake']);
Route::post('/iclock/cdata', [BiometricSyncController::class, 'admsReceive']);
Route::get('/iclock/getrequest', [BiometricSyncController::class, 'admsGetRequest']);

