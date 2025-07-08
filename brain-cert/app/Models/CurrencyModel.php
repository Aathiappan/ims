<?php

namespace App\Models;

use CodeIgniter\Model;

class CurrencyModel extends Model
{
    protected $table            = 'currency';
    protected $primaryKey       = 'currency_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['code', 'symbol', 'name', 'created_by','created_at'];
}
