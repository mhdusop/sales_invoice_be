<?php

namespace App\Models\SalesInvoice;

use App\Models\InvoiceItem\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'branch_name',
        'customer_name',
        'sales_person',
        'date',
        'grand_total',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
