<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;

class Malipo extends BaseController
{
    public function index(){
       
		$url    	= "https://bmc.lipaswitch.co.tz/malipo/aipros1";
		
		$myXML		= '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
		$myXML		.=  '<COMMAND>
							<TYPE>C2B</TYPE>
							<CUSTOMERMSISDN>786670758</CUSTOMERMSISDN>
							<MERCHANTMSISDN>780800125</MERCHANTMSISDN>
							<AMOUNT>1100</AMOUNT>
							<PIN></PIN>
							<REFERENCE>123456</REFERENCE>
							<REFERENCE1>12345671560</REFERENCE1>
							<REFERENCE2>11151111944</REFERENCE2>
						</COMMAND>';
				
		$sXML       = $this->Engine->docurl($url , $myXML);
		
		//var_dump($sXML);
		$xmlresponse	= new \SimpleXMLElement($sXML["data"]);
        var_dump($xmlresponse);

        echo $xmlresponse->STATUS."<br />";

        $status = $xmlresponse->STATUS;

        //$status = "200";

        if( $status  == "200"){
            $url    	= "https://bmc.lipaswitch.co.tz/malipo/aipros2";
		
            $myXML		= '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
            $myXML		.=  '<COMMAND>
                                <TYPE>C2B</TYPE>
                                <CUSTOMERMSISDN>786670758</CUSTOMERMSISDN>
                                <MERCHANTMSISDN>780800125</MERCHANTMSISDN>
                                <CUSTOMERNAME></CUSTOMERNAME>
                                <AMOUNT>50000</AMOUNT>
                                <PIN></PIN>
                                <REFERENCE>123456</REFERENCE>
                                <USERNAME></USERNAME>
                                <PASSWORD></PASSWORD>
                                <REFERENCE1>12345671560</REFERENCE1>
                                <REFERENCE2>11151111944</REFERENCE2>                               
                            </COMMAND>';
            
            $sXML       = $this->Engine->docurl($url , $myXML);

            var_dump($sXML);

        }
        
        die();

        $url    	= "https://bmc.lipaswitch.co.tz/malipo/aipros5";

        $myXML		= '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $myXML		.=  '<COMMAND>
                                <TYPE>BILLFETCH</TYPE>
                                <CUSTOMERMSISDN>685466727</CUSTOMERMSISDN>
                                <USERNAME></USERNAME>
                                <PASSWORD></PASSWORD>
                                <CUSTOMERREF>123456</CUSTOMERREF>
                            </COMMAND>';
        
        $sXML       = $this->Engine->docurl($url , $myXML);

        var_dump($sXML);        
		
    }

	public function aipros1(){
        doAipros1Validate(); //Validate
	}

	public function aipros2(){
        doAipros2Process(); //Process
	}    
	
	public function aipros3(){
        doAipros3Query(); //Query
	}

	public function aipros4(){
        doAipros4Fetch(); //Bill fetch
	}	    
	
	public function aipros5(){
        doAipros5Lookup(); //Lookup
	}	


}