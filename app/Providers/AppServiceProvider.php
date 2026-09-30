<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Eloquent\StudentRepository;
use App\Repositories\Contracts\AcademicRepositoryInterface;
use App\Repositories\Eloquent\AcademicRepository;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\Eloquent\FeeRepository;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Eloquent\ExamRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(AcademicRepositoryInterface::class, AcademicRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(FeeRepositoryInterface::class, FeeRepository::class);
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Authenticated::class,
            function ($event) {
                try {
                    if ($event->user && session() && !session()->has('school_id')) {
                        session(['school_id' => $event->user->school_id]);
                    }
                } catch (\Throwable $e) {
                    // Ignore session not initialized exceptions (e.g. in Console/Artisan commands)
                }
            }
        );
    }
}
