<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupportTeam\StudentRecordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SupportTeam\PaymentController;
use App\Http\Controllers\SupportTeam\PinController;
use App\Http\Controllers\SupportTeam\PromotionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Static pages (no authentication required)
Route::get('/privacy-policy', [HomeController::class, 'privacy_policy'])->name('privacy_policy');
Route::get('/terms-of-use', [HomeController::class, 'terms_of_use'])->name('terms_of_use');
Route::get('/about', function() {
    return view('pages.about');
})->name('about');
  
Auth::routes(['register' => false]);

// Home route (default redirect after login)
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Dashboard Routes
Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware('auth')->name('dashboard');

// Payment Routes
Route::group(['prefix' => 'payments', 'as' => 'payments.', 'middleware' => ['auth']], function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::get('/create', [PaymentController::class, 'create'])->name('create');
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    Route::post('/select-year', [PaymentController::class, 'select_year'])->name('select_year');
    Route::post('/select-class', [PaymentController::class, 'select_class'])->name('select_class');
    Route::get('/manage/{class_id?}', [PaymentController::class, 'manage'])->name('manage');
    Route::get('/daily-report', [PaymentController::class, 'dailyReport'])->name('daily_report');
    Route::post('/pay-now/{pr_id}', [PaymentController::class, 'pay_now'])->name('pay_now');
    Route::delete('/reset-record/{id}', [PaymentController::class, 'reset_record'])->name('reset_record');
    Route::get('/invoice/{id}', [PaymentController::class, 'invoice'])->name('invoice');
    Route::get('/receipts/{pr_id}', [PaymentController::class, 'receipts'])->name('receipts');
    Route::get('/pdf-receipts/{pr_id}', [PaymentController::class, 'pdf_receipts'])->name('pdf_receipts');
    Route::get('/{year}', [PaymentController::class, 'show'])->name('show');
    Route::get('/{payment_id}/edit', [PaymentController::class, 'edit'])->name('edit');
    Route::put('/{payment_id}', [PaymentController::class, 'update'])->name('update');
    Route::delete('/{payment_id}', [PaymentController::class, 'destroy'])->name('destroy');
});

// Event Routes
Route::prefix('events')->name('events.')->middleware(['auth'])->group(function () {
    Route::get('/get-by-range', [EventController::class, 'getByRange'])->name('get-by-range');
    Route::get('/get-by-date', [EventController::class, 'getByDate'])->name('get-by-date');
    Route::post('/quick-add', [EventController::class, 'quickAdd'])->name('quick-add');
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/create', [EventController::class, 'create'])->name('create');
    Route::post('/', [EventController::class, 'store'])->name('store');
    Route::get('/today', [EventController::class, 'getTodayEvents'])->name('today');
    Route::get('/upcoming', [EventController::class, 'getUpcomingEvents'])->name('upcoming');
    Route::get('/stats', [EventController::class, 'getEventStats'])->name('stats');
    Route::get('/{id}', [EventController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [EventController::class, 'edit'])->name('edit');
    Route::put('/{id}', [EventController::class, 'update'])->name('update');
    Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-visibility', [EventController::class, 'toggleVisibility'])->name('toggle-visibility');
});

// User Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user/{id}', [UserController::class, 'show'])->name('user.view');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [UserController::class, 'update'])->name('profile.update');
    Route::get('/my-account', [UserController::class, 'myAccount'])->name('my_account');
    Route::get('/my-children', function() {
        return view('parents.my-children');
    })->name('my_children');
});

// Users Management Routes (Support Team)
Route::group(['prefix' => 'users', 'as' => 'users.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\UserController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\UserController::class, 'store'])->name('store');
    Route::get('/get-districts', [App\Http\Controllers\SupportTeam\UserController::class, 'getDistricts'])->name('get_districts');
    Route::get('/{id}', [App\Http\Controllers\SupportTeam\UserController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\UserController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\UserController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/reset-password', [App\Http\Controllers\SupportTeam\UserController::class, 'reset_pass'])->name('reset_password');
});

// Academic Routes - Timetables
Route::middleware(['auth'])->group(function () {
    Route::prefix('timetables')->as('tt.')->group(function () {
        Route::get('/', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'store'])->name('store');
        Route::put('/{tt_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'update'])->name('update');
        Route::delete('/{tt_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'delete'])->name('delete');
        
        // Time Slots
        Route::post('/time-slots', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'store_time_slot'])->name('time_slots.store');
        Route::get('/time-slots/{ts_id}/edit', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'edit_time_slot'])->name('time_slots.edit');
        Route::put('/time-slots/{ts_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'update_time_slot'])->name('time_slots.update');
        Route::delete('/time-slots/{ts_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'delete_time_slot'])->name('time_slots.delete');
        Route::post('/use-time-slots/{ttr_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'use_time_slot'])->name('time_slots.use');
        Route::post('/bulk-create-time-slots/{ttr_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'bulk_create_time_slots'])->name('time_slots.bulk_create');
    });
    
    Route::prefix('timetable-records')->as('ttr.')->group(function () {
        Route::get('/', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'store_record'])->name('store');
        // Use {ttr_id} so global Route::bind('id') doesn't decode as hash (plain numeric ID in URL)
        Route::get('/{ttr_id}/manage', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'manage'])->name('manage');
        Route::get('/{ttr_id}/grid', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'grid_view'])->name('grid');
        Route::post('/{ttr_id}/bulk-create-time-slots', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'bulk_create_time_slots'])->name('bulk_create_time_slots');
        Route::post('/{ttr_id}/bulk-store-subjects', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'bulk_store_subjects'])->name('bulk_store_subjects');
        Route::post('/{ttr_id}/store-weekly-slots', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'store_weekly_slots'])->name('store_weekly_slots');
        Route::get('/{ttr_id}/print', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'print_record'])->name('print');
        Route::get('/{ttr_id}/edit', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'edit_record'])->name('edit');
        Route::put('/{ttr_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'update_record'])->name('update');
        Route::delete('/{ttr_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'delete_record'])->name('destroy');
        Route::get('/{ttr_id}', [App\Http\Controllers\SupportTeam\TimeTableController::class, 'show_record'])->name('show');
    });
});

// Student Routes
Route::group(['prefix' => 'students', 'as' => 'students.', 'middleware' => ['auth']], function () {
    Route::get('/', [StudentRecordController::class, 'index'])->name('index');
    Route::get('/list', [StudentRecordController::class, 'index'])->name('list');
    Route::get('/create', [StudentRecordController::class, 'create'])->name('create');
    Route::post('/', [StudentRecordController::class, 'store'])->name('store');

    // Promotion Routes
    Route::get('/promotion/manage', [PromotionController::class, 'manage'])->name('promotion_manage');
    Route::delete('/promotion/reset-all', [PromotionController::class, 'reset_all'])->name('promotion_reset_all');
    Route::delete('/promotion/reset/{promotion_id}', [PromotionController::class, 'reset'])->name('promotion_reset');
    Route::post('/promotion/selector', [PromotionController::class, 'selector'])->name('promote_selector');
    Route::get('/promotion/{fc?}/{fs?}/{tc?}/{ts?}', [PromotionController::class, 'promotion'])->name('promotion');
    Route::post('/promotion/{fc}/{fs}/{tc}/{ts}', [PromotionController::class, 'promote'])->name('promote');

    Route::get('/graduated/list', [StudentRecordController::class, 'graduated'])->name('graduated');
    Route::get('/class/{class_id}', [StudentRecordController::class, 'listByClass'])->name('list_by_class');

    // AJAX Routes
    Route::get('/get-sections/{class_id}', [StudentRecordController::class, 'getSections'])->name('get_sections');
    Route::get('/get-districts', [StudentRecordController::class, 'getDistricts'])->name('get_districts');

    Route::get('/{id}', [StudentRecordController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [StudentRecordController::class, 'edit'])->name('edit');
    Route::put('/{id}', [StudentRecordController::class, 'update'])->name('update');
    Route::delete('/{id}', [StudentRecordController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/reset-password', [StudentRecordController::class, 'reset_pass'])->name('reset_password');
    Route::put('/{id}/not-graduated', [StudentRecordController::class, 'not_graduated'])->name('not_graduated');
});

// Other Groups
Route::group(['prefix' => 'parents', 'as' => 'parents.', 'middleware' => ['auth']], function () {});
Route::group(['prefix' => 'teachers', 'as' => 'teachers.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\TeacherController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\TeacherController::class, 'store'])->name('store');
    Route::get('/get-districts', [App\Http\Controllers\SupportTeam\TeacherController::class, 'getDistricts'])->name('get_districts');
    Route::post('/assign-class-teacher', [App\Http\Controllers\SupportTeam\TeacherController::class, 'assignClassTeacher'])->name('assign_class_teacher');
    Route::post('/assign-subject', [App\Http\Controllers\SupportTeam\TeacherController::class, 'assignSubject'])->name('assign_subject');
    Route::get('/{id}', [App\Http\Controllers\SupportTeam\TeacherController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\TeacherController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\TeacherController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\TeacherController::class, 'destroy'])->name('destroy');
});

// Classes Management Routes
Route::group(['prefix' => 'classes', 'as' => 'classes.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\MyClassController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\MyClassController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\MyClassController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\MyClassController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\MyClassController::class, 'destroy'])->name('destroy');
});

// Subjects Management Routes
Route::group(['prefix' => 'subjects', 'as' => 'subjects.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\SubjectController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\SubjectController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\SubjectController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\SubjectController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\SubjectController::class, 'destroy'])->name('destroy');
});

// Dorms Management Routes
Route::group(['prefix' => 'dorms', 'as' => 'dorms.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\DormController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\DormController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\DormController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\DormController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\DormController::class, 'destroy'])->name('destroy');
});

// Classrooms Management Routes (for timetable room assignment)
Route::group(['prefix' => 'classrooms', 'as' => 'classrooms.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\ClassroomController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\ClassroomController::class, 'store'])->name('store');
    Route::delete('/{classroom_id}', [App\Http\Controllers\SupportTeam\ClassroomController::class, 'destroy'])->name('destroy');
});

// Sections Management Routes
Route::group(['prefix' => 'sections', 'as' => 'sections.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\SectionController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\SectionController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\SectionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\SectionController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\SectionController::class, 'destroy'])->name('destroy');
});

// Exams Management Routes
Route::group(['prefix' => 'exams', 'as' => 'exams.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\ExamController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\ExamController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\ExamController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\ExamController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\ExamController::class, 'destroy'])->name('destroy');
});

// Grades Management Routes
Route::group(['prefix' => 'grades', 'as' => 'grades.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\GradeController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\SupportTeam\GradeController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\GradeController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\GradeController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\GradeController::class, 'destroy'])->name('destroy');
});

// Marks Management Routes
Route::group(['prefix' => 'marks', 'as' => 'marks.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\MarkController::class, 'index'])->name('index');
    Route::post('/selector', [App\Http\Controllers\SupportTeam\MarkController::class, 'selector'])->name('selector');
    Route::get('/bulk/{class_id?}/{section_id?}', [App\Http\Controllers\SupportTeam\MarkController::class, 'bulk'])->name('bulk');
    Route::post('/bulk-select', [App\Http\Controllers\SupportTeam\MarkController::class, 'bulk_select'])->name('bulk_select');
    Route::get('/tabulation/print/{exam_id}/{class_id}/{section_id}', [App\Http\Controllers\SupportTeam\MarkController::class, 'print_tabulation'])->name('print_tabulation');
    Route::get('/tabulation/{exam_id?}/{class_id?}/{section_id?}', [App\Http\Controllers\SupportTeam\MarkController::class, 'tabulation'])->name('tabulation');
    Route::post('/tabulation-select', [App\Http\Controllers\SupportTeam\MarkController::class, 'tabulation_select'])->name('tabulation_select');
    Route::get('/batch-fix', [App\Http\Controllers\SupportTeam\MarkController::class, 'batch_fix'])->name('batch_fix');
    Route::put('/batch-update', [App\Http\Controllers\SupportTeam\MarkController::class, 'batch_update'])->name('batch_update');
    Route::get('/manage/{exam_id}/{class_id}/{section_id}/{subject_id}', [App\Http\Controllers\SupportTeam\MarkController::class, 'manage'])->name('manage');
    Route::put('/update/{exam_id}/{class_id}/{section_id}/{subject_id}', [App\Http\Controllers\SupportTeam\MarkController::class, 'update'])->name('update');
    Route::get('/year-selector/{student_id}', [App\Http\Controllers\SupportTeam\MarkController::class, 'year_selector'])->name('year_selector');
    Route::post('/year-selected/{student_id}', [App\Http\Controllers\SupportTeam\MarkController::class, 'year_selected'])->name('year_select');
    Route::get('/print/{student_id}/{exam_id}/{year}', [App\Http\Controllers\SupportTeam\MarkController::class, 'print_view'])->name('print');
    Route::put('/comment/{exr_id}', [App\Http\Controllers\SupportTeam\MarkController::class, 'comment_update'])->name('comment_update');
    Route::put('/skills/{skill}/{exr_id}', [App\Http\Controllers\SupportTeam\MarkController::class, 'skills_update'])->name('skills_update');
    Route::get('/{student_id}/{year}', [App\Http\Controllers\SupportTeam\MarkController::class, 'show'])->name('show');
});

// PIN Routes for locked marksheets
Route::group(['prefix' => 'pins', 'as' => 'pins.', 'middleware' => ['auth']], function () {
    Route::get('/', [PinController::class, 'index'])->name('index');
    Route::get('/create', [PinController::class, 'create'])->name('create');
    Route::post('/', [PinController::class, 'store'])->name('store');
    Route::get('/enter/{id}', [PinController::class, 'enter_pin'])->name('enter');
    Route::post('/verify/{id}', [PinController::class, 'verify'])->name('verify');
    Route::delete('/{pin_scope}', [PinController::class, 'destroy'])->name('destroy');
});

// Book Routes
Route::group(['prefix' => 'books', 'as' => 'books.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\BookController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\SupportTeam\BookController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\SupportTeam\BookController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\SupportTeam\BookController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\BookController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\BookController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\BookController::class, 'destroy'])->name('destroy');
});

// Book Request Routes
Route::group(['prefix' => 'book-requests', 'as' => 'book_requests.', 'middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\SupportTeam\BookRequestController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\SupportTeam\BookRequestController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\SupportTeam\BookRequestController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\SupportTeam\BookRequestController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\SupportTeam\BookRequestController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\SupportTeam\BookRequestController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\SupportTeam\BookRequestController::class, 'destroy'])->name('destroy');
});

// Role Dashboards
Route::group(['prefix' => 'student', 'as' => 'student.', 'middleware' => ['auth', 'student']], function () {
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/timetable-grid', [App\Http\Controllers\Student\DashboardController::class, 'timetable_grid'])->name('timetable_grid');
    Route::get('/weekly-timetable', [App\Http\Controllers\Student\DashboardController::class, 'weekly_timetable'])->name('weekly_timetable');
});
Route::group(['prefix' => 'teacher', 'as' => 'teacher.', 'middleware' => ['auth', 'teacher']], function () {
    Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/export', [App\Http\Controllers\Teacher\ExportController::class, 'export'])->name('export');
    Route::get('/timetable-grid', [App\Http\Controllers\Teacher\DashboardController::class, 'timetable_grid'])->name('timetable_grid');
    Route::get('/weekly-timetable', [App\Http\Controllers\Teacher\DashboardController::class, 'weekly_timetable'])->name('weekly_timetable');
});
Route::group(['prefix' => 'parent', 'as' => 'parent.', 'middleware' => ['auth', 'parent']], function () {
    Route::get('/dashboard', [App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');
});
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
});
Route::group(['prefix' => 'accountant', 'as' => 'accountant.', 'middleware' => ['auth', 'teamAccount']], function () {
    Route::get('/dashboard', [App\Http\Controllers\Accountant\DashboardController::class, 'index'])->name('dashboard');
});
Route::group(['prefix' => 'librarian', 'as' => 'librarian.', 'middleware' => ['auth', 'librarian']], function () {
    Route::get('/dashboard', [App\Http\Controllers\Librarian\DashboardController::class, 'index'])->name('dashboard');
    
    // Books Management
    Route::get('/books', [App\Http\Controllers\Librarian\BookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [App\Http\Controllers\Librarian\BookController::class, 'create'])->name('books.create');
    Route::post('/books', [App\Http\Controllers\Librarian\BookController::class, 'store'])->name('books.store');
    Route::get('/books/search', [App\Http\Controllers\Librarian\BookController::class, 'search'])->name('books.search');
    Route::get('/books/{book}', [App\Http\Controllers\Librarian\BookController::class, 'show'])->name('books.show');
    Route::get('/books/{book}/edit', [App\Http\Controllers\Librarian\BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [App\Http\Controllers\Librarian\BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [App\Http\Controllers\Librarian\BookController::class, 'destroy'])->name('books.destroy');
    Route::get('/books/{book}/issue', [App\Http\Controllers\Librarian\BookController::class, 'issueForm'])->name('books.issue');
    Route::post('/books/{book}/issue', [App\Http\Controllers\Librarian\BookController::class, 'issueBook'])->name('books.issue.store');
    Route::post('/books/{book}/return/{transaction}', [App\Http\Controllers\Librarian\BookController::class, 'returnBook'])->name('books.return');
    
    // Transactions Management
    Route::get('/transactions', [App\Http\Controllers\Librarian\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/overdue', [App\Http\Controllers\Librarian\TransactionController::class, 'overdue'])->name('transactions.overdue');
    Route::get('/transactions/search', [App\Http\Controllers\Librarian\TransactionController::class, 'search'])->name('transactions.search');
    Route::get('/transactions/report', [App\Http\Controllers\Librarian\TransactionController::class, 'report'])->name('transactions.report');
    Route::post('/transactions/{transaction}/lost', [App\Http\Controllers\Librarian\TransactionController::class, 'markAsLost'])->name('transactions.lost');
    Route::post('/transactions/{transaction}/pay-fine', [App\Http\Controllers\Librarian\TransactionController::class, 'payFine'])->name('transactions.pay_fine');
});

// AJAX Routes (for dynamic form loading)
Route::middleware(['auth'])->group(function () {
    Route::get('/get-lga/{state_id}', [App\Http\Controllers\AjaxController::class, 'get_lga'])->name('get_lga');
    Route::get('/get-class-sections/{class_id}', [App\Http\Controllers\AjaxController::class, 'get_class_sections'])->name('get_class_sections');
    Route::get('/get-class-subjects/{class_id}', [App\Http\Controllers\AjaxController::class, 'get_class_subjects'])->name('get_class_subjects');
});

Route::fallback(function () {
    return view('errors.404');
});