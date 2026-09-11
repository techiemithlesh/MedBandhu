<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BedBoardController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\Billing\HospitalSubscriptionController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\Billing\ServiceController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DutyRosterController;
use App\Http\Controllers\IpdAdmissionController;
use App\Http\Controllers\IpdChargeController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\NursingNoteController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Pharmacy\DispenseController;
use App\Http\Controllers\Pharmacy\DrugCategoryController;
use App\Http\Controllers\Pharmacy\ManufacturerController;
use App\Http\Controllers\Pharmacy\MedicineController;
use App\Http\Controllers\Pharmacy\PharmacyController;
use App\Http\Controllers\Pharmacy\PharmacyStockController;
use App\Http\Controllers\Pharmacy\PurchaseController;
use App\Http\Controllers\Pharmacy\SupplierController;
use App\Http\Controllers\Platform\HospitalController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\PlatformBillingController;
use App\Http\Controllers\Platform\SubscriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TenantContextController;
use App\Http\Controllers\VitalController;
use App\Http\Controllers\WardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'home'])->name('home');

// Local-SEO city pages — one per city in config('hms.cities').
Route::get('/hospital-management-software', [MarketingController::class, 'cities'])->name('marketing.cities');
Route::get('/hospital-management-software/{city}', [MarketingController::class, 'city'])
    ->where('city', '[a-z-]+')->name('marketing.city');

// One-click public demo — logs the visitor into the shared demo hospital.
Route::get('/demo', [\App\Http\Controllers\DemoController::class, 'enter'])
    ->middleware('throttle:20,1')->name('demo.enter');

// UI language switcher — available to guests and signed-in users alike.
Route::post('/locale', [\App\Http\Controllers\LocaleController::class, 'update'])->name('locale.update');

// SEO / crawler files (served dynamically so they track APP_URL).
Route::get('/robots.txt', [MarketingController::class, 'robots']);
Route::get('/sitemap.xml', [MarketingController::class, 'sitemap']);
Route::get('/llms.txt', [MarketingController::class, 'llms']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Switch the active branch (hospital users) / hospital (super admin).
    Route::post('/context/branch', [TenantContextController::class, 'switchBranch'])->name('context.branch');
    Route::post('/context/hospital/{hospital}', [TenantContextController::class, 'enterHospital'])->name('context.hospital.enter');
    Route::post('/context/hospital', [TenantContextController::class, 'leaveHospital'])->name('context.hospital.leave');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Platform administration — Super Admin only.
    Route::middleware('super-admin')->prefix('platform')->name('platform.')->group(function () {
        Route::resource('hospitals', HospitalController::class);
        Route::resource('plans', PlanController::class)->except('show');

        Route::get('billing', [PlatformBillingController::class, 'index'])->name('billing.index');
        Route::get('billing/invoices', [PlatformBillingController::class, 'invoices'])->name('billing.invoices');
        Route::get('billing/invoices/{invoice}', [PlatformBillingController::class, 'invoice'])->name('billing.invoice');
        Route::get('billing/payments/{payment}/receipt', [PlatformBillingController::class, 'receipt'])->name('billing.payment.receipt');

        // subscription actions on a hospital / its subscription
        Route::post('hospitals/{hospital}/subscription', [SubscriptionController::class, 'assign'])->name('hospitals.subscription.assign');
        Route::post('subscriptions/{subscription}/suspend', [SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
        Route::post('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('subscriptions/{subscription}/invoice', [SubscriptionController::class, 'raiseInvoice'])->name('subscriptions.invoice');
        Route::post('platform-invoices/{invoice}/payment', [SubscriptionController::class, 'recordPayment'])->name('platform-invoices.payment');
        Route::post('platform-invoices/{invoice}/void', [SubscriptionController::class, 'voidInvoice'])->name('platform-invoices.void');
    });

    // Hospital-scoped modules — require a bound hospital context.
    Route::middleware('hospital')->group(function () {

        // Sprint 7 — Subscription & branches (owner-facing)
        Route::middleware('permission:subscription.view')->group(function () {
            Route::get('billing/subscription', [HospitalSubscriptionController::class, 'show'])->name('billing.subscription.show');
            Route::get('billing/subscription/receipts/{payment}', [HospitalSubscriptionController::class, 'receipt'])->name('billing.subscription.receipt');
        });
        Route::middleware('permission:subscription.pay')->group(function () {
            Route::get('billing/subscription/invoices/{invoice}/pay', [HospitalSubscriptionController::class, 'pay'])->name('billing.subscription.pay');
            Route::match(['get', 'post'], 'billing/subscription/invoices/{invoice}/callback', [HospitalSubscriptionController::class, 'callback'])->name('billing.subscription.callback');
        });

        Route::middleware('permission:branches.manage')->group(function () {
            Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
            Route::get('branches/new', [BranchController::class, 'create'])->name('branches.create');
            Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
            Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
            Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        });

        // Sprint 1 — Staff & Doctors
        Route::get('departments', [DepartmentController::class, 'index'])
            ->middleware('permission:departments.view')->name('departments.index');
        Route::middleware('permission:departments.manage')->group(function () {
            Route::get('departments/create', [DepartmentController::class, 'create'])->name('departments.create');
            Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
            Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
            Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
            Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        });

        Route::get('staff', [StaffController::class, 'index'])
            ->middleware('permission:staff.view')->name('staff.index');
        Route::get('staff/create', [StaffController::class, 'create'])
            ->middleware('permission:staff.create')->name('staff.create');
        Route::post('staff', [StaffController::class, 'store'])
            ->middleware('permission:staff.create')->name('staff.store');
        Route::get('staff/{staff}', [StaffController::class, 'show'])
            ->middleware('permission:staff.view')->name('staff.show');
        Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])
            ->middleware('permission:staff.update')->name('staff.edit');
        Route::put('staff/{staff}', [StaffController::class, 'update'])
            ->middleware('permission:staff.update')->name('staff.update');
        Route::delete('staff/{staff}', [StaffController::class, 'destroy'])
            ->middleware('permission:staff.delete')->name('staff.destroy');

        Route::middleware('permission:doctors.view')->group(function () {
            Route::get('doctors', [DoctorController::class, 'index'])->name('doctors.index');
            Route::get('doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.show');
        });
        Route::middleware('permission:schedules.manage')->group(function () {
            Route::get('doctors/{doctor}/schedule', [DoctorScheduleController::class, 'edit'])->name('doctors.schedule.edit');
            Route::put('doctors/{doctor}/schedule', [DoctorScheduleController::class, 'update'])->name('doctors.schedule.update');
        });

        Route::middleware('permission:rosters.view')->get('rosters', [DutyRosterController::class, 'index'])->name('rosters.index');
        Route::middleware('permission:rosters.manage')->post('rosters', [DutyRosterController::class, 'store'])->name('rosters.store');

        // Sprint 2 — Patients
        Route::get('patients', [PatientController::class, 'index'])
            ->middleware('permission:patients.view')->name('patients.index');
        Route::get('patients-search', [PatientController::class, 'search'])
            ->middleware('permission:patients.view')->name('patients.search');
        Route::get('patients/create', [PatientController::class, 'create'])
            ->middleware('permission:patients.create')->name('patients.create');
        Route::post('patients', [PatientController::class, 'store'])
            ->middleware('permission:patients.create')->name('patients.store');
        Route::get('patients/{patient}', [PatientController::class, 'show'])
            ->middleware('permission:patients.view')->name('patients.show');
        Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])
            ->middleware('permission:patients.update')->name('patients.edit');
        Route::put('patients/{patient}', [PatientController::class, 'update'])
            ->middleware('permission:patients.update')->name('patients.update');

        // Sprint 2 — Appointments
        Route::middleware('permission:appointments.view')->group(function () {
            Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
            Route::get('appointments/slots', [AppointmentController::class, 'slots'])->name('appointments.slots');
            Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
        });
        Route::middleware('permission:appointments.create')->group(function () {
            Route::get('appointments-book/new', [AppointmentController::class, 'create'])->name('appointments.create');
            Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        });
        Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
            ->middleware('permission:appointments.cancel')->name('appointments.cancel');

        // Sprint 2 — OPD queue
        Route::middleware('permission:opd.view')->get('opd', [OpdController::class, 'index'])->name('opd.index');
        Route::middleware('permission:opd.manage-queue')->group(function () {
            Route::post('opd/{appointment}/check-in', [OpdController::class, 'checkIn'])->name('opd.check-in');
            Route::post('opd/{appointment}/no-show', [OpdController::class, 'noShow'])->name('opd.no-show');
        });
        Route::middleware('permission:vitals.record')->group(function () {
            Route::get('opd/{appointment}/vitals', [VitalController::class, 'edit'])->name('opd.vitals.edit');
            Route::put('opd/{appointment}/vitals', [VitalController::class, 'update'])->name('opd.vitals.update');
        });
        Route::middleware('permission:opd.consult')->group(function () {
            Route::get('opd/{appointment}/consult', [ConsultationController::class, 'edit'])->name('consultations.edit');
            Route::put('opd/{appointment}/consult', [ConsultationController::class, 'update'])->name('consultations.update');
        });

        // Sprint 3 — Wards & Beds master
        Route::middleware('permission:beds.manage')->group(function () {
            Route::get('wards', [WardController::class, 'index'])->name('wards.index');
            Route::get('wards/create', [WardController::class, 'create'])->name('wards.create');
            Route::post('wards', [WardController::class, 'store'])->name('wards.store');
            Route::get('wards/{ward}/edit', [WardController::class, 'edit'])->name('wards.edit');
            Route::put('wards/{ward}', [WardController::class, 'update'])->name('wards.update');
            Route::delete('wards/{ward}', [WardController::class, 'destroy'])->name('wards.destroy');

            Route::get('wards/{ward}/beds', [BedController::class, 'index'])->name('wards.beds');
            Route::post('wards/{ward}/beds', [BedController::class, 'store'])->name('beds.store');
            Route::put('beds/{bed}', [BedController::class, 'update'])->name('beds.update');
            Route::delete('beds/{bed}', [BedController::class, 'destroy'])->name('beds.destroy');
        });

        // Sprint 3 — IPD
        Route::middleware('permission:ipd.view')->group(function () {
            Route::get('ipd/board', [BedBoardController::class, 'index'])->name('ipd.board');
            Route::get('ipd/admissions', [IpdAdmissionController::class, 'index'])->name('ipd.admissions.index');
            Route::get('ipd/admissions/{admission}', [IpdAdmissionController::class, 'show'])->name('ipd.admissions.show');
        });
        Route::middleware('permission:ipd.admit')->group(function () {
            Route::get('ipd/admit/new', [IpdAdmissionController::class, 'create'])->name('ipd.admissions.create');
            Route::post('ipd/admissions', [IpdAdmissionController::class, 'store'])->name('ipd.admissions.store');
        });
        Route::post('ipd/admissions/{admission}/transfer', [IpdAdmissionController::class, 'transfer'])
            ->middleware('permission:ipd.transfer')->name('ipd.admissions.transfer');
        Route::middleware('permission:ipd.discharge')->group(function () {
            Route::post('ipd/admissions/{admission}/discharge', [IpdAdmissionController::class, 'discharge'])->name('ipd.admissions.discharge');
            Route::post('ipd/admissions/{admission}/charges/generate', [IpdAdmissionController::class, 'generateCharges'])->name('ipd.admissions.charges.generate');
            Route::post('ipd/admissions/{admission}/charges', [IpdChargeController::class, 'store'])->name('ipd.charges.store');
            Route::delete('ipd/admissions/{admission}/charges/{charge}', [IpdChargeController::class, 'destroy'])->name('ipd.charges.destroy');
        });
        Route::middleware('permission:nursing.notes')->group(function () {
            Route::post('ipd/admissions/{admission}/notes', [NursingNoteController::class, 'store'])->name('ipd.notes.store');
            Route::delete('ipd/admissions/{admission}/notes/{note}', [NursingNoteController::class, 'destroy'])->name('ipd.notes.destroy');
        });

        // Sprint 4 — Pharmacy
        Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
            Route::middleware('permission:pharmacy.view')->group(function () {
                Route::get('/', [PharmacyController::class, 'index'])->name('index');
                Route::get('stock', [PharmacyStockController::class, 'index'])->name('stock.index');
                Route::get('medicines', [MedicineController::class, 'index'])->name('medicines.index');
                Route::get('medicines/search', [MedicineController::class, 'search'])->name('medicines.search');
                Route::get('medicines/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');
                Route::get('medicines/{medicine}/batches', [MedicineController::class, 'batches'])->name('medicines.batches');
            });

            Route::middleware('permission:pharmacy.dispense')->group(function () {
                Route::get('dispense', [DispenseController::class, 'index'])->name('dispense.index');
                Route::get('dispense/new', [DispenseController::class, 'create'])->name('dispense.create');
                Route::post('dispense', [DispenseController::class, 'store'])->name('dispense.store');
                Route::get('dispense/{sale}', [DispenseController::class, 'show'])->name('dispense.show');
            });

            Route::middleware('permission:pharmacy.purchase')->group(function () {
                Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
                Route::get('purchases/new', [PurchaseController::class, 'create'])->name('purchases.create');
                Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
                Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
            });

            Route::middleware('permission:pharmacy.manage-stock')->group(function () {
                Route::get('medicines-manage/new', [MedicineController::class, 'create'])->name('medicines.create');
                Route::post('medicines', [MedicineController::class, 'store'])->name('medicines.store');
                Route::get('medicines/{medicine}/edit', [MedicineController::class, 'edit'])->name('medicines.edit');
                Route::put('medicines/{medicine}', [MedicineController::class, 'update'])->name('medicines.update');

                Route::post('stock/{batch}/adjust', [PharmacyStockController::class, 'adjust'])->name('stock.adjust');

                Route::get('manufacturers', [ManufacturerController::class, 'index'])->name('manufacturers.index');
                Route::post('manufacturers', [ManufacturerController::class, 'store'])->name('manufacturers.store');
                Route::put('manufacturers/{manufacturer}', [ManufacturerController::class, 'update'])->name('manufacturers.update');
                Route::delete('manufacturers/{manufacturer}', [ManufacturerController::class, 'destroy'])->name('manufacturers.destroy');

                Route::get('categories', [DrugCategoryController::class, 'index'])->name('categories.index');
                Route::post('categories', [DrugCategoryController::class, 'store'])->name('categories.store');
                Route::put('categories/{category}', [DrugCategoryController::class, 'update'])->name('categories.update');
                Route::delete('categories/{category}', [DrugCategoryController::class, 'destroy'])->name('categories.destroy');

                Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
                Route::get('suppliers/new', [SupplierController::class, 'create'])->name('suppliers.create');
                Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
                Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
                Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
                Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
            });
        });

        // Sprint 5 — Billing
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::middleware('permission:billing.view')->group(function () {
                Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
                Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
                Route::get('collections', [PaymentController::class, 'index'])->name('payments.index');
                Route::get('receipts/{payment}', [PaymentController::class, 'receipt'])->name('payments.receipt');
                Route::get('services', [ServiceController::class, 'index'])->name('services.index');
            });

            Route::middleware('permission:billing.create')->group(function () {
                Route::get('invoices-new/create', [InvoiceController::class, 'create'])->name('invoices.create');
                Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
                Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
                Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem'])->name('invoices.items.add');
                Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class, 'removeItem'])->name('invoices.items.remove');
                Route::post('invoices/{invoice}/finalize', [InvoiceController::class, 'finalize'])->name('invoices.finalize');
                Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

                Route::get('services-new/create', [ServiceController::class, 'create'])->name('services.create');
                Route::post('services', [ServiceController::class, 'store'])->name('services.store');
                Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
                Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
                Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
            });

            Route::middleware('permission:billing.collect-payment')
                ->post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
            Route::middleware('permission:billing.refund')
                ->post('invoices/{invoice}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        });

        // Sprint 6 — Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::middleware('permission:reports.view')->group(function () {
                Route::get('/', [ReportController::class, 'index'])->name('index');
                Route::get('opd', [ReportController::class, 'opd'])->name('opd');
                Route::get('ipd', [ReportController::class, 'ipd'])->name('ipd');
                Route::get('patients', [ReportController::class, 'patients'])->name('patients');
            });
            Route::middleware('permission:reports.view-financial')->group(function () {
                Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
                Route::get('pharmacy', [ReportController::class, 'pharmacy'])->name('pharmacy');
            });
        });
    });
});

require __DIR__.'/auth.php';
