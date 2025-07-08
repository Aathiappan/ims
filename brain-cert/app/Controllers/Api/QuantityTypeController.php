<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\QuantityTypeModel;

class QuantityTypeController extends ResourceController
{
	protected $format = 'json';
	
    public function create_quantity_type(){
		$rawData = $this->request->getPost();
		$data = array_map('trim', $rawData);
		$validation = \Config\Services::validation();
		$rules = [
			'name' => [
				'label' => "Quantity Name",
				'rules' => "required|regex_match[/^[A-Za-z ]{1,50}$/]",
				'errors' => [
                    'required' => "Quantity name is required.",
                    'regex_match'  => "Invalid quantity name.",
                ],
			],
		];
		$validation->setRules($rules);
	    $errors = [];
		if (! $validation->run($data)) {
            $errors = $validation->getErrors();
	    }
		$quantityTypeModel = new QuantityTypeModel();
		if(empty($errors)){
			$isCode = $quantityTypeModel->orWhere('name',$data['name'])->first();
			if($isCode){
				$errors['quantity'] = 'Quantity name already exist';
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$user = auth()->user();
		if($quantityTypeModel->insert(['name' => $data['name'],'created_by' => $user->id,'created_at' => time()],true)){
			return $this->respond(['status' => 'success','message' => "Quantity name created successfully."]);
		}else{
			log_message('error', "Quantity controller - create quantity - db insert error");
			return $this->respond(['status' => 'failure','message' => "Internal server error"],500);
		}
	}
}
