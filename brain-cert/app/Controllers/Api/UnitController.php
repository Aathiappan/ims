<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UnitModel;

class UnitController extends ResourceController
{
	protected $format = 'json';
	
    public function create_unit(){
		$rawData = $this->request->getPost();
		$data = array_map('trim', $rawData);
		$validation = \Config\Services::validation();
		$rules = [
			'name' => [
				'label' => "Unit Name",
				'rules' => "required|regex_match[/^[A-Za-z ]{1,50}$/]",
				'errors' => [
                    'required' => "Unit name is required.",
                    'regex_match'  => "Invalid Unit name.",
                ],
			],
			'symbol' => [
				'label' => "Unit symbol",
				'rules' => "required|regex_match[/^[\p{L}\p{S}]{1,10}$/u]",
				'errors' => [
					'required' => "Unit symbol is required.",
                    'regex_match'  => "Invalid Unit symbol.",
                ],
			],
		];
		$validation->setRules($rules);
	    $errors = [];
		if (! $validation->run($data)) {
            $errors = $validation->getErrors();
	    }
		$unitModel = new UnitModel();
		if(empty($errors)){
			$isCode = $unitModel->where('symbol',$data['symbol'])->orWhere('name',$data['name'])->first();
			if($isCode){
				$errors['symbol'] = 'Unit already exist';
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$user = auth()->user();
		if($unitModel->insert(['name' => $data['name'], 'symbol' => $data['symbol'] ,'created_by' => $user->id,'created_at' => time()],true)){
			return $this->respond(['status' => 'success','message' => "Unit created successfully.","unit_id" => $unitModel->getInsertID()]);
		}else{
			log_message('error', "Unit controller - create Unit - db insert error");
			return $this->respond(['status' => 'failure','message' => "Internal server error"],500);
		}
	}
}
