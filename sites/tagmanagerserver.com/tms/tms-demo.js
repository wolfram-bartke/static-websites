// Copyright 2020 Wolfram Bartke, tagmanagerserver.com, all rights reserved. 

var TMS = {};

TMS.Init = function() {

	// TMS configuration
	TMS.name = 'Tag Manager Server';
	TMS.dataQueue = [];
	TMS.eventsInclude = [];
	TMS.eventsExclude = [];
	TMS.dataSendingNow = false;
	TMS.tmsTrackOnly = false;
	TMS.gtmJS_Sent = false;
	var scripts = document.getElementsByTagName("script");
	var sCurrenPath = scripts[scripts.length-1].src;
	TMS.api = sCurrenPath.substring(0, sCurrenPath.lastIndexOf('/'))+'/api.php';
	TMS.debug = TMS.GetDebug();
	// for testing
	TMS.debug.console = 1;
	TMS.debug.auth = 'demo';

	// check google-analytics-opt-out browser extension
	if (typeof window["_gaUserPrefs"] !== 'undefined' && typeof window["_gaUserPrefs"].ioo !== 'undefined' && window["_gaUserPrefs"].ioo()) {
		console.log('TMS blocked via google-analytics-opt-out browser extension');
		return false;
	}
	
	TMS.sTabId = 'none';
	if(window.sessionStorage){
		if(TMS.sTabId = sessionStorage.getItem("tms_tabid")){
		}else{
			TMS.sTabId = new Date().getTime() + '.' + Math.random().toString(36).substring(5);
			sessionStorage.setItem("tms_tabid", TMS.sTabId);
		}			
	}
	TMS.sPageviewId = new Date().getTime() + '.' + Math.random().toString(36).substring(5);
	
	TMS.pushCallback = function(arr, TMS_callback) {
		arr.push = function(e) {
			Array.prototype.push.call(arr, e);
			TMS_callback(e);
		};
	};

	window.dataLayer = window.dataLayer || [];
	
	TMS.pushCallback(window.dataLayer, function(updatedArr) {
		
		if(updatedArr['event'] && updatedArr['event'] == 'gtm.js'){
			if(window.dataLayer.length > 0){
				updatedArr = window.dataLayer[0];
				updatedArr['event'] = 'gtm.js';
			}
			if(!TMS.gtmJS_Sent)
				TMS.PrepareData(updatedArr);
			TMS.gtmJS_Sent = true;
		}else{
			TMS.PrepareData(updatedArr);
		}

	});

};


TMS.PrepareData = function(aData) {
	
	aData['tms'] = {};
	aData['tms']['sPageviewId'] = TMS.sPageviewId;
	aData['tms']['sTabId'] = TMS.sTabId;
	aData['tms']['dl'] = document.URL;
	aData['tms']['de'] = document.characterSet;
	aData['tms']['dt'] = document.title;
	aData['tms']['sd'] = window.screen.colorDepth;
	aData['tms']['sr'] = window.screen.width+'x'+window.screen.height;
	aData['tms']['vp'] = window.innerWidth+'x'+window.innerHeight;
	aData['tms']['dr'] = document.referrer;

	var bMuteEvent = false;
	if(Array.prototype.indexOf && aData['event']){
		if(TMS.eventsExclude.length && TMS.eventsExclude.indexOf(aData['event']) > -1){
			bMuteEvent = true;
		}else if(TMS.eventsInclude.length && TMS.eventsInclude.indexOf(aData['event']) == -1){
			bMuteEvent = true;
		}
	}

	if(TMS.debug.console && TMS.debug.console > 0){
		aData['tms']['debug'] = TMS.debug;
		if(bMuteEvent){
			console.log('%c ['+(aData['event'] ? aData['event'] : 'Message')+'] - muted - '+TMS.name+' Input (dataLayer)', 'background: #ff0000; color: #ffffff');
		}else{
			console.log('%c ['+(aData['event'] ? aData['event'] : 'Message')+'] '+TMS.name+' Input (dataLayer)', 'background: #4285f4; color: #ffffff');
			TMS.CountLog('pushCount');
		}
		
		console.log(aData);
	}

	if(!bMuteEvent){
		if(TMS.debug.auth){
			aData['tms']['debug'] = TMS.debug;
		}
		TMS.dataQueue.push(aData);
		TMS.SendData();
	}
	
};

TMS.SendData = function() {
	
	if(TMS.dataSendingNow || !TMS.dataQueue.length > 0)
		return false;
	
	TMS.dataSendingNow = true;
	var aData = TMS.dataQueue.shift();
	
	var url = TMS.api;

	var xhr = new XMLHttpRequest();
	xhr.withCredentials = true
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4) {
			
			TMS.dataSendingNow = false;
			TMS.SendData();
			
			
			if (typeof window.RenderGaRequest !== "undefined") { 
				window.RenderGaRequest(xhr.response, 'hit_tms');
			}

			if(TMS.debug.console && xhr.response != ''){
				var logText = '';
				var eventName = '';
				try {
					var oRsp = JSON.parse(xhr.response);
					eventName = oRsp.event;
					delete oRsp.event;
					if(oRsp.preview_container){

						if(TMS.debug.logDebugPreviewInfo == 0 && oRsp.preview_container != window.tms_preview_container){
							window.tms_preview_container = oRsp.preview_container;
							console.log('%c ['+window.tms_preview_container+'] '+TMS.name+' load preview container', 'background: #9c26b0; color: #ffffff');
							TMS.debug.logDebugPreviewInfo = 1;
							if(window.sessionStorage){
								sessionStorage.setItem("tms_debug_preview_valid", 1);
							}
						}
						delete oRsp.preview_container;
					}else if(window.sessionStorage){
						sessionStorage.setItem("tms_debug_preview_valid", 0);
					}
					if(oRsp.debug_mode){
						if(TMS.debug.logDebugModeInfo == 0){
							console.log('%c [DEBUG_MODE='+oRsp.debug_mode+'] '+TMS.name+' ', 'background: #e37400; color: #ffffff');
							
							TMS.debug.logDebugModeInfo = 1;
						}
						delete oRsp.debug_mode;
					}
					if(TMS.debug.logDebugError == 0 && oRsp.debug_auth && oRsp.debug_auth == 'invalid'){
						console.log('%c [debugAuth invalid] '+TMS.name+' ', 'background: #ff0000; color: #ffffff');
						delete oRsp.debug_auth;
						TMS.debug.logDebugError = 'auth';

					}
					logText = oRsp;
				} catch (e) {
					console.log('%c [error] '+TMS.name+' ', 'background: #ff0000; color: #ffffff');
					logText = 'Error: '+xhr.response;
					TMS.debug.logDebugError = 'error';
				}

				if(window.sessionStorage){
					sessionStorage.setItem("tms_debug_error", TMS.debug.logDebugError);
				}
				
				if(TMS.debug.logDebugError == 0){
					console.log('%c ['+eventName+'] '+TMS.name+' Action (Measurement Protocol Parameter)', 'background: #49a32f; color: #ffffff');
					TMS.CountLog('tagCount');
				}
				console.log(logText);
			}
		}
	}
	xhr.open('POST', url, true);
	xhr.send(JSON.stringify(aData));
};

TMS.GetDebug = function() {
	var oDebug = {};
	oDebug.logDebugModeInfo = 0;
	oDebug.logDebugPreviewInfo = 0;
	oDebug.logDebugError = 0;
	
	if(window.sessionStorage){
		var sAuth = localStorage.getItem("tms_debug_auth");
		var sConsole = localStorage.getItem("tms_debug_console");
		var sMode = localStorage.getItem("tms_debug_mode");
		var sPreview = localStorage.getItem("tms_debug_preview");
		var sPreviewValid = sessionStorage.getItem("tms_debug_preview_valid");
		sessionStorage.setItem("tms_debug_pushCount", 0);
		sessionStorage.setItem("tms_debug_tagCount", 0);
	}
	oDebug.auth = (sAuth ? sAuth : 0);
	if(oDebug.auth){
		oDebug.console = (sConsole ? sConsole : 0);
		oDebug.mode = (sMode ? sMode : 0);
		oDebug.preview = (sPreview ? sPreview : 0);
		oDebug.previewValid = (sPreviewValid ? sPreviewValid : 0);
	}
	return oDebug;
};

TMS.CountLog = function(sCountType){
	if(window.sessionStorage){
		iCount = sessionStorage.getItem("tms_debug_"+sCountType);
		iCount++;
		sessionStorage.setItem("tms_debug_"+sCountType, iCount);
	}
};

TMS.Init();