<?php

$aConfig = array();

// Your account credentials 
$aConfig['default']['email'] = 'beta@tagmanagerserver.com';                 // Only for beta testing on a development server!
$aConfig['default']['key'] = 'b5c74fef6ce04f6ec289403fcvu3Wbzvx4Q61cuzg2';  // Only for beta testing on a development server!


// default TMS configuration
$aConfig['default']['cookieHost'] = 'your-domain.com';          // your-domain.com
$aConfig['default']['cookieName'] = '_tms';                     // Name of your TMS clientID cookie 
$aConfig['default']['gtmContainerFile'] = 'GTM-Container/GTM-TEST.json';
$aConfig['default']['debugAuth'] = 'kztrvi65br76tonzu';         // allow debug output
$aConfig['default']['debugConsole'] = 0;                        // allow debug output
$aConfig['default']['debugMode'] = 0;                           // allow debug output
#$aConfig['default']['overWriteUserIP'] = '0.0.0.0';             // overwrite user IP address before sending to Google Analytics

// Example configuration for a multidomain setup with different parameters per hostname
$aConfig['www.tagmanagerserver.com']['cookieHost'] = 'tagmanagerserver.com';
$aConfig['www.tagmanagerserver.com']['gtmContainerFile'] = 'GTM-Container/GTM-MPXMP9M.json';
$aConfig['www.tagmanagerserver.com']['debugAuth'] = 'kztrvi65br76tonzu';
$aConfig['www.tagmanagerserver.com']['email'] = 'w.bartke@hokata.de';
$aConfig['www.tagmanagerserver.com']['key'] = 'lDboEznvxSxRqlvIB8DE1nSnvhnWtvu3Wbzvx4Q61c';
if(strpos($_SERVER['HTTP_REFERER'], '/demo/')){
    $aConfig['www.tagmanagerserver.com']['debugConsole'] = 1;
    $aConfig['www.tagmanagerserver.com']['debugAuth'] = 'demo';
}
    
# $aConfig['blog.tagmanagerserver.com']['cookieHost'] = 'tagmanagerserver.com';
# $aConfig['blog.tagmanagerserver.com']['gtmContainerFile'] = 'GTM-Container/blog_tms_com.json';

// Example configuration settings for localhost 
$aConfig['localhost']['cookieHost'] = 'localhost';
$aConfig['localhost']['gtmContainerFile'] = 'GTM-Container/GTM-TEST.json';
$aConfig['localhost']['email'] = 'beta@tagmanagerserver.com';
$aConfig['localhost']['key'] = 'b5c74fef6ce04f6ec289403fcvu3Wbzvx4Q61cuzg2';
if(strpos($_SERVER['HTTP_REFERER'], '/demo/')){
    $aConfig['localhost']['debugConsole'] = 1;
    $aConfig['localhost']['debugAuth'] = 'demo';
}

return $aConfig;

?>