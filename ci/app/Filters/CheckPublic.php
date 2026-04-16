<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CheckPublic implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user_id            = 0;
        $this->userInfo		= \Joomla\CMS\Factory::getApplication()->getSession()->get('user');

        require(dirname(__DIR__).COOKIE);

        if( isset($this->userInfo->id) ){
            $user_id	= $this->userInfo->id;
        }

        if( $user_id == 0 ){
            return redirect()->route('Login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }    
}

