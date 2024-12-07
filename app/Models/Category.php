<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getActiveProductsCount()
    {
        return $this->products()->where('active', true)->count();
    }
}
