<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penanda bahwa order memang memiliki dokumen invoice/PKS.
 * Data invoice sendiri tersimpan di tabel invoices dan agreements.
 */
class OrderDocument extends Model
{
    protected $fillable = ['label'];
}
