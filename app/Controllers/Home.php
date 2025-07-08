<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Home extends BaseController
{
	use ResponseTrait;
    public function index(): string
    {
        return view('welcome_message');
    }
	public function api_login(){
		$data = $this->request->getPost();
		return $this->respond([
                'status' => 'success',
                'message' => 'Reached server '.json_encode($data)],200);
	}
	public function product_list(){
		return $this->respond([
                'status' => 'success',
                'message' => 'Reached server'],200);
	}
	
	public function product(){
		
	}
}
