<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certificate extends Model
{
    protected $fillable = ['template_name', 'content'];

    public function issuedCertificates(): HasMany
    {
        return $this->hasMany(IssuedCertificate::class, 'certificate_id');
    }
}
