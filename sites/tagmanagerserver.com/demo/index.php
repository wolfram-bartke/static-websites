<?php
$sBaseUrl = ($_SERVER["SERVER_NAME"] == 'localhost') ? 'http://localhost/TagManagerServer/webseite/' : 'https://www.tagmanagerserver.com/';

$sExamplePage = (empty($_GET['example'])) ? 'homepage' : $_GET['example'];

$aExamples = array();
$aExamples['homepage']['name'] = 'Homepage';
$aExamples['homepage']['dataLayer'][] = (object) [
  'pageType' => 'homepage',
  'visitorType' => 'external',
];
$aExamples['content']['name'] = 'Content Page';
$aExamples['content']['dataLayer'][] = (object) [
  'pageType' => 'content',
  'visitorType' => 'external',
];
$aExamples['contact']['name'] = 'Contact &amp; Conversion';
$aExamples['contact']['dataLayer'][] = (object) [
  'pageType' => 'contact',
  'visitorType' => 'external',
];
$aExamples['contact']['dataLayer'][] = (object) [
  'event' => 'uaData',
  'eventCategory' => 'conversion',
  'eventAction' => 'form.sent',
  'eventLabel' => 'Conversion ID:'.time(),
];
$aExamples['product.detail']['name'] = 'EEC Product Detail + AddToCart';
$aExamples['product.detail']['dataLayer'][] = (object) [
  'pageType' => 'product.detail',
  'visitorType' => 'external',
  'ecommerce' => (object) [
    'detail' => (object) [
      'actionField' => (object) [
        'list' => 'demo'
      ],
      'products' => array (
        0 => (object) [
          'id' => 'Tag Manager Server',
          'price' => '99.99',
          'brand' => 'HOKATA',
          'category' => 'server-based tracking',
          'variant' => 'v0.3',
        ],
      ),
    ]
  ]
];
$aExamples['product.detail']['dataLayer'][] = (object) [
  'event' => 'uaData',
  'eventCategory' => 'ecommerce',
  'eventAction' => 'addToCart',
  'ecommerce' => (object) [
    'add' => (object) [
      'actionField' => (object) [
        'list' => 'demo'
      ],
      'products' => array (
        0 => (object) [
          'id' => 'Tag Manager Server',
          'price' => '99.99',
          'brand' => 'HOKATA',
          'category' => 'server-based tracking',
          'variant' => 'v0.3',
          'quantity' => '1',
        ],
      ),
    ]
  ]
];
$aExamples['checkout.success']['name'] = 'EEC Checkout Success';
$aExamples['checkout.success']['dataLayer'][] = (object) [
  'pageType' => 'checkout.success',
  'visitorType' => 'external',
  'ecommerce' => (object) [
    'purchase' => (object) [
      'actionField' => (object) [
        'id' => time(),
        'revenue' => '99.99',
        'tax' => '19.00',
        'shipping' => '4.99',
        'coupon' => 'DOWNLOAD_123',
      ],
      'products' => array (
        0 => (object) [
          'id' => 'Tag Manager Server',
          'price' => '99.99',
          'brand' => 'HOKATA',
          'category' => 'server-based tracking',
          'variant' => 'v0.3',
        ],
      ),
    ]
  ]
];

$aDemoButtonHTML = [];
$aDataLayerHTML = [];
$aDataLayerHTML[] = 'window.dataLayer = window.dataLayer || [];';
foreach($aExamples as $sExample => $aExampleData){
  $sActiveClass = ($sExamplePage == $sExample) ? 'mdl-button--cta' : '';
  $aDemoButtonHTML[] = '<a href="demo/?example='.$sExample.'" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect '.$sActiveClass.'">'.$aExampleData['name'].'</a>';
  if(!empty($sActiveClass)){
    foreach($aExampleData['dataLayer'] as $aData){
      $aDataLayerHTML[] = 'window.dataLayer.push('.json_encode($aData, JSON_PRETTY_PRINT).');';
    }  
  }
}
$sDataLayerHTML = implode($aDataLayerHTML, "\n");

?><!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Server-side tracking Demo with Tag Manager Server. Check this Google Analytics tracking with all browsers (ITP & ETP) and blockers (AdBlock, Ghostery, ...).">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>Client vs Server-side Tracking: Tag Manager Server Demo to test all browser (ITP & ETP) and blocker (AdBlock, Ghostery, ...)</title>
    <base href="<?php print$sBaseUrl; ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="/fav/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/fav/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/fav/favicon-16x16.png">
    <link rel="mask-icon" href="/fav/safari-pinned-tab.svg" color="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <meta property="og:image" content="https://www.tagmanagerserver.com/img/tms-demo-fb.png" />
    <meta property="og:url" content="https://www.tagmanagerserver.com/demo/" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Tag Manager Server Demo to test all browser (ITP & ETP) and blocker (AdBlock, Ghostery, ...)" />
    <meta property="og:description" content="Server-side tracking Demo with Tag Manager Server. Check this Google Analytics tracking with all browsers (ITP & ETP) and blockers (AdBlock, Ghostery, ...)." />
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Tag Manager Server Demo to test all browser (ITP & ETP) and blocker (AdBlock, Ghostery, ...)">
    <meta name="twitter:description" content="Server-side tracking Demo with Tag Manager Server. Check this Google Analytics tracking with all browsers (ITP & ETP) and blockers (AdBlock, Ghostery, ...).">
    <meta name="twitter:site" content="@WolframBartke">
    <meta name="twitter:image" content="https://www.tagmanagerserver.com/img/tms-demo-share.png">
    <meta name="twitter:creator" content="@WolframBartke">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.grey-blue.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="tms.css?v=1" />
    <link rel="stylesheet" href="lightbox.css" />

    <!--<script src="tms/tms.js"></script>-->
    <script src="tms/tms-demo.js"></script>
    <script>
    function RenderGaRequest(sHitUrl, sTargetID){
      if(element = document.getElementById(sTargetID)){
        if(sHitUrl != ''){
          if(sTargetID == 'hit_tms'){
            oUrlParams = JSON.parse(sHitUrl);
            if(oUrlParams['ga-request']){
              aUrlParams = [];
              for(sVar in oUrlParams['ga-request'][0]){
                aUrlParams.push(sVar+"="+oUrlParams['ga-request'][0][sVar]);
              }
              element.innerText = element.innerText + decodeURIComponent(aUrlParams.join("&")) + "\n\n" + decodeURIComponent(aUrlParams.join("\n")) + "\n\n\n";
            }
          }

          if(sTargetID == 'hit_ga_gtm'){

          aUrlParams = sHitUrl.split('&');
          element.innerText = element.innerText + decodeURIComponent(sHitUrl) + "\n\n" + decodeURIComponent(aUrlParams.join("\n")) + "\n";
          if(element = document.getElementById("GA_done")){
            element.classList.remove("block");
            element.classList.add("success");
            element.innerText = 'done';
          }
        }


        }
        

        
      }
    }

<?php  array_shift($aDataLayerHTML); ?>
<?php print array_shift($aDataLayerHTML); ?>
    </script>
    
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MXCK8WQ');</script>
<!-- End Google Tag Manager -->
<?php print '<script>'.implode("\n", $aDataLayerHTML).'</script>'; ?>
<body>
<div class="hokata-layout-transparent mdl-layout mdl-js-layout">

  
<main class="mdl-layout__content">



<div class="section-entry mdl-typography--text-center" style="padding:20px 20px;">
  <div style="max-width:200px;display:inline-block;"><a href="<?php print$sBaseUrl; ?>"><img src="tag-manager-server.png" alt="Tag Manager Server" width="100%" /></a></div>
</div>



<div class="mdl-grid section-content">

  <div class="mdl-cell mdl-cell--12-col">
    


    <div class="mdl-card__title">
      <h1 class="mdl-card__title-text">Tag Manager Server Demo - Client vs Server-side Tracking: <br>Google Analytics + Google Tag Manager vs. Tag Manager Server</h1>
    </div>
  </div> <!-- end mdl-grid section-content -->

  <div class="mdl-cell mdl-cell--4-col">
    <div class="mdl-card__supporting-text">
    <img src="img/test-chrome.png" alt="Chrome trackig Test" width="100%" class="lightbox_opener" />
      <p>
        Chrome without Tracking Protection<br><i>Result: Tracking works quite normally</i>
      </p>
    </div>
  </div> <!-- end mdl-grid section-content -->
  <div class="mdl-cell mdl-cell--4-col">
    <div class="mdl-card__supporting-text">
    <img src="img/test-firefox.png" alt="Firefox trackig Test" width="100%" class="lightbox_opener" />
    <p>
        Firefox with AdBlocker<br><i>Result: GTM is running, but Analytics tracking is blocked</i>
      </p>
    </div>
  </div> <!-- end mdl-grid section-content -->
  <div class="mdl-cell mdl-cell--4-col">
    <div class="mdl-card__supporting-text">
    <img src="img/test-brave.png" alt="Brave trackig Test" width="100%" class="lightbox_opener" />
    <p>
        Brave Browser<br><i>Result: GTM and Analytics tracking is blocked</i>
      </p>
    </div>
  </div> <!-- end mdl-grid section-content -->

  <div class="mdl-cell mdl-cell--12-col">
    <div class="mdl-card__supporting-text">
      <h2 style="padding-left:0;">Page Examples</h2>
      <p>
        Open different Browser and check this Analytics tracking methods with different browser settings like ITP or ETP and activate common blockers like AdBlock or Ghostery.
      </p>
<?php
print implode(' &nbsp; ', $aDemoButtonHTML);
?>
     </div>

      <h2>DataLayer Data for active Page Example</h2>
      <pre style="margin:0 20px 20px 20px;padding:20px;background-color:#ebebeb;">
<?php
print $sDataLayerHTML;
?>
      </pre>
    </div>

  </div> <!-- end mdl-grid section-content -->


	<div class="mdl-grid section-content" style="background-color:#ebebeb">
			
			
			  <div class="mdl-cell mdl-cell--12-col">
          <h2>Tracking Request Results</h2>
          <p style="padding-left:20px;"><small>
            <b>Your User-Agent</b>: <?php print $_SERVER['HTTP_USER_AGENT'] ?>
          </small></p>
			  </div>
			
			<div class="mdl-cell mdl-cell--6-col">
			<h3>Google Tag Manager + Google Analytics</h3>
<ul class="mdl-list teaser-list">
  <li class="mdl-list__item mdl-list__item--three-line">
    <span class="mdl-list__item-primary-content">
      <i class="material-icons  mdl-list__item-avatar success">done</i>
      <div>DataLayer<br><small>DataLayer HTML-Code Inegration incl. Push Events</small></div>
    </span>
  </li>
  <li class="mdl-list__item mdl-list__item--three-line">
    <span class="mdl-list__item-primary-content">
      <i id="GTM_done" class="material-icons mdl-list__item-avatar block">block</i>
      <div>Google Tag Manager <br><small>GTM JavaScript file loaded and processed.</small></div>
    </span>
  </li>
  <li class="mdl-list__item mdl-list__item--three-line">
    <span class="mdl-list__item-primary-content">
      <i id="GA_done" class="material-icons  mdl-list__item-avatar block">block</i>
      <div>Google Analytics<br><small>Load analytics.js file and send GA-request</small></div>
    </span>
  </li>
</ul>
<pre id="hit_ga_gtm" style="overflow:auto;background-color:#fff;max-height:250px;margin:0 0 0 20px;">
Google Analytics Requests:
</pre>
			
			</div>
			
			<div class="mdl-cell mdl-cell--6-col">
      <h3>Tag Manager Server</h3>
<ul class="mdl-list teaser-list">
<li class="mdl-list__item mdl-list__item--three-line">
  <span class="mdl-list__item-primary-content">
    <i class="material-icons  mdl-list__item-avatar success">done</i>
    <div>DataLayer (same as GTM + GA)<br><small>DataLayer HTML-Code Inegration incl. Push Events</small></div>
  </span>
</li>
  <li class="mdl-list__item mdl-list__item--three-line">
    <span class="mdl-list__item-primary-content">
      <i class="material-icons mdl-list__item-avatar success">done</i>
      <div>Tag Manager Server<br><small>Tracking configuration via GTM-JSON Import.</small></div>
    </span>
  </li>
  <li class="mdl-list__item mdl-list__item--three-line">
    <span class="mdl-list__item-primary-content">
      <i class="material-icons  mdl-list__item-avatar success">done</i>
      <div>server-side Google Analytics tracking<br><small>All Google Analytics requests are Server 2 Server</small></div>
    </span>
  </li>
</ul>
<pre id="hit_tms" style="overflow:auto;background-color:#fff;max-height:250px;margin:0 0 0 20px;">
server-side Google Analytics Requests:
</pre>		
			</div>
      </div>
      



      <div class="mdl-grid section-content">
			
      <div class="mdl-cell mdl-cell--6-col contentData" style="padding-top:26px;" data-pid="profile_keyfacts" data-plist="main.content" data-pname="Bei HOKATA sind Sie genau richtig...">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text"><a name="what-is-tms">What is the Tag Manager Server?</a></h2>
            </div>
            <div class="mdl-card__supporting-text">
              <p>
                The Tag Manager Server moves most tracking scripts from the browser to a server.
                The processing of the DataLayer works like the Google Tag Manager &mdash; of course all e-commerce events are taken into account.
              </p>
              <p>
                More informations: <a href="https://www.tagmanagerserver.com/">www.tagmanagerserver.com</a>
              </p>
            </div>
        </div>

        <div class="mdl-cell mdl-cell--6-col contentData" style="padding-top:26px;" data-pid="profile_keyfacts" data-plist="main.content" data-pname="Bei HOKATA sind Sie genau richtig...">
            <img src="img/tag-manager-server.png" alt="Google Analytics Tracking with the Tag Manager Server" width="100%" class="lightbox_opener">
        </div>






  </div> <!-- end mdl-grid section-content -->
			


            <div class="mdl-grid section-footer">
			  <div class="mdl-cell mdl-cell--6-col footer-left">
			    <p>&copy; 2020 HOKATA Online Marketing</p>
				<p>Blumenstraße 44<br>70182 Stuttgart</p>
			  </div>
			  <div class="mdl-cell mdl-cell--6-col footer-right">
			    <p><a href="https://www.hokata.de/kontakt.html" target="_blank">Impressum</a> | <a href="https://www.hokata.de/kontakt.html" target="_blank">Datenschutz</a></p>
			  </div>
            </div>

        </main>
  
</div>


<div id="modal_lightbox" class="lightbox_modal">
  
  <div class="modal-content">
  
    <div id="lightbox_slides">
      <div class="mySlides">
        <img src="img/gtm-analytics-tracking.png" alt="" />
      </div>
    </div>
	<span class="close cursor" onclick="closeModal()">&times;</span>
    <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
    <a class="next" onclick="plusSlides(1)">&#10095;</a>

    <div class="caption-container">
      <p id="caption"></p>
    </div>

  </div>
</div>
<script>
function openModal() {
  document.getElementById("modal_lightbox").style.display = "block";
}

function closeModal() {
  document.getElementById("modal_lightbox").style.display = "none";
}

var contentAnker = document.querySelectorAll("a[name]");
for (var i = 0; i < contentAnker.length; i++) {
  contentAnker[i].setAttribute('data-depth', i+1);
}

var opener = document.getElementsByClassName("lightbox_opener");
var aSlideHtml = [];
for (var i = 0; i < opener.length; i++) {
  opener[i].setAttribute('data-nr', i+1);
  opener[i].addEventListener("click", clickOpener);
  aSlideHtml.push('<div class="mySlides"><img src="'+opener[i].src+'" alt="" /></div>');
}
document.getElementById("lightbox_slides").innerHTML = aSlideHtml.join('');
function clickOpener(){
	openModal();
	currentSlide(parseInt(this.getAttribute('data-nr')));
}

var slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  var i;
  var slides = document.getElementsByClassName("mySlides");
  var captionText = document.getElementById("caption");
  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
  }
  slides[slideIndex-1].style.display = "block";
  captionText.innerHTML = opener[slideIndex-1].alt;
}

document.onkeydown = checkKey;

function checkKey(e) {
    e = e || window.event;
	if (e.key == 'ArrowLeft') { // left arrow
       plusSlides(-1);
    }
    else if (e.key == 'ArrowRight') { // right arrow
       plusSlides(1);
	}
    else if (e.key == 'Escape') { // right arrow
       closeModal();
    }

}
</script>

    <script src="https://code.getmdl.io/1.0.6/material.min.js"></script>
</body>

</html>
