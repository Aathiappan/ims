<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Models\PersonalAccessTokenModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Models\UserIdentityModel;

class AuthApiController extends ResourceController
{
    protected $format = 'json';

    public function login()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($email) || empty($password)) {
            return $this->failValidationErrors(['email and password are required']);
        }

        // Get the user
        $user = model(UserModel::class)->findByCredentials(['email' => $email]);

        if (! $user || !password_verify($password, $user->password_hash)) {
            return $this->failUnauthorized('Invalid email or password.');
        }

        // Check if active and allowed
        if (! $user->isActivated()) {
            return $this->failForbidden('Account is not active.');
        }

        // Manually log the user in
        auth()->login($user);
		//$expiresAt = Time::now()->addHours(1);
		$expiresAt = Time::parse('2025-07-01 12:00:00');
        // Create a token (Personal Access Token or JWT if you're using one)
        //$token = auth()->user()->generateAccessToken('api-token',['admin'],$expiresAt);
		$user = auth()->user();
		$token = $user->generateAccessToken('api-token');
		//$model = model(AccessTokenModel::class);
		$expoires_at = Time::now()->addHours(1);
		$model = new UserIdentityModel();
		$model->update($token->id, ['expires' => $expoires_at]);
		

        return $this->respond([
			'status' => 'success',
            'message' => 'Login successful',
            'access_token' => $token->raw_token,     // For client use
            'expires_at' => $expoires_at->format('Y-m-d H:i:s') ,
			'timezone' => Time::now()->getTimezone()->getName(),
        ]);
    }
	
	public function logout()
	{
		$rawToken = $this->request->getHeaderLine('Authorization');
		$rawToken = explode(' ',$rawToken);
		$user = auth()->user();
		if ($user && $rawToken) {
			$user->revokeAccessToken($rawToken[1]);
			//$user->revokeAllAccessTokens();
			return $this->respond([
				'status' => 'success',
				'message' => 'Successfully logged out.',
			]);
		}
		return $this->respond(['message' => 'Invalid token or user.'],401);
	}
}
