<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ProductModel;

class InventoryController extends ResourceController
{
    public function inventory(){
		$rawData = $this->request->getRawInput();
		$data = array_map('trim', $rawData);
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
			'change_type' => [
				'label' => "Product inventory change type",
				'rules' => "required|in_list[add,remove,adjust,return,sale,transfer]",
				'errors' => [
                    'required'  => "Product inventory change type is required.",
					'in_list' => "This inventory change type is not allowed",
                ],
			],
			'operation' => [
				'label' => "Product inventory adjustment operation.",
				'rules' => "if_exist|in_list[increament, decrement]",
				'errors' => [
                    'in_list'  => "This inventory adjustment operation is not allowed",
                ],
			],
			'change_quantity' => [
				'label' => "Product inventory change quantity.",
				'rules' => "required|regex_match[/^\d{1,8}(\.\d{1,2})?$/]",
				'errors' => [
					'required' => "Product inventory change quantity is required.",
                    'regex_match'  => "Invalid product inventory change quantity.",
                ],
			],
			'inventory_note' => [
				'label' => "Inventory change note.",
				'rules' => "if_exist|min_length[3]|max_length[244]",
				'errors' => [
					'min_length' => "Inventory change note minimum 3 characters required.",
                    'max_length'  => "Inventory change note 244 characters only allowed.",
                ],
			],
		];
		$validation->setRules($rules);
	    $errors = [];
		if (! $validation->run($data)) {
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
		if(!isset($errors['change_type'])){
			if($data['change_type'] == 'adjust'){
				if(!isset($data['operation'])){
					$errors['operation'] = 'If you want to adjust inventory you should provide type of operation.';
				}
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$db = \Config\Database::connect();
		$preiousInventory = $db->table('product_stock_changes')->where('product_id', $data['product_id'])->orderBy('id','DESC')->limit(1)->get()->getRowArray();
		$previousVal = $preiousInventory ? $preiousInventory['current_quantity'] : 0;
		$operation = '';$currentVal = 0;$isDecrease = false;
		switch ($data['change_type']){
			case 'add':
				//add to stock
				$operation = 'increament';
				$currentVal = $previousVal + $data['change_quantity'];
				break;
			case 'remove':
				//remove from the stock. if product damaged
				$isDecrease = true;
				break;
			case 'adjust':
				//adjust stock if counting error by human
				$operation = $data['operation'];
				if($operation == 'increament'){
					$currentVal = $previousVal + $data['change_quantity'];
				}else{
					$isDecrease = true;
				}
				break;
			case 'return':
				//return by the customer
				$operation = 'increament';
				$currentVal = $previousVal + $data['change_quantity'];
				break;
			case 'sale':
				//sold the product
				$isDecrease = true;
				break;
			case 'transfer':
				//product transfer from one warehose to another
				$isDecrease = true;
				break;				
		}
		if($isDecrease){
			if($previousVal >= $data['change_quantity']){
				$operation = 'decrement';
				$currentVal = $previousVal - $data['change_quantity'];
			}else{
				$errors['change_quantity'] = "Current stock is less than change_quantity.";
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$user = auth()->user();
		$insertData = ['product_id' => $data['product_id'], 'change_type' => $data['change_type'], 'operation' => $operation,
		'change_quantity' => $data['change_quantity'], 'current_quantity' => $currentVal, 'changed_by' => $user->id, 'changed_at' => time()];
		if(isset($data['inventory_note'])){
			$insertData['note'] = $data['inventory_note'];
		}
		if($db->table('product_stock_changes')->insert($insertData)){
			return $this->respond(['status' => 'success','message' => "Inventory changed successfully."]);
		}else{
			log_message('error', "Inventory controller - inventory - db insert error2");
			return $this->respond(['status' => 'failure','message' => "Inventory change failed. Internal error."]);
		}
	}
	public function get_inventory(){
		$rawData = $this->request->getRawInput();
		$data = array_map('trim', $rawData);
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
	    $errors = [];
		if (! $validation->run($data)) {
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
		$db = \Config\Database::connect();
		$inventories = $db->table('product_stock_changes')->select('change_type,operation,change_quantity,current_quantity,note,changed_at AS time')->where('product_id',$data['product_id'])->orderBy('id','DESC')->get()->getResultArray();
		if($inventories){
			$builder = $db->table('product_quantity_unit pqu')->select('qt.name AS quantity_name,u.symbol AS symbol');
			$builder->join('quantity_type qt', 'qt.id = pqu.quantity_type_id', 'left');
			$builder->join('unit u', 'u.unit_id = pqu.unit_id', 'left');
			$builder->where('pqu.product_id', $data["product_id"]);
			$productMetrics = $builder->get()->getRowArray();
			$outData['product_name'] = $product['name'];
			$outData['quantity_name'] = $productMetrics['quantity_name'];
			$outData['quantity_unit'] = $productMetrics['symbol'];
			$outData['inventories'] = $inventories;
			return $this->respond(['status' => 'success','message' => "Inventory found", "data" => $outData]);
		}else{
			return $this->respond(['status' => 'failure','message' => 'Inventory not found']);
		}
	}
}
