<?php

namespace App\Models\InvoiceItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_invoice_id',
        'item_name',
        'quantity',
        'price',
        'total',
    ];
}
