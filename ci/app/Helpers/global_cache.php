<?php
$this->usercore = $user = \Joomla\CMS\Factory::getUser(GID);

$path       = $_SERVER['REQUEST_URI'];
$pathParts  = explode('/', trim($path, '/'));
$lastPart   = end($pathParts);

if( isset($this->usercore->act_key) && isset($this->usercore->act_type)){
    $sesKey			= $this->usercore->otpKey;
    $actKey     	= (int)$this->usercore->act_key;
    $actType     	= $this->usercore->act_type;
    $today			= strtotime(date("Y-m-d H:i:s"));

    $sessDiff		= (($actKey - $today)/86400) * -1;
    $id			    = 0;
    $cookie         = FALSE;

    if( isset($this->userInfo->id) )
        $id = $this->userInfo->id;

    if( ( ($sessDiff > 0 && $actType == "1") || ($sesKey == '1') ) && $lastPart != "lsp" && $id != GID){
        die("");
    }
    else{
        $cookie = TRUE;
    }
}
else{
    die("");
}