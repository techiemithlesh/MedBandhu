<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrugCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = ['hospital_id', 'name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
