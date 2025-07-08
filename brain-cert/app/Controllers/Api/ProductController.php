<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ProductModel;
use App\Models\CurrencyModel;
use App\Models\QuantityTypeModel;
use App\Models\UnitModel;
use App\Models\CategoryModel;

class ProductController extends ResourceController
{
	protected $format = 'json';
	
    public function create(){
		$rawData = $this->request->getPost();
		$data = array_map('trim', $rawData);
		$validation = \Config\Services::validation();
		$rules = [
			'name' => [
				'label' => "Product Name",
				'rules' => "required|regex_match[/^[A-Za-z0-9\- ]{1,100}$/]",
				'errors' => [
                    'required' => "Product name is required.",
                    'regex_match'  => "Invalid Product name.",
                ],
			],
			'description' => [
				'label' => "Product description",
				'rules' => "if_exist|min_length[3]|max_length[244]",
				'errors' => [
					'min_length' => "Product description minimum 3 characters required.",
                    'max_length'  => "Product description 244 characters only allowed.",
                ],
			],
			'sku_id' => [
				'label' => "Product sku",
				'rules' => "required|regex_match[/^[A-Za-z0-9-]{1,30}$/]",
				'errors' => [
					'required' => "Product sku_id is required.",
                    'regex_match'  => "Invalid product sku_id.",
                ],
			],
			'category_id' => [
				'label' => "Product category id",
				'rules' => "required|regex_match[/^\d{1,10}$/]",
				'errors' => [
					'required' => "Product category id is required.",
                    'regex_match'  => "Invalid product category id.",
                ],
			],
			'amount' => [
				'label' => "Product amount",
				'rules' => "required|regex_match[/^\d{1,8}(\.\d{1,2})?$/]",
				'errors' => [
					'required' => "Product amount is required.",
                    'regex_match'  => "Invalid product amount.",
                ],
			],			
			'currency_code' => [
				'label' => "Currency code",
				'rules' => "required|regex_match[/^[A-Za-z]{3}$/]",
				'errors' => [
					'required' => "Product currency code is required.",
                    'regex_match'  => "Invalid product currency code.",
                ],
			],
			'quantity_name' => [
				'label' => "Quantity Name",
				'rules' => "required|regex_match[/^[A-Za-z ]{1,50}$/]",
				'errors' => [
                    'required' => "Product quantity name is required.",
                    'regex_match'  => "Invalid product quantity name.",
                ],
			],
			'unit_name' => [
				'label' => "Unit Name",
				'rules' => "required|regex_match[/^[A-Za-z ]{1,50}$/]",
				'errors' => [
                    'required' => "Product unit name is required.",
                    'regex_match'  => "Invalid product unit name.",
                ],
			],
		];
		$validation->setRules($rules);
	    $errors = [];
		if (! $validation->run($data)) {
            $errors = $validation->getErrors();
	    }
		$categoryModel = new CategoryModel();
		if(empty($errors)){
			$isCategory = $categoryModel->where('category_id',$data['category_id'])->first();
			if(!$isCategory){
				$errors['category_id'] = 'Product category id is not exist';
			}
		}
		$productModel = new ProductModel();
		if(empty($errors)){
			$isSku = $productModel->where('sku_id',$data['sku_id'])->first();
			//log_message('error',"sku ".json_encode($isSku)." errors ".json_encode($errors));
			if($isSku){
				$errors['sku_id'] = 'Product sku_id is already exist';
			}
		}
		$currency_id = '';
		$currencyModel = new CurrencyModel();
		if(empty($errors)){
			$isCurCode = $currencyModel->where('code',$data['currency_code'])->first();
			if(!$isCurCode){
				$errors['currency_code'] = 'Product currency_code is not exist';
			}else{
				$currency_id = $isCurCode['currency_id'];
			}
			
		}
		$quantity_id = '';
		$quantityTypeModel = new QuantityTypeModel();
		if(empty($errors)){
			$isQuantityTyp = $quantityTypeModel->where('name',$data['quantity_name'])->first();
			if(!$isQuantityTyp){
				$errors['quantity_name'] = 'Product quantity_name is not exist';
			}else{
				$quantity_id = $isQuantityTyp['id'];
			}
			
		}
		$unit_id = '';
		$unitModel = new UnitModel();
		if(empty($errors)){
			$isUnit = $unitModel->where('name',$data['unit_name'])->first();
			if(!$isUnit){
				$errors['unit_name'] = 'Product unit name is not exist';
			}else{
				$unit_id = $isUnit['unit_id'];
			}
			
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$user = auth()->user();
		$db = \Config\Database::connect();
		try{
			$db->transStart();
			$db->table('product')->insert(['name' => $data['name'], 'description' => $data['description'], 'sku_id' => $data['sku_id']
			,'created_by' => $user->id, 'created_at' => time()]);
			$product_id = $db->insertID();
			
			$db->table('product_category')->insert(['product_id' => $product_id, 'category_id' => $data['category_id'], 
			'assigned_by' => $user->id, 'assigned_at' => time()]);
			
			$db->table('product_prices')->insert(['product_id' => $product_id, 'currency_id' => $currency_id, 'price' => $data['amount'], 'effective_from' => time(), 'created_by' => $user->id, 'created_at' => time()]);
			
			$db->table('product_quantity_unit')->insert(['product_id' => $product_id, 'quantity_type_id' => $quantity_id, 'unit_id' => $unit_id, 'created_by' => $user->id, 'created_at' => time()]);
			$db->transComplete();
			if ($db->transStatus() === false) {
				log_message('error', "Product controller - create product - db insert error1");
				return $this->respond(['status' => 'failure','message' => "Product creation failed. Internal error."]);
			}
			return $this->respond(['status' => 'success','message' => "Product created successfully.", "product_id" => $product_id]);
		}catch (\Throwable $e) {
			$db->transRollback();
			log_message('error', "Product controller - create product - db insert error2");
			return $this->respond(['status' => 'failure','message' => "Product creation failed. Internal error."]);
		}
	}
	public function get_product($id=null){
		if($id != null){
			$data["product_id"] = trim($id);
			$validation = \Config\Services::validation();
			$rules = [
				'product_id' => [
					'label' => "Product Id",
					'rules' => "required|regex_match[/^\d{1,10}$/]",
					'errors' => [
						'required' => "Product Id is required.",
						'regex_match'  => "Invalid Product Id.",
					],
				],
			];
			$validation->setRules($rules);
			if (! $validation->run($data)) {
				return $this->respond(['status' => 'failure','message' => $validation->getErrors()]);
			}
			$db = \Config\Database::connect();
			$sql = "CALL get_product_items(?,?)";
			$query = $db->query($sql, [$data["product_id"],'1']);

			$product = $query->getResult();
			if($product){
				return $this->respond(['status' => 'success','message' => "Product found", "data" => $product]);
			}else{
				return $this->respond(['status' => 'failure','message' => "Product not found"]);
			}
		}else{
			$db = \Config\Database::connect();
			$sql = "CALL get_product_items(?,?)";
			$query = $db->query($sql, ['null','1']);

			$products = $query->getResult();
			if($products){
				return $this->respond(['status' => 'success','message' => "Products found", "data" => $products]);
			}else{
				return $this->respond(['status' => 'failure','message' => "Products not found"]);
			}
		}
	}
	
	public function update_product(){
		$rawData = $this->request->getRawInput();
		$data = array_map('trim', $rawData);
		if(isset($data['status'])){
			$validation = \Config\Services::validation();
			$rules = [
				'product_id' => [
					'label' => "Product  id",
					'rules' => "required|regex_match[/^\d{1,10}$/]",
					'errors' => [
						'required' => "Product id is required.",
						'regex_match'  => "Invalid product id.",
					],
				],
			];
			$validation->setRules($rules);
			$errors = [];
			if (!$validation->run($data)) {
				$errors = $validation->getErrors();
			}
			$product = [];
			$productModel = new ProductModel();
			if(!isset($errors['product_id'])){
				$product = $productModel->where('deleted_at','null')->where('id',$data['product_id'])->first();
				//log_message('error',"status ".gettype($product['status']).$product['status']);
				if(!$product){
					$errors['product_id'] = "Product not found";
					return $this->respond(['status' => 'failure','message' => $errors]);
				}
			}else{
				return $this->respond(['status' => 'failure','message' => $errors]);
			}
			$status = false;
			switch($data['status']){
				case 'activate':
					if($product['status'] == '1'){
						return $this->respond(['status' => 'failure','message' => ['status' => 'Product already activated.']]);
					}else{
						$status = '1';
					}
					break;
				case 'deactivate':
					if($product['status'] == '0'){
						return $this->respond(['status' => 'failure','message' => ['status' => 'Product already deactivated.']]);
					}else{
						$status = '0';
					}
					break;
				default:
					return $this->respond(['status' => 'failure','message' => ['status' => 'This is not acceptable status.']]);
			}
			if($status !== false){
				try{
					$productModel->update($data['product_id'],['status' => $status, 'updated_at' => time()]);
				}catch(\CodeIgniter\Database\Exceptions\DatabaseException $e){
					log_message('error', "Product controller - update product - db update error ".$e->getMessage());
					return $this->respond(['status' => 'failure','message' => "Product status update failed."]);
				}
				return $this->respond(['status' => 'success','message' => "Product status updated"]);
			}			
		}
		$validation = \Config\Services::validation();
		$rules = [
			'product_id' => [
				'label' => "Product  id",
				'rules' => "required|regex_match[/^\d{1,10}$/]",
				'errors' => [
					'required' => "Product id is required.",
                    'regex_match'  => "Invalid product id.",
                ],
			],
			'name' => [
				'label' => "Product Name",
				'rules' => "if_exist|regex_match[/^[A-Za-z0-9\- ]{1,100}$/]",
				'errors' => [
                    'regex_match'  => "Invalid Product name.",
                ],
			],
			'description' => [
				'label' => "Product description",
				'rules' => "if_exist|min_length[3]|max_length[244]",
				'errors' => [
					'min_length' => "Product description minimum 3 characters required.",
                    'max_length'  => "Product description 244 characters only allowed.",
                ],
			],
			'sku_id' => [
				'label' => "Product sku",
				'rules' => "if_exist|regex_match[/^[A-Za-z0-9-]{1,30}$/]",
				'errors' => [
                    'regex_match'  => "Invalid product sku_id.",
                ],
			],
			'amount' => [
				'label' => "Product amount",
				'rules' => "if_exist|regex_match[/^\d{1,8}(\.\d{1,2})?$/]",
				'errors' => [
                    'regex_match'  => "Invalid product amount.",
                ],
			],
			'currency_code' => [
				'label' => "Currency code",
				'rules' => "if_exist|regex_match[/^[A-Za-z]{3}$/]",
				'errors' => [
                    'regex_match'  => "Invalid product currency code.",
                ],
			],
		];
		$validation->setRules($rules);
	    $errors = [];
		if (!$validation->run($data)) {
            $errors = $validation->getErrors();
	    }
		$product = [];
		$productModel = new ProductModel();
		if(!isset($errors['product_id'])){
			$product = $productModel->where('status','1')->where('deleted_at','null')->where('id',$data['product_id'])->first();
			if(!$product){
				$errors['product_id'] = "Product not found";
				return $this->respond(['status' => 'failure','message' => $errors]);
			}
		}else{
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$updateData = [];
		if(!isset($errors['name']) && isset($data['name'])){
			if($product['name'] != $data['name']){
				$updateData['name'] = $data['name'];
			}
			
		}
		if(!isset($errors['description']) && isset($data['description'])){
			if($product['description'] != $data['description']){
				$updateData['description'] = $data['description'];
			}
		}
		if(!isset($errors['sku_id']) && isset($data['sku_id'])){
			if($product['sku_id'] != $data['sku_id']){
				$isSku = $productModel->where('sku_id', $data['sku_id'])->first();
				if($isSku){
					$errors['sku_id'] = "This sku_id already exist.";
				}else{
					$updateData['sku_id'] = $data['sku_id'];//no error in sku_id procced to update
				}
			}else {
				$errors['sku_id'] = "No change found in sku_id.";
			}
		}
		$currency_id = '';
		if(!isset($errors['amount']) && isset($data['amount'])){
			if(!isset($data['currency_code'])){
				$errors['currency_code'] = "If you want to change the amount you should provide currency_code.";
			}else if(!isset($errors['currency_code']) && isset($data['currency_code'])){
				$currencyModel = new CurrencyModel();
				$isCurCode = $currencyModel->where('code',$data['currency_code'])->first();
				if(!$isCurCode){
					$errors['currency_code'] = 'Product currency_code is not exist';
				}else{
					$currency_id = $isCurCode['currency_id'];
					$db = \Config\Database::connect();
					$price = $db->table('product_prices')->where('product_id',$data['product_id'])->where('currency_id',$currency_id)->orderBy('price_id','DESC')->limit(1)->get()->getResultArray();
					if($price){
						if($price[0]['price'] == $data['amount']){
							$errors['amount'] = "No change between previous and new amount.";
						}
					}
					unset($db);unset($price);
				}
				unset($isCurCode);
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		
		$user = auth()->user();
		$db = \Config\Database::connect();
		try{
			$flag = false;
			$db->transStart();
			if(!empty($updateData)){
				$updateData['updated_at'] = time();
				$db->table('product')->where('id',$data['product_id'])->update($updateData);
				$flag = true;
			}
			if(isset($data['amount']) &&  !empty($currency_id)){
				$db->table('product_prices')->insert(['product_id' => $data['product_id'], 'currency_id' => $currency_id, 'price' => $data['amount'], 'effective_from' => time(), 'created_by' => $user->id, 'created_at' => time()]);
				$flag = true;
			}
			$db->transComplete();
			if ($db->transStatus() === false) {
				log_message('error', "Product controller - update product - db update error1");
				return $this->respond(['status' => 'failure','message' => "Product update failed. Internal error."]);
			}
			if(!$flag){
				return $this->respond(['status' => 'failure','message' => "There is no data to update."]);
			}
			return $this->respond(['status' => 'success','message' => "Product details updated successfully."]);
		}catch (\Throwable $e) {
			$db->transRollback();
			log_message('error', "Product controller - update product - db update error2");
			return $this->respond(['status' => 'failure','message' => "Product details update failed. Internal error."]);
		}
	}
	public function delete_product(){
		$rawData = $this->request->getRawInput();
		$data = array_map('trim', $rawData);
		$validation = \Config\Services::validation();
		$rules = [
			'product_id' => [
				'label' => "Product  id",
				'rules' => "required|regex_match[/^\d{1,10}$/]",
				'errors' => [
					'required' => "Product id is required.",
                    'regex_match'  => "Invalid product id.",
                ],
			]
		];
		$validation->setRules($rules);
	    $errors = [];
		if (! $validation->run($data)) {
            $errors = $validation->getErrors();
	    }
		$product = [];
		$productModel = new ProductModel();
		if(!isset($errors['product_id'])){
			$product = $productModel->find($data['product_id']);
			if(!$product){
				$errors['product_id'] = "Products not found";
				return $this->respond(['status' => 'failure','message' => $errors]);
			}
		}else{
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		try{
			$productModel->update($data['product_id'],['deleted_at' => time(), 'updated_at' => time()]);
		}catch(\CodeIgniter\Database\Exceptions\DatabaseException $e){
			log_message('error', "Product controller - delete product - db delete error ".$e->getMessage());
			return $this->respond(['status' => 'failure','message' => "Product deletion failed."]);
		}
		return $this->respond(['status' => 'success','message' => "Product successfully deleted"]);
	}
}
