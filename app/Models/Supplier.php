<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'hospital_id', 'name', 'contact_person', 'phone', 'email',
        'address', 'gstin', 'drug_license_no', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
