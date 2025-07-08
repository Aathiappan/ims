<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table      = 'product';
    protected $primaryKey = 'id';
	protected $returnType = 'array';
    protected $allowedFields = ['name', 'description', 'sku_id', 'status', 'created_by','created_at','updated_at','deleted_at'];
	
	//protected $useSoftDeletes = true;
    //protected $deletedField  = 'deleted_at';
}
