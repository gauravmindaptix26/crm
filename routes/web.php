<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Models\Project;
use App\Models\User;
use App\Mail\ProjectAssignedMail;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\{
    ProfileController, DashboardController, DepartmentController, UserController, CandidateController,
    SupportTicketController, ProjectDirectoryController, CountryController, GeoTargetController,
    GuestPostController, GigController, LinkBuildingController,ProjectMonthlyReportController, TaskPhaseController,
    ProjectTaskController, ManageLinkController, PaymentAccountController,ProjectPaymentController,HiredFromController,AllDataEntryController,ProjectController,AllPortfolioController,SaleTeamProjectController,LogController,SalesLeadController,AssignedProjectController,ProjectPortfolioController,PaymentDetailController,NicheController,EmailTemplateController,AllRndController,MyAssignedProjectController,DsrController,DsrReportController,PmProjectsReportController,TeamReportController,SalesProjectAttachmentController,TaskController,SubmittedTaskReportController,UserNoteController,HrNoteController,AllProjectStatusController,ProjectAttachmentController,NotificationController,ProjectFollowupController,NotificationActionController,FollowupOverdueNotification,EmployeeReviewController,ProjectAuditController,SubmissionsCategoryController,SubmissionSiteController,SubmissionController,MozSubmissionSiteController,LeavePolicyController,LeaveRequestController,LeaveController,SeoPmDsrController,AdminDsrController,WebDevPmDsrController,AdminWebDevDsrController
};

// Home Route 
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
});

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Department Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('departments', DepartmentController::class);
}); 

// User Management Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('/users/generate-employee-code', [UserController::class, 'generateEmployeeCode'])->name('users.generateEmployeeCode');
    Route::get('/users/filter', [UserController::class, 'filterUsers'])->name('users.filter');
    Route::get('/users/{id}', [UserController::class, 'shows'])->name('users.show');
    Route::post('/user-notes', [UserNoteController::class, 'store'])->name('user-notes.store');


});

Route::middleware(['auth'])->group(function () {
    Route::get('/reviews', [EmployeeReviewController::class, 'index'])->name('pm.reviews.index');
    Route::post('/reviews', [EmployeeReviewController::class, 'store'])->name('pm.reviews.store');
    Route::get('/all-reviews', [EmployeeReviewController::class, 'allReviews'])
    ->name('admin.reviews.index');
});

Route::middleware(['auth'])->group(function () {

Route::post('/hr-notes', [HrNoteController::class, 'store'])->name('hr-notes.store');
});

// Candidate Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('candidates', CandidateController::class)->except(['show']);
});

// Support Ticket Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('support-tickets', SupportTicketController::class);
    Route::get('support-tickets/{id}', [SupportTicketController::class, 'show']);
    Route::post('/support-tickets/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support-tickets.reply');
});

// Project Directory Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('project-directories', ProjectDirectoryController::class);
});

// Country Management Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('countries', CountryController::class);
});

// Geo Target Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('geo-targets', GeoTargetController::class)->except(['show']);
});

// Guest Post Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('guest-posts', GuestPostController::class);
});

// Gig Management Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('gigs', GigController::class);
});

// Link Building Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('link-building', LinkBuildingController::class);
});

// Task Phase Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('task-phases', TaskPhaseController::class);
});

// Project Task Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('project-tasks', ProjectTaskController::class);
});

// Manage Links Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('manage-links', ManageLinkController::class)->except(['show']);
});
Route::get('projects/{project}/edit-page', [ProjectController::class, 'editPage'])->name('projects.edit.page');
Route::put('projects/{id}', [ProjectController::class, 'update'])->name('projects.update'); 



// Project Routes
Route::middleware(['auth'])->group(function () {
Route::get('/api/subcategories/{parent_id}', [ProjectController::class, 'getSubcategories'])->name('get.subcategories');
Route::resource('projects', ProjectController::class);

    // Project Status Update Routes (Moved Inside Middleware)
Route::get('projects/{id}/status', [ProjectController::class, 'editStatus'])->name('projects.status');
Route::put('projects/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');
Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    // In routes/web.php
Route::post('projects/{project}/duplicate', [ProjectController::class, 'duplicate'])->name('projects.duplicate');

Route::get('projects/{project}/attachments/create', [ProjectAttachmentController::class, 'create'])->name('projects.attachments.create');
Route::post('projects/{project}/attachments', [ProjectAttachmentController::class, 'store'])->name('projects.attachments.store');
Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});

Route::get('pending-invoices', [ProjectController::class, 'pendingInvoices'])->name('projects.pending.invoices');


Route::middleware(['auth'])->group(function () {

Route::get('/projects/status/{status}', [AllProjectStatusController::class, 'index'])->name('projects.byStatus');

});
Route::get('/projects/by-payment/{department_id}', [AllProjectStatusController::class, 'byPayment'])->name('projects.byPayment');
Route::get('/projects/closed-breakdown/{department_id}', [AllProjectStatusController::class, 'closedBreakdown'])->name('projects.closedBreakdown');





// Manage Links Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('payment_accounts', PaymentAccountController::class);
});

//Project Payment
Route::middleware(['auth'])->group(function () {

Route::resource('project_payments', ProjectPaymentController::class);
});
//Project Monthly Report
Route::middleware(['auth'])->group(function () {

Route::resource('project_monthly_reports', ProjectMonthlyReportController::class);

});

//Project Closed Report
Route::middleware(['auth'])->group(function () {

Route::get('closed-projects', [ProjectController::class, 'closed'])->name('projects.closed');
    
});
//Project Paused Report
Route::middleware(['auth'])->group(function () {

Route::get('paused-projects', [ProjectController::class, 'paused'])->name('projects.paused');
Route::post('projects/{project}/followup/send', [ProjectFollowupController::class, 'send'])
        ->name('projects.followup.dispatch');
        // Route::post('/notifications/dismiss', [NotificationActionController::class, 'dismiss'])->name('notifications.dismiss');
        Route::get('/notifications/{id}/read', [NotificationActionController::class, 'markAsRead'])->name('notifications.read');        
});

//Project Hired From
Route::middleware(['auth'])->group(function () {
    Route::resource('hired-from', HiredFromController::class);
});

//All Data Entry
Route::middleware(['auth'])->group(function () {
    Route::resource('all-data-entries', AllDataEntryController::class);

});

//Sales Lead
Route::middleware(['auth'])->group(function () {
    Route::resource('sales-leads', SalesLeadController::class);
    Route::post('sales-leads/update-status', [SalesLeadController::class, 'updateStatus'])->name('sales-leads.updateStatus');
    Route::get('/all-sales-leads', [SalesLeadController::class, 'allSalesLeads'])->name('all.sales.leads');
    Route::get('/sales-lead/{id}', [SalesLeadController::class, 'show'])->name('sales-lead.show');
    Route::post('/sales-lead/{id}/add-note', [SalesLeadController::class, 'addNote'])->name('sales-lead.addNote');



});

//Portfolio
Route::middleware(['auth'])->group(function () {
    Route::resource('all-portfolios', AllPortfolioController::class);
});

//Sale Projects
Route::middleware(['auth'])->group(function () {
    Route::resource('sales-projects', SaleTeamProjectController::class);
    Route::get('sales-projects/{sales_project}/attachments/create', [SalesProjectAttachmentController::class, 'create'])->name('sales-projects.attachments.create');
 

Route::post('sales-projects/{sales_project}/attachments', [SalesProjectAttachmentController::class, 'store'])->name('sales-projects.attachments.store');

});

//Assigen Sale Projects
Route::middleware(['auth'])->group(function () {
    Route::resource('assigned-projects', AssignedProjectController::class);

});

//Portfolio Project
 Route::middleware(['auth'])->group(function () {
    Route::resource('project-portfolios', ProjectPortfolioController::class);

 });

 Route::get('payment-details', [PaymentDetailController::class, 'index'])->name('payment.details');
// Niche Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('niches', NicheController::class);
});

Route::middleware(['auth'])->group(function () {

Route::resource('email-templates', EmailTemplateController::class);
});

// Rnd Route
Route::middleware(['auth'])->group(function () {

Route::resource('all-rnds', AllRndController::class);
});
Route::get('/success', function (Illuminate\Http\Request $request) {
    return view('auth.success', ['redirect_to' => $request->query('redirect_to', route('dashboard'))]);
})->name('auth.success');
// Employee assigned  Route
Route::get('my-assigned-projects', [MyAssignedProjectController::class, 'index'])
    ->name('my.assigned.projects')
    ->middleware('auth');
 
// Project Manager Team Report Page
Route::middleware(['auth'])->group(function () {

Route::get('/project-manager/team-report', [DashboardController::class, 'teamReport'])->name('projectManager.teamReport');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/sales-team-projects', [DashboardController::class, 'salesTeamProjects'])->name('sales.team.projects');
    Route::get('sales-team-projects/working/{user_id?}', [DashboardController::class, 'salesTeamWorkingProjects'])
    ->name('sales.team.projects.working');

});

//DSR Controller
Route::middleware(['auth'])->group(function () {
    Route::get('/dsr/create', [DsrController::class, 'create'])->name('dsr.create');
    Route::post('/dsr/store', [DsrController::class, 'store'])->name('dsr.store');
    Route::get('/dsr/previous', [DsrController::class, 'showPreviousDsrs'])->name('dsr.previous');

    
     // Team DSR report listing (accessible by Project Manager)
     Route::get('/team-dsr', [DsrController::class, 'index'])->name('team.dsr.index');
     Route::get('/team-dsr/view/{user_id}/{report_date}', [DsrController::class, 'view'])->name('team.dsr.view');
     Route::get('/employee-all-dsr', [DsrController::class, 'allEmployeeDsr'])->name('employee.all.dsr');
Route::post('/employee-all-dsr/search', [DsrController::class, 'searchEmployeeDsr'])->name('employee.all.dsr.search');


});
//Design Team Report
Route::middleware(['auth'])->group(function () {

Route::get('/design-team-reports', [DashboardController::class, 'index'])->name('design-team-reports.index');
});

//PM Project Report
Route::middleware(['auth'])->group(function () {

    Route::get('/pm-projects-report', [PmProjectsReportController::class, 'index'])->name('pm-projects-report');
    Route::get('/pm-projects-list', [PmProjectsReportController::class, 'projectList'])->name('pm-projects-list');

    });
    Route::get('/pm-projects-report/sales-persons', [PmProjectsReportController::class, 'fetchSalesPersons']);

    //PM Project Report
Route::middleware(['auth'])->group(function () {

    Route::get('/team-reports', [TeamReportController::class, 'index'])->name('team-reports.index');
});

 //Task Management
 Route::get('tasks/{task}/edit-json', [TaskController::class, 'editJson'])->name('tasks.editJson');
 Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');


 Route::middleware(['auth'])->group(function () {

    Route::resource('tasks', TaskController::class);
    Route::get('/task/{task}/add-message', [TaskController::class, 'showAddMessageForm'])->name('task.addMessageForm');
    Route::post('/task/{task}/submit-message', [TaskController::class, 'submitMessage'])->name('task.submitMessage');
    Route::get('tasks/submitted', [TaskController::class, 'submittedReport'])->name('tasks.submitted');


});
Route::middleware(['auth'])->group(function () {

Route::get('/submitted-tasks', [SubmittedTaskReportController::class, 'index'])->name('submitted.tasks');
});



Route::middleware(['auth'])->group(function () {
    Route::get('/send-email', [NotificationController::class, 'showForm'])->name('admin.send.email.form');
    Route::post('/send-email', [NotificationController::class, 'sendEmail'])->name('admin.send.email.submit');
});
Route::get('/test-log', function () {
    Log::info('✅ Test log entry written at: ' . now());
    return "Log written. Check storage/logs/laravel.log";
});
Route::middleware(['auth'])->group(function () {
    Route::get('audit', [ProjectAuditController::class, 'index'])->name('projects.audit');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('submission_categories', SubmissionsCategoryController::class);
    Route::resource('submission_sites', SubmissionSiteController::class);

});

Route::prefix('website-submission')->group(function () {
    Route::get('/', [SubmissionController::class, 'index'])->name('submissions.index'); // show all categories
    Route::get('/{slug}', [SubmissionController::class, 'show'])->name('submissions.show'); // show sites inside a category
});

Route::middleware(['auth'])->group(function () {
    Route::resource('moz-sites', MozSubmissionSiteController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::resource('leave-policies', LeavePolicyController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);});

// Route::middleware(['auth'])->group(function () {
// Route::resource('leave-requests', LeaveRequestController::class);
// Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
// Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');});

Route::middleware(['auth'])->group(function () {
    Route::get('leaves/apply', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('leaves/apply', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('leaves/history', [LeaveController::class, 'history'])->name('leaves.history');
    Route::get('leaves/team-history', [LeaveController::class, 'teamHistory'])->name('leaves.team-history');
});

Route::middleware('auth')->group(function () {
    // ... existing routes
    Route::patch('/leaves/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');    
    Route::patch('/leaves/{id}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
});

Route::middleware('auth')->group(function () {
    // ... existing routes
    Route::get('/attendance/dashboard', [LeaveController::class, 'dashboard'])->name('attendance.dashboard');}
);

// Route::middleware('auth')->group(function () {
//     Route::get('/seo-pm-dsr', [SeoPmDsrController::class, 'create'])->name('seo.pm.dsr.create');
//     Route::post('/seo-pm-dsr', [SeoPmDsrController::class, 'store'])->name('seo.pm.dsr.store');
//     Route::get('/seo-pm-dsr/{id}/edit', [SeoPmDsrController::class, 'edit'])->name('seo.pm.dsr.edit');
//     Route::put('/seo-pm-dsr/{id}', [SeoPmDsrController::class, 'update'])->name('seo.pm.dsr.update');
// });
Route::middleware('auth')->group(function () {


Route::get('/seo-pm-dsr', [SeoPmDsrController::class, 'create'])->name('seo.pm.dsr.dashboard');
Route::get('/seo-pm-dsr/daily', [SeoPmDsrController::class, 'showDailyForm'])->name('seo.pm.dsr.daily');
Route::post('/seo-pm-dsr/daily', [SeoPmDsrController::class, 'storeDaily'])->name('seo.pm.dsr.store.daily');

Route::get('/seo-pm-dsr/weekly', [SeoPmDsrController::class, 'showWeeklyForm'])->name('seo.pm.dsr.weekly');
Route::post('/seo-pm-dsr/weekly', [SeoPmDsrController::class, 'storeWeekly'])->name('seo.pm.dsr.store.weekly');

Route::get('/seo-pm-dsr/monthly', [SeoPmDsrController::class, 'showMonthlyForm'])->name('seo.pm.dsr.monthly');
Route::post('/seo-pm-dsr/monthly', [SeoPmDsrController::class, 'storeMonthly'])->name('seo.pm.dsr.store.monthly');

});



Route::prefix('admin')->middleware('auth')->group(function () {

    Route::get('dsr-reports', [AdminDsrController::class, 'index'])
        ->name('admin.dsr.reports');

    Route::get('dsr-reports/{pm_id}', [AdminDsrController::class, 'show'])
        ->name('admin.dsr.show');

    Route::get('dsr/report/{id}', [AdminDsrController::class, 'view'])
        ->name('admin.dsr.view');

    Route::post('dsr/{id}/coo-status', [AdminDsrController::class, 'updateCooRating'])
        ->name('admin.dsr.update-coo-status');
});

// PM Routes
Route::middleware('auth')->group(function () {
    Route::get('web-dev/pm/dsr/daily', [WebDevPmDsrController::class, 'showDailyForm'])->name('web.dev.pm.dsr.daily');
    Route::post('web-dev/pm/dsr/store-daily', [WebDevPmDsrController::class, 'storeDaily'])->name('web.dev.pm.dsr.store.daily');
    Route::get('web-dev/pm/dsr/dashboard', function () { return view('web_dev_pm_dsr.dashboard'); })->name('web.dev.pm.dsr.dashboard');
});

// Admin Routes
Route::middleware('auth')->group(function () {
    Route::get('admin/web-dev/dsr/reports', [AdminWebDevDsrController::class, 'index'])->name('admin.web.dev.dsr.index');
    Route::get('admin/web-dev/dsr/report/{id}', [AdminWebDevDsrController::class, 'view'])->name('admin.web.dev.dsr.view');
});

// Authentication Routes
require __DIR__.'/auth.php';
