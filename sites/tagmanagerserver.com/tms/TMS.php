<?php

/**
 * Tag Manager Server
 *
 * @link https://www.tagmanagerserver.com
 * @copyright 2020 Wolfram Bartke (w.bartke@tagmanagerserver.com)
 *
 * @package TagManagerServer
 */


class TMS {
	
	function __construct() {

		// Set this master UA-ID to overwrite all GTM-Container UA-IDs, good idea for testing.
		$this->sServer_UA_ID = '';			// Example: 'UA-12345678-9'

		$this->sVersion = 'v0.3.5';			// Script Version
		header('TMS: '.$this->sVersion); 	// 

		$this->aConfig = include 'config.php';
		
		$sHTTP_HOST = $_SERVER['HTTP_HOST'];

		// use the forwarded host if exists, for example with htaccess proxy in subfolder setup
		if(isset($_SERVER['HTTP_X_FORWARDED_SERVER'])) 						
			$sHTTP_HOST = $_SERVER['HTTP_X_FORWARDED_SERVER'];

		$this->sCookieHost = '';					// hostname for cookies
		$this->sGTM_ContainerFile = '';				// Google Tag Manager JSON-Export container relative path and filename
		$this->sCookieName = '_tms'; 				// Name of your TMS clientID cookie 
		$this->iMaxTabPageEventTimeout = 2*60; 		// Timeout for previous page tracking events in the same browser tab to remove expired datalayer data in server session
		$this->sDebugAuth = '';						// debug auth code must match to config file
		$this->bDebugConsole = 0;					// debug output
		$this->bDebugMode = 0;						// set GTM debug mode
		$this->sDebugPreview = '';					// set GTM preview container
		$this->sOverWriteUserIP = '';				// overwrite user IP address before sending to Google Analytics
		$this->aWaitingEEC = NULL;					// collected Enhanced Ecommerce data waiting for submission to Google analytics 
		$this->aTrackingInfo = array();				// Google Analytics Measurement Protocol URL
		$this->bNoGTM = false;						// Tag Manager Server Integration without GTM Integration, so the gtm.js event might be missing 
		$this->sLicenseEmail = '';					// 
		$this->sLicenseKey = '';					// 


		// set default configuration
		$this->TMS_SetConfig($this->aConfig['default']);

		// overwrite default setting with special host configuration
		if(isset($this->aConfig[$sHTTP_HOST]))
			$this->TMS_SetConfig($this->aConfig[$sHTTP_HOST]);

		if(empty($this->sCookieHost))
			die('No cookie host set found for: '.$sHTTP_HOST);
		
		if(empty($this->sGTM_ContainerFile))
			die('No TMS-Container found for: '.$sHTTP_HOST);

		// existing or new client id
		$this->CID = $this->TMS_Get_CID(); 			
		
		// predefined system variables
		// Usage in Google Tag Manager: define a constant with any variable name and use TMS_SystemVars name as value
		$this->TMS_SystemVars = array();
		$this->TMS_SystemVars['TMS_CLID'] = $this->CID;							// GA client ID
		$this->TMS_SystemVars['TMS_TIMESTAMP'] = date('c'); 					// ISO 8601 date
		$this->TMS_SystemVars['TMS_USERAGENT'] = $_SERVER['HTTP_USER_AGENT'];	// User agent of the visitor browser
		$this->TMS_SystemVars['TMS_SESSIONID'] = $this->TMS_Get_SessionID(); 	// GA Session ID for session scope custom dimension (Example: 1591796057.bF6XNLuB)
		$this->TMS_SystemVars['TMS_CONTAINER'] = $this->sGTM_ContainerFile;		// GTM Container filename
		
		$this->TMS_SetCookieHeader();
		$this->TMS_Container_Import();
	}

	/**
     * overwrite TMS setting from config
     *
     * @return nothing
     */
	function TMS_SetConfig($aConfigHost){
		$this->aCache[] = $aConfigHost;
		$this->sCookieHost = !empty($aConfigHost['cookieHost']) ? $aConfigHost['cookieHost'] : $this->sCookieHost;
		$this->sGTM_ContainerFile = !empty($aConfigHost['gtmContainerFile']) ? $aConfigHost['gtmContainerFile'] : $this->sGTM_ContainerFile;
		$this->sCookieName = !empty($aConfigHost['cookieName']) ? $aConfigHost['cookieName'] : $this->sCookieName;
		$this->iMaxTabPageEventTimeout = (!empty($aConfigHost['maxTabPageEventTimeout']) && is_integer($aConfigHost['maxTabPageEventTimeout'])) ? $aConfigHost['maxTabPageEventTimeout'] : $this->iMaxTabPageEventTimeout; 
		$this->sDebugAuth = !empty($aConfigHost['debugAuth']) ? $aConfigHost['debugAuth'] : $this->sDebugAuth;
		$this->bDebugConsole = !empty($aConfigHost['debugConsole']) ? $aConfigHost['debugConsole'] : $this->bDebugConsole;
		$this->bDebugMode = !empty($aConfigHost['debugMode']) ? $aConfigHost['debugMode'] : $this->bDebugMode;
		$this->sOverWriteUserIP = !empty($aConfigHost['overWriteUserIP']) ? $aConfigHost['overWriteUserIP'] : $this->sOverWriteUserIP;
		$this->sLicenseEmail = !empty($aConfigHost['email']) ? $aConfigHost['email'] : $this->sLicenseEmail;
		$this->sLicenseKey = !empty($aConfigHost['key']) ? $aConfigHost['key'] : $this->sLicenseKey;
	}
	
	/**
     * cookie headers are necessary for tracking via a subdomain
     *
     * @return bool true if there is a HTTP_REFERER
     */
	function TMS_SetCookieHeader(){
		if(!empty($_SERVER['HTTP_REFERER'])){
			$aRefUrl = parse_url($_SERVER['HTTP_REFERER']);
			if($this->sCookieHost == substr($aRefUrl['host'], -strlen($this->sCookieHost))){
				header('Access-Control-Allow-Credentials: true');
				header('Access-Control-Allow-Origin: '.$aRefUrl['scheme'].'://'.$aRefUrl['host']);
				return true;
			}
		}
		return false;
	}

	/**
     * check debug auth and set debug mode
     *
	 * @param array $aDebug debug values from dataLayer push event
     * @return nothing
     */
	function TMS_OverwriteDebugSettings($aDebug){
		if(!empty($aDebug['auth']) && $aDebug['auth'] == $this->sDebugAuth){
			$this->bDebugMode = (isset($aDebug['mode']) ? $aDebug['mode'] : $this->bDebugMode);
			$this->bDebugConsole = ( (isset($aDebug['console']) || $this->bDebugMode) ? $aDebug['console'] : $this->bDebugConsole);
			
			$this->sDebugPreview = ( isset($aDebug['preview']) ? $aDebug['preview'] : $this->sDebugPreview);
			if(!empty($this->sDebugPreview) && is_file($this->sDebugPreview)){
				$this->sGTM_ContainerFile = $this->sDebugPreview;
				$this->TMS_Container_Import();
			}
				
		}else{
			$this->sDebugAuth = 'invalid';
		}
	}
	
	/**
     * Import the Tag Manager Container JSON file
     *
     * @return bool true after successful import
     */
	function TMS_Container_Import(){

		// translation for predefined GTM dataLayer push event Names
		$this->aEventTypeTransform = array(
			'gtm.js' => 'gtm.js',
			'PAGEVIEW' => 'gtm.js',
			'DOM_READY' => 'gtm.dom',
			'WINDOW_LOADED' => 'gtm.load'
		);

		// load imported container, with all supported GTM-Container elements, from cache
		$this->aTrackingContainer = $this->TMS_LoadContainerCache($this->sGTM_ContainerFile);
		if($this->aTrackingContainer !== false){
			return true;
		
		// start container import if container-file exits
		}else if(is_file($this->sGTM_ContainerFile)){
			$aTMS_Container = json_decode(file_get_contents($this->sGTM_ContainerFile), true);
			if(empty($aTMS_Container))
				die('GTM container file '.dirname($_SERVER['REQUEST_URI']).'/'.$this->sGTM_ContainerFile.' no valid JSON string.');

		// stop the import and show a message
		}else{
			die('GTM container file '.dirname($_SERVER['REQUEST_URI']).'/'.$this->sGTM_ContainerFile.' not found.');
		}

		$aTrackingContainer = array();
		
		// add gtm.js as initial dataLayer push event
		$aInitEvent= $this->TMS_GetInitEvent();
		$aTrackingContainer['triggerIdName'][$aInitEvent['id']]['name'] = $aInitEvent['name'];
		$aTrackingContainer['triggerIdName'][$aInitEvent['id']]['type'] = $aInitEvent['type'];
		$aTrackingContainer['trigger'][$aInitEvent['type']][$aInitEvent['name']]['id'] = $aInitEvent['id'];
		$aTrackingContainer['trigger'][$aInitEvent['type']][$aInitEvent['name']]['type'] = $aInitEvent['type'];
		
		// collect all trigger with predefined push event names
		if(is_array($aTMS_Container['containerVersion']['trigger'])){
			foreach($aTMS_Container['containerVersion']['trigger'] as $aTrigger){
				if($aTrigger['type'] == 'DOM_READY' || $aTrigger['type'] == 'PAGEVIEW' || $aTrigger['type'] == 'WINDOW_LOADED' || $aTrigger['type'] == 'CUSTOM_EVENT'){
					$aTrackingContainer['triggerIdName'][$aTrigger['triggerId']]['name'] = $aTrigger['name'];
					$aTrackingContainer['triggerIdName'][$aTrigger['triggerId']]['type'] = $this->TMS_DataLayerPushEventTypeTransform($aTrigger['type']);
					
					$aTrackingContainer['trigger'][$this->TMS_DataLayerPushEventTypeTransform($aTrigger['type'])][$aTrigger['name']]['id'] = $aTrigger['triggerId'];
					$aTrackingContainer['trigger'][$this->TMS_DataLayerPushEventTypeTransform($aTrigger['type'])][$aTrigger['name']]['tags'] = array();
					if(isset($aTrigger['filter']))
						$aTrackingContainer['trigger'][$this->TMS_DataLayerPushEventTypeTransform($aTrigger['type'])][$aTrigger['name']]['filter'] = $aTrigger['filter'];
					if(isset($aTrigger['customEventFilter']))
						$aTrackingContainer['trigger'][$this->TMS_DataLayerPushEventTypeTransform($aTrigger['type'])][$aTrigger['name']]['customEventFilter'] = $aTrigger['customEventFilter'];
				}
			}
		}


		// collect all supported tags
		if(is_array($aTMS_Container['containerVersion']['tag'])){
			foreach($aTMS_Container['containerVersion']['tag'] as $aTag){
				
				// ignore paused tags
				if(!empty($aTag['paused']))
					continue;

				if($aTag['type'] == 'ua'){  // Collection of Universal Analytics Tags
					$aTemp = array();
					$aTemp['name'] = $aTag['name'];
					$aTemp['trackType'] = '';
					$aTemp['trigger'] = $aTag['firingTriggerId'];
					if(isset($aTag['blockingTriggerId']))
						$aTemp['blockingTrigger'] = $aTag['blockingTriggerId'];

					// link trigger with tags
					foreach($aTag['firingTriggerId'] as $iTriggerId){
						if(isset($aTrackingContainer['triggerIdName'][$iTriggerId])){
							$sTriggerType = $aTrackingContainer['triggerIdName'][$iTriggerId]['type'];
							$sTriggerName = $aTrackingContainer['triggerIdName'][$iTriggerId]['name'];
							$aTrackingContainer['trigger'][$sTriggerType][$sTriggerName]['tags'][] = $aTag['name'];
						}
					}

					// parse supported tag parameter
					$aTemp['parameter'] = $this->TMS_Container_ParseParameter($aTag['parameter']);
					$aTemp['trackType'] = $aTemp['parameter']['TEMPLATE']['trackType'];
					$aTrackingContainer['tag']['ua'][$aTag['name']] = $aTemp;
					
				}
			}
			$aTrackingContainer['index'][] = 'init';
		}

		// collect all supported variables
		if(is_array($aTMS_Container['containerVersion']['variable'])){
			foreach($aTMS_Container['containerVersion']['variable'] as $aVariable){
				$aTemp = array();
				
				// dataLayer variable (only dataLayer Version 2 supported)
				if($aVariable['type'] == 'v' && $this->TMS_Container_FindParameter($aVariable['parameter'], 'dataLayerVersion') == '2'){
					$aTemp['name'] = $aVariable['name'];
					$aTemp['type'] = $aVariable['type'];
					$aTemp['parameter'] = $this->TMS_Container_ParseParameter($aVariable['parameter']);

					$aTrackingContainer['variable'][$aVariable['name']] = $aTemp;
				
				// KONSTANTE
				}else if($aVariable['type'] == 'c'){
					$aTemp['name'] = $aVariable['name'];
					$aTemp['type'] = $aVariable['type'];
					$aTemp['parameter'] = $this->TMS_Container_ParseParameter($aVariable['parameter']);
					$aTrackingContainer['variable'][$aVariable['name']] = $aTemp;
				
				// Var Type First-Party-Cookie
				}else if($aVariable['type'] == 'k'){
					$aTemp['name'] = $aVariable['name'];
					$aTemp['type'] = $aVariable['type'];
					$sCookieName = $this->TMS_Container_ParseParameter($aVariable['parameter']);
					$aTemp['parameter'] = (isset($_COOKIE[$sCookieName]) ? $_COOKIE[$sCookieName] : '');
					$aTrackingContainer['variable'][$aVariable['name']] = $aTemp;
				
				// Lookup Table
				}else if($aVariable['type'] == 'smm'){
					$aTemp['name'] = $aVariable['name'];
					$aTemp['type'] = $aVariable['type'];
					$aTemp['parameter'] = $this->TMS_Container_ParseParameter($aVariable['parameter']);
					$aTrackingContainer['variable'][$aVariable['name']] = $aTemp;

				// Google Analytics Settings
				}else if($aVariable['type'] == 'gas'){
					$aTemp['name'] = $aVariable['name'];
					$aTemp['type'] = $aVariable['type'];
					$aTrackingContainer['index'][] = md5($this->aCache[0]['key']);
					if(isset($this->aCache[1]) && isset($this->aCache[1]['key']))
						$aTrackingContainer['index'][] = md5($this->aCache[1]['key']);
					foreach($aVariable['parameter'] as $aParam){
						if($aParam['type'] == 'LIST'){
							foreach($aParam['list'] as $aList){
								if($aParam['key'] == 'fieldsToSet'){
									$sKey = $this->TMS_Container_FindParameter($aList['map'], 'fieldName');
									$sValue = $this->TMS_Container_FindParameter($aList['map'], 'value');
									$aTemp['parameter'][$aParam['type']][$aParam['key']][$sKey] = $sValue;
								}else if($aParam['key'] == 'dimension'){
									$sIndex = $this->TMS_Container_FindParameter($aList['map'], 'index');
									$sDimension = $this->TMS_Container_FindParameter($aList['map'], 'dimension');
									$aTemp['parameter'][$aParam['type']][$aParam['key']][$sIndex] = $sDimension;
								}
								
							}
						}else if($aParam['type'] == 'BOOLEAN'){
							$aTrueFalse = array('false' => false, 'true' => true);
							$aTemp['parameter'][$aParam['type']][$aParam['key']] = $aTrueFalse[$aParam['value']];
						}else{
							$aTemp['parameter'][$aParam['type']][$aParam['key']] = $aParam['value'];
						}
					}
					$aTrackingContainer['variable'][$aVariable['name']] = $aTemp;
				
				// URL
				}else if($aVariable['type'] == 'u'){
					$aTemp['name'] = $aVariable['name'];
					$aTemp['type'] = $aVariable['type'];
					$aTemp['parameter'] = $this->TMS_Container_ParseParameter($aVariable['parameter']);

					$sVarComponent = $this->TMS_Container_FindParameter($aVariable['parameter'], 'component');
//////// hier weiter!!!!!
					$sUrlSource = $this->TMS_Container_FindParameter($aVariable['parameter'], 'customUrlSource');
					if($sUrlSource === false){
						$sUrlSource = $aTrackingContainer['variable']['PAGE_URL'];
					}

					if($sVarComponent == 'URL'){

					}

					if($this->aTrackingContainer['variable'][$sVarName]['type'])
					$this->TMS_Container_ParseParameter($aVariable['parameter']);
					

/*
{
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "23",
                "name": "URL - Vollständige URL",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "URL"
                    }
                ],
                "fingerprint": "1589643737265",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "24",
                "name": "URL - Vollständige URL 2",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "URL"
                    },
                    {
                        "type": "TEMPLATE",
                        "key": "customUrlSource",
                        "value": "{{DL pageType}}"
                    }
                ],
                "fingerprint": "1589644061345",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "25",
                "name": "URL - Protokoll",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "PROTOCOL"
                    }
                ],
                "fingerprint": "1589644092563",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "26",
                "name": "URL - Hostname",
                "type": "u",
                "parameter": [
                    {
                        "type": "BOOLEAN",
                        "key": "stripWww",
                        "value": "false"
                    },
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "HOST"
                    }
                ],
                "fingerprint": "1589644122163",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "27",
                "name": "URL - Port",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "PORT"
                    }
                ],
                "fingerprint": "1589644188452",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "28",
                "name": "URL - Pfad",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "PATH"
                    },
                    {
                        "type": "LIST",
                        "key": "defaultPages",
                        "list": [
                            {
                                "type": "TEMPLATE",
                                "value": "index.html"
                            }
                        ]
                    }
                ],
                "fingerprint": "1589644369422",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "29",
                "name": "URL - Hostname ohne www",
                "type": "u",
                "parameter": [
                    {
                        "type": "BOOLEAN",
                        "key": "stripWww",
                        "value": "true"
                    },
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "HOST"
                    }
                ],
                "fingerprint": "1589644401698",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "30",
                "name": "URL - Datei-Endung",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "EXTENSION"
                    }
                ],
                "fingerprint": "1589644511921",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "31",
                "name": "URL - Suchanfrage",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "QUERY"
                    },
                    {
                        "type": "TEMPLATE",
                        "key": "queryKey",
                        "value": "get_parameter"
                    }
                ],
                "fingerprint": "1589644561607",
                "formatValue": {}
            },
            {
                "accountId": "136157975",
                "containerId": "30811141",
                "variableId": "32",
                "name": "URL - Fragment",
                "type": "u",
                "parameter": [
                    {
                        "type": "TEMPLATE",
                        "key": "component",
                        "value": "FRAGMENT"
                    }
                ],
                "fingerprint": "1589644630390",
                "formatValue": {}
			}
		}}}
			*/



				}
			}
		}
		
		// build in variables
		if(isset($aTMS_Container['containerVersion']['builtInVariable'])){
			foreach($aTMS_Container['containerVersion']['builtInVariable'] as $aVariable){
				$aTemp['name'] = $aVariable['name'];
				$aTemp['type'] = $aVariable['type'];
				$aTemp['parameter'] = '';
				$aTrackingContainer['variable'][$aVariable['name']] = $aTemp;
			}
		}

		// Check license information from TMS server (only once a day or after an update)
		// Hint: If you manipulate this license check you and your customer will be banned to use the TMS in the future
		$this->aTrackingInfo['message']['license'] = $this->TMS_CheckLicense();
		
		// write imported GTM-container, with all supported GTM-Container elements, to cache
		$this->TMS_WriteContainerCache($this->sGTM_ContainerFile.'.cache', $aTrackingContainer);
		// load container from cache aganin to avoid JSON interpretation differences.
		$this->aTrackingContainer = $this->TMS_LoadContainerCache($this->sGTM_ContainerFile);
		$this->TMS_PrepareMeasurementProtocol($this->aTrackingContainer);

		return true;
	}

	/**
     * Load reduced Tag Manager Container from cache
     *
     * @return array Tag Manager Container from cache
     */
	function TMS_LoadContainerCache($sFile){
		$sFileCache = $sFile.'.cache';
		if(is_file($sFileCache) && filemtime($sFile) < filemtime($sFileCache) && filemtime($sFileCache) > strtotime("-1 day")){
			$sCache = file_get_contents($sFileCache);
			if(($sCache = json_decode($sCache, true)) !== NULL)
				return $sCache;
		}
		return false;
	}

	/**
     * Write reduced Tag Manager Container to cache
     *
     * @return bool true if successful written
     */
	function TMS_WriteContainerCache($sFile, $aTrackingContainer){
		if(file_put_contents($sFile , json_encode($aTrackingContainer))){
			return true;
		}
			
		return false;
	}
	
	/**
     * Return gtm.js as initial dataLayer push event
     *
     * @return array initial dataLayer push event
     */
	function TMS_GetInitEvent(){
		$aInitEvent['id'] = '2147479553'; // the value is not documented, but has never changed 
		$aInitEvent['name'] = 'gtm.js';
		$aInitEvent['type'] = 'gtm.js';
		return $aInitEvent;
	}
	
    /**
     * Translation of predefined GTM dataLayer push event names
     *
     * @param string $sPushEventType
     * @return string 'event' or translated predefined event name
     */
	function TMS_DataLayerPushEventTypeTransform($sPushEventType){
		if(isset($this->aEventTypeTransform[$sPushEventType]))
			return $this->aEventTypeTransform[$sPushEventType];
		
		return 'event';
	}
	
    /**
     * Find parameter in tags and variables
     *
     * @param array $aParameter with all tag parameters
     * @return bool|string false or appropriate parameter
     */
	function TMS_Container_FindParameter($aParameter, $sSearchParameter){
		foreach($aParameter as $sParam){
			if($sParam['key'] == $sSearchParameter)
				return $sParam['value'];
		}
		return false;
	}
	
    /**
     * Parse tag and variable parameters
     *
     * @param array $aParameter with all tag or variable parameters
     * @return array parsed parameter
     */
	function TMS_Container_ParseParameter($aParameter){
		$aParamReturn = array();
		foreach($aParameter as $aParam){
			if($aParam['type'] == 'LIST'){
				foreach($aParam['list'] as $aList){
					$sKey = $this->TMS_Container_FindParameter($aList['map'], 'fieldName');
					if($sKey === false)
						$sKey = $this->TMS_Container_FindParameter($aList['map'], 'key');
					$sValue = $this->TMS_Container_FindParameter($aList['map'], 'value');
					$aParamReturn[$aParam['type']][$aParam['key']][$sKey] = $sValue;
				}
			}else{
				$aParamReturn[$aParam['type']][$aParam['key']] = $aParam['value'];
			}
		}
		return $aParamReturn;
	}
	
    /**
     * Recursive function to get nested variable content 
     *
     * @param string $sVar value of a variable
     * @param array $aDataLayer current dataLayer
     * @return string resolved content of nested variable
     */
	function TMS_GetVarValue($sVar, $aDataLayer){

		// check nested variables and return final value
		preg_match_all('/{{[^}}}]*}}/', $sVar, $aVarNamesMatches);
		if(!isset($aVarNamesMatches[0]) && !count($aVarNamesMatches[0])){
			return $sVar;
		}
		
		$aVarSearch = array();
		$aVarReplace = array();
		
		foreach($aVarNamesMatches[0] as $sMatch){
			
			$sVarName = substr($sMatch, 2, -2);

			if(isset($this->aTrackingContainer['variable'][$sVarName])){
				
				// Var Type v
				if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'v'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					$sVarValue = NULL;
					
					if(isset($this->aTrackingContainer['variable'][$sVarName]['parameter']['TEMPLATE'])
						&& isset($this->aTrackingContainer['variable'][$sVarName]['parameter']['INTEGER'])
						&& isset($this->aTrackingContainer['variable'][$sVarName]['parameter']['INTEGER']['dataLayerVersion'])
						&& $this->aTrackingContainer['variable'][$sVarName]['parameter']['INTEGER']['dataLayerVersion'] == 2
					){

						$sVarDataLayer = $this->aTrackingContainer['variable'][$sVarName]['parameter']['TEMPLATE']['name'];
						$aDataLayerPath = explode('.', $sVarDataLayer);
						
						$sVarValue = $this->TMS_GetDataLayerData($aDataLayerPath, $aDataLayer);
						
						if(is_null($sVarValue))
							$sVarValue = $this->TMS_GetParameterGetDefault($this->aTrackingContainer['variable'][$sVarName]['parameter'], $aDataLayer);
					}
					$aVarReplace[] = $sVarValue;
				
				// Var Type build_in
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'builtInVariable'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					$aVarReplace[] = $this->aTrackingContainer['variable'][$sVarName]['value'];
					
				// Var Type URL
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'u'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					$aVarReplace[] = $this->aTrackingContainer['variable'][$sVarName]['value'];

				// Var Type First-Party-Cookie (only works if the script runs on the same server as the website or shop)
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'k'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					
					$sValue = '';
					if($this->aTrackingContainer['variable'][$sVarName]['parameter']['BOOLEAN']['decodeCookie'] == 'true')
						$sValue = urldecode($sValue);
					
					$aVarReplace[] = $sValue;

				// undefined ??? 
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'undefined'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					$aVarReplace[] = '';
					
				// Constant
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'c'){
					$aVarSearch[] = '{{'.$sVarName.'}}';

					if(isset($this->TMS_SystemVars[$this->aTrackingContainer['variable'][$sVarName]['parameter']['TEMPLATE']['value']])){
						$aVarReplace[] = $this->TMS_SystemVars[$this->aTrackingContainer['variable'][$sVarName]['parameter']['TEMPLATE']['value']];
					}else{
						$aVarReplace[] = $this->TMS_GetVarValue($this->aTrackingContainer['variable'][$sVarName]['parameter']['TEMPLATE']['value'], $aDataLayer);
					}
					
				// RegEx Table
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'RegEx-Tabelle'){
						
				// Lookup Table
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'smm'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					$sInput = $this->TMS_GetVarValue($this->aTrackingContainer['variable'][$sVarName]['parameter']['TEMPLATE']['input'], $aDataLayer);

					// do it like the GTM
					if($sInput === true)
						$sInput = 'true';
					if($sInput === false)
						$sInput = 'false';

					if(isset($this->aTrackingContainer['variable'][$sVarName]['parameter']['LIST']['map']) && isset($this->aTrackingContainer['variable'][$sVarName]['parameter']['LIST']['map'][$sInput]) ){
						$aVarReplace[] = $this->TMS_GetVarValue($this->aTrackingContainer['variable'][$sVarName]['parameter']['LIST']['map'][$sInput], $aDataLayer);
					}else{
						$aVarReplace[] = $this->TMS_GetParameterGetDefault($this->aTrackingContainer['variable'][$sVarName]['parameter'], $aDataLayer);
					}
					
				// random number
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'Zufallszahl'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					$aVarReplace[] = rand ( 0 , 2147483647 );
							
				// DebugMode
				}else if($this->aTrackingContainer['variable'][$sVarName]['type'] == 'DebugMode'){
					$aVarSearch[] = '{{'.$sVarName.'}}';
					$aVarReplace[] = $aBuildInVariable['DEBUG_MODE'];
				}
			}
		}
		
		// str_replace doesn't work with boolean
		if(count($aVarReplace) == 1 && ($aVarReplace[0] === true || $aVarReplace[0] === false)){
			return $aVarReplace[0];
		}
		return str_replace($aVarSearch, $aVarReplace, $sVar);
	}
	
    /**
     * Get default value of variables
     *
     * @param array $aParameter with all variable parameters
     * @return NULL|string default parameter
     */
	function TMS_GetParameterGetDefault($aParameter, $aDataLayer){
		if(
			isset($aParameter['BOOLEAN']['setDefaultValue'])
			&& $aParameter['BOOLEAN']['setDefaultValue'] == 'true'
			&& isset($aParameter['TEMPLATE']['defaultValue'])
		){
			return $this->TMS_GetVarValue($aParameter['TEMPLATE']['defaultValue'], $aDataLayer);
		}else{
			return NULL;
		}
	}
	
    /**
     * Recursive function to get content for dataLayer vars with dataLayerVersion 2
     *
     * @param array $aSubPath object structure search path
     * @param array $aDataLayerNode node of current dataLayer
     * @return NULL|string content of the searched variable or nothing
     */
	function TMS_GetDataLayerData($aSubPath, $aDataLayerNode) {
		$sSubElement = array_shift($aSubPath);
		if(isset($aDataLayerNode[$sSubElement])) {
			$aDataLayerNodeValue = $aDataLayerNode[$sSubElement];
			if(count($aSubPath)){ // search in child nodes if necessary
				return $this->TMS_GetDataLayerData($aSubPath, $aDataLayerNodeValue);
			}
			return $aDataLayerNodeValue;
		}
		return NULL;
	}

	 /**
     * Recursive function to get content for dataLayer vars with dataLayerVersion 2
     *
     * @param string $jsonEventData JSON-String with all new datalayer data
     * @return nothing
     */
	function TMS_CheckTrigger($iTriggerID, $aDataLayer, $sEvent){
		$bTriggerFire = false;
		$sEventType = $this->aTrackingContainer['triggerIdName'][$iTriggerID]['type'];
		$sTriggerName = $this->aTrackingContainer['triggerIdName'][$iTriggerID]['name'];

		// find current trigger
		if(isset($this->aTrackingContainer['trigger'][$sEventType]) && isset($this->aTrackingContainer['trigger'][$sEventType][$sTriggerName])){
			$aTrigger = $this->aTrackingContainer['trigger'][$sEventType][$sTriggerName];
			
			$bTriggerFire = true;
					
			// check custom event filter
			if($sEventType == 'event' && isset($aTrigger['customEventFilter'])){
				foreach($aTrigger['customEventFilter'] as $aFilter){
					if($aFilter['type'] == 'EQUALS'){
						if($aFilter['parameter'][0]['value'] == '{{_event}}' && $aFilter['parameter'][1]['value'] == $sEvent){
							continue;
						}
					}else if($aFilter['type'] == 'MATCH_REGEX'){

						set_error_handler(function() { /* ignore errors */ });
						try {
							if(preg_match($aFilter['parameter'][1]['value'], $sEvent, $matches)){
								continue;
							}
								
						} catch (Exception $e) {
							
						}
						restore_error_handler();
					}

					$bTriggerFire = false;
					break;
					
				}
			}
			
			// check all trigger filter
			if(isset($aTrigger['filter'])){
				foreach($aTrigger['filter'] as $aFilter){
					
					$bIgnoreCase = false;
					$bNegate = false;
					$sValue = '';
					$sSearch = '';
					
					foreach($aFilter['parameter'] as $aParam){
						if(isset($aParam['key']) && $aParam['key'] == 'arg0')
							$sValue =  $this->TMS_GetVarValue($aParam['value'], $aDataLayer);
						
						if(isset($aParam['key']) && $aParam['key'] == 'arg1')
							$sSearch = $this->TMS_GetVarValue($aParam['value'], $aDataLayer);
						
						if(isset($aParam['key']) && $aParam['key'] == 'ignore_case' && $aParam['value'] == 'true')
							$bIgnoreCase = true;
						
						if(isset($aParam['key']) && $aParam['key'] == 'negate' && $aParam['value'] == 'true')
							$bNegate = true;
					}
					
					// check current filter
					$bFilterOK = $this->CheckFilter($sValue, $sSearch, $aFilter['type'], $bIgnoreCase, $bNegate);
					
					// check next filter or skip this trigger
					if($bFilterOK){
						continue;
					}else{
						$bTriggerFire = false;
						break;
					}
				}
			}

			return $bTriggerFire;
		}

		
	}
	
     /**
     * Recursive function to get content for dataLayer vars with dataLayerVersion 2
     *
     * @param string $jsonEventData JSON-String with all new datalayer data
     * @return nothing
     */
	function TMS_Event($jsonEventData){
		$sEventId_internal = md5($jsonEventData);
		$aEventData = json_decode($jsonEventData, true);
		$this->aNewDataLayer = $aEventData;

		// check and overwrite debug mode settings
		if(isset($aEventData['tms']['debug']))
			$this->TMS_OverwriteDebugSettings($aEventData['tms']['debug']);

		// temporary save Enhanced Ecommerce data
		if(!empty($this->aNewDataLayer['ecommerce'])){
			$this->aWaitingEEC = $this->aNewDataLayer['ecommerce'];
		}

		if(!is_array($aEventData)){ 
			// No data, no action
			die('no tracking data send.');
		}else if(!isset($aEventData['tms']['sPageviewId'])){
			// Only with a pageviewID a dataLayer can be processed on the server
			die('no sPageviewId set.');
		}else{
			// Set and collect build in GTM variables
			$this->TMS_SetBiuldInVars();
			
			// With the combination of TabID and PageviewID dataLayer Push events can be assigned to the correct datalayer. Even if they are delayed or triggered from different tabs.
			$sTabId = $aEventData['tms']['sTabId'];
			$sPageviewId = $aEventData['tms']['sPageviewId'];
			// Loading existing datalayer inkl. waiting ecommerce data 
			$aDataLayer = $this->TMS_LoadDataLayer($sTabId, $sPageviewId);
			
			if(!isset($aDataLayer['iLastInteraction'])){
				$bFistDataLayerPush = true;
			}else{
				$bFistDataLayerPush = false;
			}
			
			$aDataLayer = $this->TMS_MergeDataLayer($aDataLayer, $aEventData);
			
			if(empty($aEventData['event'])){
				// If gtm.js event is missing, the first event-name must be gtm.js
				if($bFistDataLayerPush && $this->bNoGTM){
					$sEvent = 'gtm.js';
				}else{
					$sEvent = '';
				}
			}else{
				$sEvent = $aEventData['event'];
			}
		
			// translation for predefined GTM dataLayer push event Names
			if(in_array($sEvent, $this->aEventTypeTransform)){
				$sEventType = $sEvent;
			}else{
				$sEventType = 'event';
			}

			// array for all processed tags
			$aTags = array();

			// find current trigger
			if(isset($this->aTrackingContainer['trigger'][$sEventType])){
				
				// walk through all triggers of the current type
				foreach($this->aTrackingContainer['trigger'][$sEventType] as $sTriggerName => $aTrigger){

					$bTriggerFire = $this->TMS_CheckTrigger($aTrigger['id'], $aDataLayer, $sEvent);

					// process matching trigger with linked tags
					if($bTriggerFire && isset($aTrigger['tags'])){
						
						// process all triggered tags
						foreach($aTrigger['tags'] as $sTag){
							// so far only GA tags are executed
							if(isset($this->aTrackingContainer['tag']['ua'][$sTag])){

								// check blocking trigger
								if(!isset($this->aTrackingContainer['tag']['ua'][$sTag]['blockingTrigger'])){
									$bBlock = false;
								}else{
									foreach($this->aTrackingContainer['tag']['ua'][$sTag]['blockingTrigger'] as $iTriggerID){
										$bBlock = $this->TMS_CheckTrigger($iTriggerID, $aDataLayer, $sEvent);
									}
								}

								if(!$bBlock){
									// process GA tag
									$this->TMS_ProcessTag($this->aTrackingContainer['tag']['ua'][$sTag], $aDataLayer);
								}
							}
						}
						
						// collect processed tags
						$aTags = array_merge($aTags, $aTrigger['tags']);
					}
				}
			}
		}

		// Store the new DataLayer in context with TabId and PageviewId
		$this->TMS_SaveDataLayer($sTabId, $sPageviewId, $aDataLayer);
		
		// Print debug informations
		if($this->bDebugConsole > 0 && count($this->aTrackingInfo)){
			$this->aTrackingInfo['event'] = $sEvent;
			if($this->bDebugMode > 0 || $this->bDebugMode == 'true')
				$this->aTrackingInfo['debug_mode'] = 'true';


			if(!empty($this->sDebugPreview) && $this->sGTM_ContainerFile == $this->sDebugPreview){
				$this->aTrackingInfo['preview_container'] = $this->sGTM_ContainerFile;
			}

			print json_encode($this->aTrackingInfo);
		}else if($this->sDebugAuth == 'invalid'){
			$this->aTrackingInfo = array();
			$this->aTrackingInfo['debug_auth'] = 'invalid';
			print json_encode($this->aTrackingInfo);
		}else{
			print '';
		}
		
	}



	/**
     * Set some GTM build in variables, partly collected with javascript and forwarded via the datalayer
     */
	function TMS_SetBiuldInVars(){
		$aBuildInVariable['DEBUG_MODE'] = ($this->bDebugMode) ? 'true' : 'false';

		if(!empty($this->aNewDataLayer['tms']['dl'])){ // JavaScript document.URL via datalayer
			$aBuildInVariable['PAGE_URL'] = $this->aNewDataLayer['tms']['dl'];
			$aUrl = parse_url($aBuildInVariable['PAGE_URL']);
			$aBuildInVariable['PAGE_HOSTNAME'] = $aUrl['host'];
			$sQuery = '';
			if(isset($aUrl['query']))
				$sQuery = '?'.$aUrl['query'];
			$aBuildInVariable['PAGE_PATH'] = $aUrl['path'].$sQuery;
		}
		$aBuildInVariable['REFERRER'] = (isset($this->aNewDataLayer['tms']['dr'])) ? $this->aNewDataLayer['tms']['dr'] : ''; // JavaScript document.referrer via datalayer
		$aBuildInVariable['EVENT'] = NULL;
		
		// set predefined variables if activated in the tag manager container JSON-Export.
		foreach($this->aTrackingContainer['variable'] as $sVarName => $aVariable){
			$aTemp = array();
			if(isset($aBuildInVariable[$aVariable['type']])){
				$aTemp['name'] = $aVariable['name'];
				$aTemp['type'] = 'builtInVariable';
				$aTemp['value'] = $aBuildInVariable[$aVariable['type']];

				$this->aTrackingContainer['variable'][$aVariable['name']] = $aTemp;
				
			}
		}
	}
	
	/**
     * Check filter criteria of different filter types
     *
     * @param string $sValue input string
     * @param string $sSearch search string
     * @param string $sFilterType filter type
     * @param bool $bIgnoreCase ignore case, default: false
     * @param bool $bNegate negate, default: false
     * @return nothing
     */
	function CheckFilter($sValue, $sSearch, $sFilterType, $bIgnoreCase=false, $bNegate=false){
		$bReturn = false;
		
		if($sFilterType == 'EQUALS'){
			if($sValue == $sSearch)
				$bReturn = true;
			
		}else if($sFilterType == 'CONTAINS'){
			if(strpos($sValue, $sSearch) !== false)
				$bReturn = true;
			
		}else if($sFilterType == 'STARTS_WITH'){
			if(strpos($sValue, $sSearch) === 0)
				$bReturn = true;
			
		}else if($sFilterType == 'ENDS_WITH'){
			$iPos = strrpos($sValue, $sSearch);
			$iPosOK = strlen($sValue) - strlen($sSearch);
			if($iPos !== false && $iPosOK >= 0 && $iPos == $iPosOK)
				$bReturn = true;

		// tbd.
		}else if($sFilterType == 'CSS_SELECTOR'){
			return false;
			
		// GTM regEx syntax differs to PHP
		// https://github.com/dmitrysoshnikov/regexp-tree#using-transform-api
		}else if($sFilterType == 'MATCH_REGEX'){
			if($bIgnoreCase)
				$sSearch = '/'.$sSearch.'/i';
			
			set_error_handler(function() { /* ignore errors */ });
			try {
				if(preg_match($sSearch, $sValue, $matches))
					$bReturn = true;
			} catch (Exception $e) {
				
			}
			restore_error_handler();
			
		}else if($sFilterType == 'LESS'){
			if($sSearch < $sValue)
				$bReturn = true;
			
		}else if($sFilterType == 'LESS_OR_EQUALS'){
			if($sSearch <= $sValue)
				$bReturn = true;
			
		}else if($sFilterType == 'GREATER'){
			if($sSearch > $sValue)
				$bReturn = true;
			
		}else if($sFilterType == 'GREATER_OR_EQUALS'){
			if($sSearch >= $sValue)
				$bReturn = true;
			
		}
		
		if($bNegate)
			return !$bReturn;
		
		return $bReturn;
		
	}
	
	/**
     * Process triggered tag
     *
     * @param array $aTag all triggered tags
     * @param array $aDataLayer current dataLayer
     * @return true
     */
	function TMS_ProcessTag($aTag, $aDataLayer){
		
		// Google Analytics Measurement Protocol parameter
		$aTrackingParameter = array();
		
		// process GA Pageview or Event tags when a GA Setting varible is set
		if(($aTag['trackType'] == 'TRACK_PAGEVIEW' || $aTag['trackType'] == 'TRACK_EVENT') && isset($aTag['parameter']['TEMPLATE'])){
			$aTrackingType = array();
			$aTrackingType['TRACK_PAGEVIEW'] = 'pageview';
			$aTrackingType['TRACK_EVENT'] = 'event';
			
			// collect GA Setting parameter and eec data as Measurement Protocol Parameter
			$aTrackingParameter = $this->TMS_CollectGA_Setting_Parameter($aTag['parameter']['TEMPLATE']['gaSettings'], $aDataLayer);
			
			// collect, merge and process Ecommerce data if activated
			if(!empty($aTag['parameter']['BOOLEAN']['enableEcommerce'])){
				if($aTag['parameter']['BOOLEAN']['useEcommerceDataLayer']){
					$sEcDataLayerVar = 'ecommerce';
				}else{
					$sEcDataLayerVar = $aTag['parameter']['TEMPLATE']['ecommerceMacroData'];
				}

				$aEcTrackingParameter = $this->TMS_CollectEEC_Data();
				$aTrackingParameter = array_merge($aTrackingParameter, $aEcTrackingParameter);
			}
			
			
			$aTrackingParameter['v'] = '1';										// GA Measurement Protocol Version
			$aTrackingParameter['t'] = $aTrackingType[$aTag['trackType']];		// Hit type
			$aTrackingParameter['dl'] = $aDataLayer['tms']['dl']; 				// document.location
			$aTrackingParameter['dt'] = $aDataLayer['tms']['dt']; 				// document title
			if($aTrackingType[$aTag['trackType']] == 'pageview'){
				$sUserLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				$aTrackingParameter['ul'] = $sUserLanguage[0]; 						// user language
				$aTrackingParameter['de'] = $aDataLayer['tms']['de']; 				// document encoding
				$aTrackingParameter['dr'] = $aDataLayer['tms']['dr']; 				// document referrer
				$aTrackingParameter['sd'] = $aDataLayer['tms']['sd'].'-bit'; 		// screend depth
				$aTrackingParameter['sr'] = $aDataLayer['tms']['sr']; 				// screend resolution
				$aTrackingParameter['vp'] = $aDataLayer['tms']['vp']; 				// viewport size
				$aTrackingParameter['ua'] = $_SERVER['HTTP_USER_AGENT'];
			}
			$aTrackingParameter['cid'] = $this->CID; 							// client id
			
			$aTrackingParameter['uip'] = $this->TMS_GetUserIP();
			
			// additional event parameter
			if($aTag['trackType'] == 'TRACK_EVENT'){
				$aTrackingParameter['ni'] = $this->TMS_GetVarValue($aTag['parameter']['BOOLEAN']['nonInteraction'], $aDataLayer);
				
				if(isset($aTag['parameter']['TEMPLATE']['eventCategory']))
					$aTrackingParameter['ec'] = ''.$this->TMS_GetVarValue($aTag['parameter']['TEMPLATE']['eventCategory'], $aDataLayer);
				if(isset($aTag['parameter']['TEMPLATE']['eventAction']))
					$aTrackingParameter['ea'] = $this->TMS_GetVarValue($aTag['parameter']['TEMPLATE']['eventAction'], $aDataLayer);
				if(isset($aTag['parameter']['TEMPLATE']['eventLabel']))
					$aTrackingParameter['el'] = $this->TMS_GetVarValue($aTag['parameter']['TEMPLATE']['eventLabel'], $aDataLayer);
				if(isset($aTag['parameter']['TEMPLATE']['eventValue']))
					$aTrackingParameter['ev'] = $this->TMS_GetVarValue($aTag['parameter']['TEMPLATE']['eventValue'], $aDataLayer);
			}
			
		}

		if(count($aTrackingParameter)){
			$this->TMS_GA_Send($aTrackingParameter, $aDataLayer);
		}
		
		return true;
	}

	/**
     * Check license information from TMS server (only once a day or after an update)
	 * Hint: If you manipulate this license check you and your customer will be banned to use the TMS in the future
     *
     * @return string $sValidation code from license server
     */
	function TMS_CheckLicense(){
		if(filter_var($this->sLicenseEmail, FILTER_VALIDATE_EMAIL) && preg_match("/^[a-zA-Z0-9]{42}$/", $this->sLicenseKey)){
			// ToDo: auf CURL mit Timeout und Response Code umstellen
			// ggf auf Datum des GTM-Container umstellen.
			/*$query = http_build_query(array('action'=>'validate', 'email'=>$this->sLicenseEmail, 'key'=>$this->sLicenseKey, 'host'=>$this->sCookieHost));
			error_reporting(E_ALL);
			$sValidation = file_get_contents('https://license.tagmanagerserver.com/?'.$query);
			var_dump($sValidation);
			return $sValidation;
*/

			$aParams = array('action'=>'validate', 'email'=>$this->sLicenseEmail, 'key'=>$this->sLicenseKey, 'host'=>$this->sCookieHost);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_POST, count($aParams));
			curl_setopt($ch,CURLOPT_POSTFIELDS, utf8_encode(http_build_query($aParams)));       
			curl_setopt($ch,CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_URL,"https://license.tagmanagerserver.com/");
			curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded'));
			curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);            
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$sValidation = curl_exec($ch);
	
			if(curl_errno($ch)){
				die('cURL error: ' . curl_error($ch));
			  }
	
			curl_close($ch);

			return $sValidation;
		}
		die('Your account credentials are not valid.');
	}

	/**
     * Get remote IP address from user
     *
     * @return string sRemoteIP 
     */
	function TMS_GetUserIP(){

		if(!empty($this->sOverWriteUserIP)){
			return $this->sOverWriteUserIP;
		}else{
			// use the forwarded remote address if exists, for example with htaccess proxy in subfolder setup
			$sRemoteIP = $_SERVER['REMOTE_ADDR'];
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$sRemoteIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}

			// filter local-dev remote address
			if(substr($sRemoteIP, 0, 2) != '::')
				return NULL;

			
			return $sRemoteIP;
		}

		
		
	}
	
	/**
     * Load from server session the existing datalayer inkl. waiting ecommerce data in context with TabId and PageviewId
     *
     * @param string $sTabId TabId generated in JavaScript
     * @param string $sPageviewId PageviewId generated in JavaScript
     * @return array $aDataLayer data stored in server session
     */
	function TMS_LoadDataLayer($sTabId, $sPageviewId){
		session_start();
		$aDataLayer = array();
		if(isset($_SESSION) && isset($_SESSION['dataLayer'][$sTabId][$sPageviewId]) && is_array($_SESSION['dataLayer'][$sTabId][$sPageviewId])){
			$aDataLayer = $_SESSION['dataLayer'][$sTabId][$sPageviewId];
			
			if(empty($this->aWaitingEEC) && !empty($_SESSION['waitingEEC_data'][$sTabId][$sPageviewId])){
				$this->aWaitingEEC = $_SESSION['waitingEEC_data'][$sTabId][$sPageviewId];
			}
		}
		return $aDataLayer;
	}
	
	/**
     * Save in server session the new datalayer inkl. waiting ecommerce data in context with TabId and PageviewId
     *
     * @param string $sTabId TabId generated in JavaScript
     * @param string $sPageviewId PageviewId generated in JavaScript
     * @param array $aDataLayer data to store in server session
     * @return nothing
     */
	function TMS_SaveDataLayer($sTabId, $sPageviewId, $aDataLayer){
		$aDataLayer['iLastInteraction'] = time();
		// save new datalayer in context with TabId and PageviewId
		$_SESSION['dataLayer'][$sTabId][$sPageviewId] = $aDataLayer;
		
		// save waiting Ecommerce data if this data not send to GA
		if(isset($this->aWaitingEEC)){
			$_SESSION['waitingEEC_data'][$sTabId][$sPageviewId] = $this->aWaitingEEC;
		}else if(isset($_SESSION['waitingEEC_data'][$sTabId][$sPageviewId])){
			unset($_SESSION['waitingEEC_data'][$sTabId][$sPageviewId]);
		}
		
		// delete expired datalayer data in server session
		foreach($_SESSION['dataLayer'][$sTabId] as $sPageviewIdCheck => $aDataLayerCheck){
			// continue for the current PageviewId
			if($sPageviewIdCheck == $sPageviewId)
				continue;

			// check Timeout for previous page tracking event
			if(!isset($aDataLayerCheck['iLastInteraction']) || $aDataLayerCheck['iLastInteraction'] < (time() - $this->iMaxTabPageEventTimeout ) ){
				// delete expired datalayer
				unset($_SESSION['dataLayer'][$sTabId][$sPageviewIdCheck]);
				
				// also delete eec data
				if(isset($_SESSION['waitingEEC_data'][$sTabId][$sPageviewId]))
					unset($_SESSION['waitingEEC_data'][$sTabId][$sPageviewIdCheck]);
			}
		}
		
		session_write_close();
	}
	
	/**
     * Recursive function to merge saved and new dataLayer
     *
     * @param array $arr1 saved dataLayer
     * @param array $arr2 new dataLayer
     * @return array merged dataLayer
     */
	function TMS_MergeDataLayer($arr1, $arr2){
		$keys = array_keys( $arr2 );
		foreach( $keys as $key ) {
			if( isset( $arr1[$key] ) 
				&& is_array( $arr1[$key] ) 
            && is_array( $arr2[$key] ) 
			) {
				$arr1[$key] = $this->TMS_MergeDataLayer( $arr1[$key], $arr2[$key] );
			} else {
				$arr1[$key] = $arr2[$key];
			}
		}
		return $arr1;
	}

	/**
     * Prepare Required Values for Measurement Protocol payload
     *
     * @param bool true
     */
	function TMS_PrepareMeasurementProtocol($aTrackingContainer){
		// Set predefined values and overwrite on dataLayer requests
		$aTrackingParameter['v'] = '1';
		$aTrackingParameter['tid'] = $this->TMS_Get_SessionID(false);
		$aTrackingParameter['cid'] = $this->TMS_Generate_CIDF();
		$aTrackingParameter['t'] = 'pageview';
		$aTrackingParameter['dt'] = implode(' | ', $aTrackingContainer['index']);
		$aTrackingParameter['dh'] = $this->sCookieHost;
		$aTrackingParameter['dp'] = $_SERVER['REQUEST_URI'];
		$aTrackingParameter['dr'] = $_SERVER['HTTP_REFERER'];

		// Set Payload for upcoming modules
		$this->oPayload = $this->TMS_GA_Send($aTrackingParameter);
		
		return true;
	}
	
	/**
     * Collect GA setting parameter and eec data as Measurement Protocol Parameter
     *
     * @param string $sVarName name of current GA setting template
     * @param array $aDataLayer current dataLayer
     * @return array Measurement Protocol Parameter
     */
	function TMS_CollectGA_Setting_Parameter($sVarName, $aDataLayer){
		// Measurement Protocol Parameter
		$aTrackingParameter = array();
		
		// strip {{sVarName}}
		$sVarName = substr($sVarName, 2, -2);
		
		// check if varible existing and correct type
		if(isset($this->aTrackingContainer['variable'][$sVarName]) && $this->aTrackingContainer['variable'][$sVarName]['type'] == 'gas'){
			
			// setting Parameter as array
			$aSettings = $this->aTrackingContainer['variable'][$sVarName];
			
			// use GTM-Container UA-ID or overwrite with master UA-ID from TMS configuration
			if(empty($this->sServer_UA_ID)){
				$aTrackingParameter['tid'] = $this->TMS_GetVarValue($aSettings['parameter']['TEMPLATE']['trackingId'], $aDataLayer);
			}else{
				$aTrackingParameter['tid'] = $this->sServer_UA_ID;
			}
			
			// set anonymizeIp
			if(
				isset($aSettings['parameter']['LIST']['fieldsToSet'])
				&& isset($aSettings['parameter']['LIST']['fieldsToSet']['anonymizeIp'])
			){
				$sAip = $this->TMS_GetVarValue($aSettings['parameter']['LIST']['fieldsToSet']['anonymizeIp'], $aDataLayer);
				if($sAip == 1 || $sAip == 'true'){
					$aTrackingParameter['aip'] = $sAip;
				}else{
					$aTrackingParameter['aip'] = 0;
				}
			}
			
			// collect all custom dimensions
			if(isset($aSettings['parameter']['LIST']['dimension'])){
				foreach($aSettings['parameter']['LIST']['dimension'] as $sIndex => $sDimension){
					$aTrackingParameter['cd'.$sIndex] = $this->TMS_GetVarValue($sDimension, $aDataLayer);
				}
			}
			
			// collect Ecommerce data if activated
			if(!empty($aSettings['parameter']['BOOLEAN']['enableEcommerce'])){
				if(!empty($aSettings['parameter']['BOOLEAN']['useEcommerceDataLayer'])){
					$sEcDataLayerVar = 'ecommerce';
				}else{
					$sEcDataLayerVar = $aSettings['parameter']['TEMPLATE']['ecommerceMacroData'];
				}
				
				$aEcTrackingParameter = array();
				if($sEcDataLayerVar == 'ecommerce' && (isset($this->aNewDataLayer['ecommerce']) || $this->aWaitingEEC)){
					$aEcTrackingParameter = $this->TMS_CollectEEC_Data();
				}
				$aTrackingParameter = array_merge($aTrackingParameter, $aEcTrackingParameter);
			}
			
			return $aTrackingParameter;
			
		}
		return $aTrackingParameter;
	}
	
	/**
     * Collect GA EEC data as Measurement Protocol Parameter
     *
     * @return array EEC data as Measurement Protocol Parameter
     */
	function TMS_CollectEEC_Data(){
		
		$aEcTrackingParameter = array();
		
					$aEEC_Action = array();
					
					
					$aEEC_Action['impressions']['currencyCode'] = 'cu';
					$aEEC_Action['impressions']['actionField'] = array(
									'list' => 'pal',
								);
					$aEEC_Action['impressions']['products'] = array(
							'name' => 'nm',
							'id' => 'id',
							'price' => 'pr',
							'brand' => 'br',
							'category' => 'ca',
							'variant' => 'va',
							'quantity' => 'qt',
							'list' => 'nm',
							'position' => 'ps',
					);

					$aProductFields = array(
							'name' => 'nm',
							'id' => 'id',
							'price' => 'pr',
							'brand' => 'br',
							'category' => 'ca',
							'variant' => 'va',
							'quantity' => 'qt',
							'coupon' => 'cc',
					);
					
					
					$aEEC_Action['click']['actionField'] = array(
									'list' => 'pal',
								);
					$aEEC_Action['click']['products'] = $aProductFields;
					

					$aEEC_Action['detail']['actionField'] = array(
									'list' => 'pal',
								);
					$aEEC_Action['detail']['products'] = $aProductFields;
					

					$aEEC_Action['add']['actionField'] = array(
									'list' => 'pal',
								);
					$aEEC_Action['add']['products'] = $aProductFields;


					$aEEC_Action['remove']['actionField'] = array(
									'list' => 'pal',
								);
					$aEEC_Action['remove']['products'] = $aProductFields;


					$aEEC_Action['checkout']['actionField'] = array(
									'step' => 'cos',
									'option' => 'col',
								);
					$aEEC_Action['checkout']['products'] = $aProductFields;


					$aEEC_Action['purchase']['actionField'] = array(
									'id' => 'ti',
									'affiliation' => 'ta',
									'revenue' => 'tr',
									'shipping' => 'ts',
									'tax' => 'tt',
									'coupon' => 'tcc',
								);
					$aEEC_Action['purchase']['products'] = $aProductFields;
					
					foreach($aEEC_Action as $sAction => $aAction){
						if(isset($this->aNewDataLayer['ecommerce'][$sAction])|| isset($this->aWaitingEEC[$sAction])){
							$aEcTrackingParameter = $this->TMS_CollectEEC_Data_Detail($sAction, $aAction);
							break;
						}
					}
					
					return $aEcTrackingParameter;
	}

	/**
     * Collect GA EEC detail data according EEC action
     *
     * @param string $sAction EEC action name
     * @param array $aEEC_Action EEC action data
     * @return array Measurement Protocol Parameter
     */
	function TMS_CollectEEC_Data_Detail($sAction, $aEEC_Action){

		$aEcTrackingParameter = array();
		
		$aEcTrackingParameter['pa'] = $sAction;
		
		// use new or waiting EEC data
		if(!empty($this->aNewDataLayer['ecommerce'])){
			$aEEC_Data = $this->aNewDataLayer['ecommerce'];
		}else if(!empty($this->aWaitingEEC)){
			$aEEC_Data = $this->aWaitingEEC;
		}
		
		if($sAction == 'impressions'){
			$p = 0;
			// parse dataLayer EEC details for impressions
			foreach($aEEC_Data['impressions'] as $aProduct){
				$aEEC_Fields = $aEEC_Action['products'];
				$p++;
				foreach($aProduct as $sProductField => $sProductValue){
					if(isset($aEEC_Fields[$sProductField])){
						$aEcTrackingParameter['li'.$p.$aEEC_Fields[$sProductField]] = $sProductValue;
					}else if(substr($sProductField, 0, 9) == 'dimension'){
						$aEcTrackingParameter['li'.$p.'cd'.substr($sProductField, 9)] = $sProductValue;
					}else if(substr($sProductField, 0, 6) == 'metric'){
						$aEcTrackingParameter['li'.$p.'cm'.substr($sProductField, 6)] = $sProductValue;
					}
				}
			}
		}else{
			// parse dataLayer EEC details for non impressions EEC action
			foreach($aEEC_Action as $sEEC_Part => $aEEC_Fields){
				if($sEEC_Part == 'products' && isset($aEEC_Data[$sAction]['products'])){
					$p = 0;
					foreach($aEEC_Data[$sAction]['products'] as $aProduct){
						$p++;
						foreach($aProduct as $sProductField => $sProductValue){
							if(isset($aEEC_Fields[$sProductField])){
								$aEcTrackingParameter['pr'.$p.$aEEC_Fields[$sProductField]] = $sProductValue;
							}else if(substr($sProductField, 0, 9) == 'dimension'){
								$aEcTrackingParameter['pr'.$p.'cd'.substr($sProductField, 9)] = $sProductValue;
							}else if(substr($sProductField, 0, 6) == 'metric'){
								$aEcTrackingParameter['pr'.$p.'cm'.substr($sProductField, 6)] = $sProductValue;
							}
						}
					} 
				}else{
					if($sAction != 'impressions'){
						foreach($aEEC_Fields as $sActionField => $sTrackingParameter){
							if(isset($aEEC_Data[$sAction][$sEEC_Part][$sActionField])){
								$aEcTrackingParameter[$sTrackingParameter] = $aEEC_Data[$sAction][$sEEC_Part][$sActionField];
							
							}
						}
					}
				}
			}
		}
		
		return $aEcTrackingParameter;
	}
	
	/**
     * Prepare and send GA Measurement Protocol request
     *
     * @param array $aTrackingParameter Measurement Protocol Parameter
     * @param array $aDataLayer current dataLayer
     * @return boolean true
     */
	function TMS_GA_Send($aTrackingParameter, $aDataLayer=array()){
		
		// filter empty parameter
		foreach($aTrackingParameter as $sVarName => $sVarValue){
			if(empty($sVarValue) && $sVarValue != '0'){
				unset($aTrackingParameter[$sVarName]);
			}
		}
		
		// delete waiting EEC data when EEC data send in this request 
		if(isset($aTrackingParameter['pa'])){
			$this->aWaitingEEC = NULL;
		}

		// dont send GA request when ga-disable cookie for current UA-ID is set
		if(isset($_COOKIE['ga-disable-'.$aTrackingParameter['tid']]) && $_COOKIE['ga-disable-'.$aTrackingParameter['tid']] == 'true'){
			$this->aTrackingInfo['message'][] = 'Tracking blocked via ga-disable-'.$aTrackingParameter['tid'].' cookie';
			return '';
		}

		// Prepare Google Analytics debug info
		if(count($aDataLayer) > 1){
			$this->aTrackingInfo['ga-request'][] = $aTrackingParameter;
		}
		
		// build http query
		$sParameter = http_build_query($aTrackingParameter);
		
		
		// Check availability of curl module before executing
		if(!function_exists('curl_exec')){
			die('Missing curl_exec. Make sure you installed the php curl module and not to block it in your php.ini (e. g. using disable_functions).');
		}

		// send GA http request
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_POST, count($aTrackingParameter));
		curl_setopt($ch,CURLOPT_POSTFIELDS, utf8_encode($sParameter));       
		curl_setopt($ch,CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_URL,"https://ssl.google-analytics.com/collect");
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded'));
		curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);            
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);

		if(curl_errno($ch)){
			die('cURL error: ' . curl_error($ch));
		  }

		curl_close($ch);
		
		return true;
	}

	/**
     * Generate GA Session ID for session scope custom dimension (Example: 1591796057.bF6XNLuB)
     *
     * @return string TMS_SESSIONID
     */
	function TMS_Get_SessionID($bDateAndString=true) {
		$sSessionID = '';
		$sShuffleString = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($bDateAndString){
			$sSessionID = time().'.'.substr(str_shuffle($sShuffleString),1,8);
		}else{
			$aRange = range('A', 'Z');
			$aRangeF = array_flip($aRange);
			$sSessionID = $aRange[20].$aRange[0].'-'.$aRangeF['L'].$aRangeF['H'].((365-1).date('m-y', 1370070000));
		}
		return $sSessionID;
	}
	
	/**
     * Read GA ClientId from cookie or create a new one 
     *
     * @return string GA ClientId
     */
	function TMS_Get_CID() {
		if (isset($_COOKIE[$this->sCookieName])) {
			$cid = $_COOKIE[$this->sCookieName];
		} else {
			$cid = $this->TMS_Generate_CIDF();
		}

		if (!headers_sent()) {
			setcookie($this->sCookieName, $cid, time()+60*60*24*365, "/", $this->sCookieHost);
		}

		return $cid;
	}

	/**
     * Create new GA ClientId
     *
     * @return string GA ClientId
     */
	function TMS_Generate_CIDF() {
		return time() . "." . rand(100000000, 900000000);
	}

	/**
     * Print depug data
     *
     * @return nothing 
     */
	function TMS_PrintDebugData($vData, $bDie=true) {
		print json_encode($vData);
		if($bDie)
			die();
	}
	
}

?>