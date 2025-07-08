<?php
// app/Filters/ExpiringTokenAuth.php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Filters\TokenAuth as ShieldTokenAuth;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;
use CodeIgniter\Shield\Exceptions\AuthException;
use CodeIgniter\Shield\Models\PersonalAccessTokenModel;
use CodeIgniter\HTTP\Response;

class ExpiringTokenAuth extends ShieldTokenAuth
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = parent::before($request, $arguments);
		
		$authenticator = auth('tokens')->getAuthenticator();
		$user = auth()->user();
		
		
        // If parent didn't already reject it
        if ($user) {
			$token = $user->getAccessToken($authenticator->getBearerToken());
            if ($token instanceof AccessToken && $token->expires && $token->expires->getTimestamp() < time()) {
				$authenticator->logout();
				$user->revokeAccessToken($authenticator->getBearerToken());
                return service('response')
                    ->setStatusCode(Response::HTTP_UNAUTHORIZED)
                    ->setJSON(['message' => 'Token expired. Please login again.']);
            }
        }

        return $response;
    }
	
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
