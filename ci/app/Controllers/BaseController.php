<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ["global"];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        date_default_timezone_set('Africa/Dar_es_Salaam'); // Added user timezone

        register_ci_instance($this);// This enables access of $this globally e.g in Helper as $Ci
		
        $this->groupsArr    = array();
        $this->Profile      = new \stdClass();
        $this->lang_code    = "eng";
        //$cookie             = FALSE;
		$this->request		= service('request');
        $this->ipAddress    = $this->request->getIPAddress();

        $this->router       = service('router');
        $this->crController = $this->router->controllerName();
        $this->crMethod     = $this->router->methodName();
        
		$this->Engine 		= new \App\Models\Engine();

		$this->userInfo		= \Joomla\CMS\Factory::getApplication()->getSession()->get('user');
		$this->userSession	= \Joomla\CMS\Factory::getApplication()->getSession();

        require(dirname(__DIR__).COOKIE);
        $this->Engine->verifyCookie( $cookie );
        
        if( !empty($this->userInfo->groups) ){
            $this->groupsArr     = $this->userInfo->groups;
            $this->Profile	    = $this->Engine->fetchUserDetails($this->userInfo->id);
        }     

        $this->permitICTManager       = FALSE;
        $this->permitFinanceManager   = FALSE;
        $this->permitGLOBALManager    = FALSE;
        $this->permitSuperUser        = FALSE;

        if( isset($this->Profile->lang_code) ){
            $this->lang_code = $this->Profile->lang_code;
        }

        $this->sesionLanguage         = $this->Engine->getLanguages()[$this->lang_code];
        $this->sesionRolesArray       = $this->Engine->getAllUserRoles($this->groupsArr);
        $this->globalCookies          = $this->Engine->getGlobalCookies();
        $this->statusCookie           = $this->Engine->getStatusCookies($this->globalCookies , $this->crMethod);

        if( in_array(10, $this->groupsArr) ){
            $this->permitICTManager = TRUE;
        }

        if( in_array(12, $this->groupsArr) ){
            $this->permitFinanceManager = TRUE;
        }
        
        if( in_array(11, $this->groupsArr) ){
            $this->permitGLOBALManager = TRUE;
        }
        
        if( in_array(8, $this->groupsArr) ){
            $this->permitSuperUser = TRUE;
        }

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }
}
