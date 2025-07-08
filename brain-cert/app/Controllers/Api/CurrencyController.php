<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\CurrencyModel;

class CurrencyController extends ResourceController
{
    protected $format = 'json';
	public function create_currency(){
		$rawData = $this->request->getPost();
		$data = array_map('trim', $rawData);
		$validation = \Config\Services::validation();
		$rules = [
			'name' => [
				'label' => "Currency Name",
				'rules' => "required|regex_match[/^[A-Za-z ]{1,50}$/]",
				'errors' => [
                    'required' => "Currency name is required.",
                    'regex_match'  => "Invalid Currency name.",
                ],
			],
			'code' => [
				'label' => "Currency code",
				'rules' => "required|regex_match[/^[A-Za-z]{3}$/]",
				'errors' => [
					'required' => "Currency code is required.",
                    'regex_match'  => "Invalid Currency code.",
                ],
			],
			'symbol' => [
				'label' => "Currency symbol",
				'rules' => "required|regex_match[/^\p{Sc}+$/u]",
				'errors' => [
					'required' => "Currency symbol is required.",
                    'regex_match'  => "Invalid currency symbol.",
                ],
			],
		];
		$validation->setRules($rules);
	    $errors = [];
		if (! $validation->run($data)) {
            $errors = $validation->getErrors();
	    }
		$currencyModel = new CurrencyModel();
		if(empty($errors)){
			$isCode = $currencyModel->where('code',$data['code'])
				->orWhere('symbol',$data['symbol'])
				->orWhere('name',$data['name'])
				->first();
			if($isCode){
				$errors['currency'] = 'Currency already exist';
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$user = auth()->user();
		if($currencyModel->insert(['name' => $data['name'], 'symbol' => $data['symbol'] ,'code' => strtoupper($data['code']) ,'created_by' => $user->id,'created_at' => time()],true)){
			return $this->respond(['status' => 'success','message' => "Currency created successfully."]);
		}else{
			log_message('error', "Currency controller - create currency - db insert error");
			return $this->respond(['status' => 'failure','message' => "Internal server error"],500);
		}
	}
}
