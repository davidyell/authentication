<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Authenticator;

use Authentication\Result;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Token Authenticator
 *
 * Authenticates an identity based on a token in a query param or the header.
 */
class TokenAuthenticator extends AbstractAuthenticator
{

    /**
     * Checks if the token is in the headers or a request parameter
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return string|null
     */
    protected function _getToken(ServerRequestInterface $request)
    {
        $header = $this->config('header');
        if (!empty($header)) {
            return $request->getHeaderLine($header);
        }

        $param = $this->config('queryParam');
        $queryParams = $request->getQueryParams();
        if (isset($queryParams[$param])) {
            return $queryParams[$param];
        }

        return null;
    }

    /**
     * Authenticates the identity contained in a request. Will use the `config.userModel`, and `config.fields`
     * to find POST data that is used to find a matching record in the `config.userModel`. Will return false if
     * there is no post data, either username or password is missing, or if the scope conditions have not been met.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @param \Psr\Http\Message\ResponseInterface $response Unused response object.
     * @return \Authentication\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response)
    {
        $token = $this->_getToken($request);
        if (empty($token)) {
            return new Result(null, Result::FAILURE_OTHER);
        }

        $user = $this->identifiers()->identify(['token' => $token]);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->identifiers()->getErrors());
        }

        return new Result($user, Result::SUCCESS);
    }
}
