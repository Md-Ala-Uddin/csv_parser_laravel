<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductData extends Model
{
    use HasFactory;

    protected $table = 'tblProductData';
    protected $primaryKey = 'intProductDataId';
    public $timestamps = false;

    protected $fillable = [
        'strProductName',
        'strProductDesc',
        'strProductCode',
        'dtmAdded',
        'dtmDiscontinued',
        'stock_level',
        'price'
    ];

    protected $casts = [
        'stock_level' => 'integer',
        'price' => 'float',
        'dtmAdded' => 'datetime',
        'dtmDiscontinued' => 'datetime',
        'stmTimestamp' => 'datetime'
    ];

    public function setStockLevelAttribute($value)
    {
        // Cast '' to 0
        $this->attributes['stock_level'] = $value !== '' ? $value : 0;
    }
    
    public function setPriceAttribute($value)
    {
        // Cast '' to 0.0
        $this->attributes['price'] = $value !== '' ? $value : 0.0;
    }
}
