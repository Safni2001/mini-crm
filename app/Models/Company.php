<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'email',
        'logo',
        'website',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? asset('storage/' . $this->logo) : null,
        );
    }

    public function getLogoPath(): ?string
    {
        return $this->logo ? storage_path('app/public/' . $this->logo) : null;
    }

    public function hasLogo(): bool
    {
        return !empty($this->logo);
    }

    public function deleteLogoFile(): bool
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->delete($this->logo);
        }
        
        return true;
    }
}
