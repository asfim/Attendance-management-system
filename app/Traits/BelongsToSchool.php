<?php

namespace App\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::creating(function ($model) {
            if (!$model->school_id && Auth::check()) {
                $model->school_id = Auth::user()->school_id;
            }
        });

        static::addGlobalScope('school', function (Builder $builder) {
            try {
                if (session() && session()->has('school_id')) {
                    $builder->where($builder->getQuery()->from . '.school_id', session('school_id'));
                } elseif (static::class !== \App\Models\User::class && Auth::check()) {
                    $user = Auth::user();
                    if ($user && $user->school_id) {
                        $builder->where($builder->getQuery()->from . '.school_id', $user->school_id);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore exceptions during console boots
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
