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
        'dtmDiscontinued'
    ];

    protected $casts = [
        'dtmAdded' => 'datetime',
        'dtmDiscontinued' => 'datetime',
        'stmTimestamp' => 'datetime'
    ];
}
