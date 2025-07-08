<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\CategoryModel;

class CategoryController extends ResourceController
{
	protected $format = 'json';
	
	public function create_category(){
		$rawData = $this->request->getPost();
		$data = array_map('trim', $rawData);
		$validation = \Config\Services::validation();
		$rules = [
			'name' => [
				'label' => "Category Name",
				'rules' => "required|regex_match[/^[A-Za-z0-9\- ]{1,100}$/]",
				'errors' => [
                    'required' => "Category name is required.",
                    'regex_match'  => "Invalid category name.",
                ],
			],
			'description' => [
				'label' => "Category description",
				'rules' => "if_exist|min_length[3]|max_length[244]",
				'errors' => [
					'min_length' => "Category description minimum 3 characters required.",
                    'max_length' => "Category description 244 characters only allowed.",
                ],
			],
			'parent_id' => [
				'label' => "Parent Category Id",
				'rules' => "if_exist|regex_match[/^\d{1,10}$/]",
				'errors' => [
                    'regex_match'  => "Invalid parent_id.",
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
			$isCatExist = $categoryModel->where('name',$data['name'])->first();
			if($isCatExist){
				$errors['name'] = 'Category name is already exist';
			}
		}
		if(empty($errors) && isset($data['parent_id'])){
			$isParent = $categoryModel->where('category_id',$data['parent_id'])->first();
			if(!$isParent){
				$errors['parent_id'] = 'Category parent is not exist';
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		$user = auth()->user();
		
		if($categoryModel->insert(['name' => $data['name'], 'description' => $data['description'] ?? null,'parent_id' => $data['parent_id'] ?? null,'created_by' => $user->id,'created_at' => time()],true)){
			return $this->respond(['status' => 'success','message' => "Category created successfully.", "category_id" => $categoryModel->getInsertID()]);
		}else{
			log_message('error', "Catergory controller - create category - db insert error");
			return $this->respond(['status' => 'failure','message' => "Internal server error"],500);
		}
	}
	
	public function get_category($id = null){
		if($id != null){
			$data["category_id"] = trim($id);
			$validation = \Config\Services::validation();
			$rules = [
				'category_id' => [
					'label' => "Category Id",
					'rules' => "required|regex_match[/^\d{1,10}$/]",
					'errors' => [
						'required' => "Category Id is required.",
						'regex_match'  => "Invalid Category Id.",
					],
				],
			];
			$validation->setRules($rules);
			if (! $validation->run($data)) {
				return $this->respond(['status' => 'failure','message' => $validation->getErrors()]);
			}
			$db = \Config\Database::connect();
			$builder = $db->table('category c');
			$builder->select('c.category_id, c.name AS category_name, c.description AS description, p.name AS parent_name');
			$builder->join('category p', 'p.category_id = c.parent_id', 'left');
			$builder->where('c.category_id', $data["category_id"]);
			$category = $builder->get()->getRowArray();
			if($category){
				return $this->respond(['status' => 'success','message' => "Category found", "data" => $category]);
			}else{
				return $this->respond(['status' => 'failure','message' => "Category not found"]);
			}
			 
		}else{
			$db = \Config\Database::connect();
			$builder = $db->table('category c');
			$builder->select('c.category_id, c.name AS category_name, c.description AS description,p.name AS parent_name');
			$builder->join('category p', 'p.category_id = c.parent_id', 'left');
			$category = $builder->get()->getResultArray();
			//log_message('error', "Catergory controller - get category - error".json_encode($category));
			
			if($category){
				return $this->respond(['status' => 'success','message' => "Category found", "data" => $category]);
			}else{
				return $this->respond(['status' => 'success','message' => "Categories not found"]);
			}
		}
	}
	
	public function update_category(){
		$rawData = $this->request->getRawInput();
		$data = array_map('trim', $rawData);
		$validation = \Config\Services::validation();
		$rules = [
			'category_id' => [
				'label' => "Categor Id",
				'rules' => "required|regex_match[/^\d{1,10}$/]",
				'errors' => [
                    'required' => "category_id is required.",
                    'regex_match'  => "Invalid category_id.",
                ],
			],
			'name' => [
				'label' => "Categor Name",
				'rules' => "if_exist|regex_match[/^[A-Za-z0-9\- ]{1,100}$/]",
				'errors' => [
                    'regex_match'  => "Invalid category name.",
                ],
			],
			'description' => [
				'label' => "Category description",
				'rules' => "if_exist|min_length[3]|max_length[244]",
				'errors' => [
					'min_length' => "Category description minimum 3 characters required.",
                    'max_length' => "Category description 244 characters only allowed.",
                ],
			],
			'parent_id' => [
				'label' => "Parent Category Id",
				'rules' => "if_exist|regex_match[/^\d{1,10}$/]",
				'errors' => [
                    'regex_match'  => "Invalid parent_id.",
                ],
			],
		];
		$validation->setRules($rules);
		$errors = [];
		if (! $validation->run($data)) {
			$errors = $validation->getErrors();
		}
		$categoryModel = new CategoryModel();
		$updateData = [];
		if(empty($errors) && isset($data['name'])){
			$isCatExist = $categoryModel->where('name',$data['name'])->first();
			if($isCatExist){
				$errors['name'] = 'Category name is already exist';
			}
		}
		if(empty($errors) && isset($data['parent_id'])){
			if($data['category_id'] != $data['parent_id']){
				$isParent = $categoryModel->where('category_id',$data['parent_id'])->first();
				if(!$isParent){
					$errors['parent_id'] = 'Category parent is not exist';
				}else if($isParent["parent_id"] == $data['category_id']){
					$errors['parent_id'] = "category_id referring Category should not be parent for parent_id referring Category";
				}
			}else{
				$errors['parent_id'] = 'Category parent_id and category_id should not same.';
			}
		}
		if(!empty($errors)){
			return $this->respond(['status' => 'failure','message' => $errors]);
		}
		
		if(isset($data['name'])){
			$updateData["name"] = $data['name'];
		}
		if(isset($data['parent_id'])){
			$updateData["parent_id"] = $data['parent_id'];
		}
		if(isset($data['description'])){
			$updateData['description'] = $data['description'];
		}
		if(!empty($updateData)){
			try{
				$categoryModel->update($data['category_id'],$updateData);
			}catch(\CodeIgniter\Database\Exceptions\DatabaseException $e){
				log_message('error', "Catergory controller - update category - db update error ".$e->getMessage());
				return $this->respond(['status' => 'failure','message' => "Category details update failed."]);
			}
			return $this->respond(['status' => 'success','message' => "Category details updated"]);
		}else{
			return $this->respond(['status' => 'failure','message' => "There is no data to update."]);
		}
		
	}
	
}
