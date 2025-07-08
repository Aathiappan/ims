<?php

namespace App\Models;

use CodeIgniter\Model;

class QuantityTypeModel extends Model
{
    protected $table            = 'quantity_type';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'created_by','created_at'];
}
