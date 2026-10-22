(function() {
	var TMS_VIS = {};

	TMS_VIS.Config = function() {
		TMS_VIS.data = {};
		TMS_VIS.data.partInView = 50; // Percent value oder true
		TMS_VIS.data.timeInView = 1000; // in milliseconds (200 default)
		TMS_VIS.data.pushInterval = 3000; // in milliseconds
		TMS_VIS.data.eAttr = {'name':'name','depth':'data-tms-edepth'};
		TMS_VIS.data.eList = {};
		TMS_VIS.data.debug = false;
	};

	TMS_VIS.Init = function() {
		TMS_VIS.Config();
		TMS_VIS.data.eList = TMS_VIS.CollectElementData();
		TMS_VIS.CheckElementImpression();
		var intervalCheckImpression = setInterval(TMS_VIS.CheckElementImpression, TMS_VIS.data.timeInView);
		var intervalPushImpression = setInterval(TMS_VIS.ImpressionPush, TMS_VIS.data.pushInterval);
	};


	TMS_VIS.CollectElementData = function() {
		var eList = {};
		
		var eElement = document.querySelectorAll("a[name]");
		for (var i = 0; i < eElement.length; i++) {
			eElement[i].setAttribute('data-tms-edepth', i+1);
			eList[i] = TMS_VIS.GetElementData(eElement[i]);
		}
		return eList;
	};

    TMS_VIS.GetElementData = function(element) {
		var eData = {};
		var eAttr = TMS_VIS.data.eAttr;
		for (var attrName in eAttr) {
			eData[attrName] = element.getAttribute(eAttr[attrName]);
		}
		return eData;
	};

	/**
	 * Check if element is visible in Browser
     *
     * @param {Object} element HTML-Object which are checked
     * @param {boolean or integer} elementInView How much of an Element must be visible (true = all, integer as percentage)
    */
	TMS_VIS.IsElementInView = function(element, elementInView) {
		var viewPortTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
		var viewPortBottom = viewPortTop + Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
		var elementTop = element.getBoundingClientRect().top + document.documentElement.scrollTop;
		var elementHeight = element.offsetHeight;
		var elementBottom = elementTop + elementHeight;
		var inView = false;
		
		if (elementInView === true) {
			inView = ((viewPortTop < elementTop) && (viewPortBottom > elementBottom));
		} else if(elementInView > 1) {
			var elementPartHeight = elementHeight - elementHeight/100*elementInView;
			inView = ((elementTop > viewPortTop - elementPartHeight) && (elementBottom < viewPortBottom + elementPartHeight));
		} else {
			inView = (((elementTop >= viewPortTop) && (elementTop <= viewPortBottom)) || ((elementBottom >= viewPortTop) && (elementBottom <= viewPortBottom)));
		}
		return inView;
	};
	
	TMS_VIS.CheckElementImpression = function(){
		var elements = document.querySelectorAll('a[name]');
		
		for (var i = 0; i < elements.length; i++) {
			if( ( elements[i].getAttribute("data-tms-eimpr") == null || elements[i].getAttribute("data-tms-eimpr") == "0" )
					&& TMS_VIS.IsElementInView(elements[i], TMS_VIS.data.partInView) ){
				var timeNow = +new Date(); // get unix-timestamp in milliseconds
				if(elements[i].getAttribute("data-tms-eimprtime") == "0"){
					elements[i].setAttribute("data-tms-eimprtime",  timeNow );
				}else{
					//elements[i].addEventListener("click", TMS_VIS.ElementClick);
					elements[i].setAttribute("data-tms-eimpr",  '1' );
					if(TMS_VIS.data.debug){
						elements[i].style.backgroundColor = '#bcbcbc';
					}
				}
				// set impression time to 0, and next interval set impression time to timeNow
				elements[i].setAttribute("data-tms-eimprtime",  "0" );
			}
			//break;
		}
	};
	
	TMS_VIS.CheckElementPush = function(){
		var pushElements = [];
		var elements = document.querySelectorAll("[data-tms-eimpr='1']") || {};
		for (var i = 0; i < elements.length; i++) {
			var pId = elements[i].getAttribute(TMS_VIS.data.eAttr['name']) || false;
			if(pId){
				elements[i].setAttribute( "data-tms-eimpr", '2' );
				if(TMS_VIS.data.debug){
					elements[i].style.backgroundColor = '#00eb00';
				}
				pushElements.push(TMS_VIS.GetElementData(elements[i]));
			}
		}
		return pushElements;
	};
	
	TMS_VIS.ImpressionPush = function(){
		var pushElements = TMS_VIS.CheckElementPush();
		
		for (var i = 0; i < pushElements.length; i++) {
			window.dataLayer.push({
				"event" : "uaData",
				"eventCategory": "content.visibility",
				"eventAction":TMS_VIS.data.eList[i]['depth'],
				"eventLabel":pushElements[i].name,
				"eventValue":"0"
			});
		}
	};

	TMS_VIS.Init();
})();