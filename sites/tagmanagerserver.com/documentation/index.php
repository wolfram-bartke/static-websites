<?php
$sBaseUrl = ($_SERVER["SERVER_NAME"] == 'localhost') ? 'http://localhost/TagManagerServer/webseite/' : 'https://www.tagmanagerserver.com/';
$sBaseLink = $sBaseUrl.'documentation/';
?><!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Tag Manager Server Documentation">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>Tag Manager Server Documentation</title>
    <base href="<?php print$sBaseUrl; ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="/fav/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/fav/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/fav/favicon-16x16.png">
    <link rel="mask-icon" href="/fav/safari-pinned-tab.svg" color="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <meta property="og:image" content="https://www.tagmanagerserver.com/img/tms-demo-fb.png" />
    <meta property="og:url" content="https://www.tagmanagerserver.com/documentation/" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Tag Manager Server Documentation" />
    <meta property="og:description" content="" />
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Tag Manager Server Documentation">
    <meta name="twitter:description" content="">
    <meta name="twitter:site" content="@WolframBartke">
    <meta name="twitter:image" content="https://www.tagmanagerserver.com/img/tms-demo-share.png">
    <meta name="twitter:creator" content="@WolframBartke">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.grey-blue.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="tms.css?v=1" />
    <link rel="stylesheet" href="lightbox.css" />

    <style>
    code {
      background-color:#ebebeb;
    }
    .more_space li{
      margin-bottom:7px;
    }
    </style>

    <script src="tms/tms.js"></script>
    <script>window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event':'gtm.js'})</script>

<body>
<div class="hokata-layout-transparent mdl-layout mdl-js-layout">

  
<main class="mdl-layout__content">



<div class="section-entry mdl-typography--text-center" style="padding:20px 20px;">
  <div style="max-width:200px;display:inline-block;"><a href="<?php print$sBaseUrl; ?>"><img src="tag-manager-server.png" alt="Tag Manager Server" width="100%" /></a></div>
</div>



<div class="mdl-grid section-content">

  <div class="mdl-cell mdl-cell--12-col">
    <div class="mdl-card__supporting-text">
      <h1 style="padding-left:0;">Tag Manager Server Documentation</h1>
      <p>
        Server-side Google Analytics tracking with the Tag Manager Server:<br><a href="https://www.tagmanagerserver.com">https://www.tagmanagerserver.com</a> <em>(open for Beta Registration)</em>
      </p>
      <p>
        Live demo to compare Google Tag Manager vs Tag Manager Server:<br><a href="https://www.tagmanagerserver.com/demo/">https://www.tagmanagerserver.com/demo/</a> <em>(test with different browser and settings)</em>
      </p>



    </div>

  </div>
  </div> <!-- end mdl-grid section-content -->


	<div class="mdl-grid section-content" style="background-color:#ebebeb">
  <div class="mdl-cell mdl-cell--12-col">
    <div class="mdl-card__supporting-text">
      <h2 style="padding-left:0;">Table of Contents</h2>
      <ul>
<li><a href="<?php print $sBaseLink;?>#what-is-tms">What is the Tag Manager Server?</a></li>
<li><a href="<?php print $sBaseLink;?>#supported-tags-triggers-and-variables">Supported tags, trigger and variables</a>
<ul>
<li><a href="<?php print $sBaseLink;?>#supported-tags">Tags</a></li>
<li><a href="<?php print $sBaseLink;?>#supported-trigger">Trigger</a></li>
<li><a href="<?php print $sBaseLink;?>#supported-variables">Variables</a></li>
<!--<li><a href="<?php print $sBaseLink;?>#compatibility-check">GTM-Container compatibility check</a></li>-->
</ul>
</li>
<li><a href="<?php print $sBaseLink;?>#tms-specials">TMS Specials</a>
<ul>
<li><a href="<?php print $sBaseLink;?>#special-variables">Individual Tag Manager Server Variables</a></li>
<li><a href="<?php print $sBaseLink;?>#special-push-handling">Specific dataLayer-push-event handling</a></li>
</ul>
</li>
<li><a href="<?php print $sBaseLink;?>#installation">Installation</a>
<ul>
<li><a href="<?php print $sBaseLink;?>#preparation">Preparation</a></li>
<li><a href="<?php print $sBaseLink;?>#implementation">Implementation</a>
<ul>
<li><a href="<?php print $sBaseLink;?>#subfolder-variant">Subfolder variant</a></li>
<li><a href="<?php print $sBaseLink;?>#subfolder-proxy-variant">Subfolder proxy variant</a></li>
<li><a href="<?php print $sBaseLink;?>#subdomain-variant">Subdomain variant</a></li>
</ul>
</li>
</ul>
</li>
<li><a href="<?php print $sBaseLink;?>#debugging">Debugging</a>
<ul>
<li><a href="<?php print $sBaseLink;?>#debug-console">TMS Debug Console (chrome extension)</a></li>
<li><a href="<?php print $sBaseLink;?>#troubleshooting-your-first-run">Troubleshooting your first run</a></li>
</ul>
</li>
</ul>
     </div>


    </div>

  </div> <!-- end mdl-grid section-content -->



  <div class="mdl-grid section-content">
			
      <div class="mdl-cell mdl-cell--6-col contentData" style="padding-top:26px;">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text"><a name="what-is-tms">What is the Tag Manager Server?</a></h2>
            </div>
            <div class="mdl-card__supporting-text">
              <p>
                The Tag Manager Server moves most tracking scripts from the browser to a server. The processing of the DataLayer is the same as Google Tag Manager — of course, all e-commerce events are taken into account.
              </p>
              <p>
                Variables and triggers can be created with the Google Tag Manager as before. GTM container will be exported and imported via JSON-file.
              </p>
              <p>
              All DataLayer push events are automatically sent to the Tag Manager server, processed there, and sent to Google Analytics via the Measurement Protocol.
              </p>
            </div>
        </div>

        <div class="mdl-cell mdl-cell--6-col contentData" style="padding-top:26px;">
            <img src="img/tag-manager-server.png" alt="Google Analytics Tracking with the Tag Manager Server" width="100%" class="lightbox_opener">
        </div>
			
      <div class="mdl-cell mdl-cell--12-col" style="padding-top:26px;">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text">What is the TMS not?</h2>
            </div>
            <div class="mdl-card__supporting-text">
              <p>
                In most cases, this is <strong>no replacement for the Google Tag Manager</strong>.
              </p>
              <ul>
                <li>
                  the TMS only supports the most important Universal Analytics functions of the Google Tag Manager
                  <ul>
                    <li>
                      full list of TMS features: <a href="https://www.tagmanagerserver.com/en/#functions" rel="nofollow">https://www.tagmanagerserver.com/en/#functions</a>
                    </li>
                  </ul>
                </li>
                <li>
                  no third party tags supported
                </li>
                <li>
                  my recommendation: <strong>track all TMS hits in an additional Google Analytics property</strong>
                </li>
              </ul>
            </div>
        </div>
			
      <div class="mdl-cell mdl-cell--12-col" style="padding-top:26px;">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text">So what does the TMS?</h2>
            </div>
            <div class="mdl-card__supporting-text">
              <p>
                The Tag Manager Server provides more accurate web analytics data with minimal effort.
              </p>
              <ul>
                <li>bypass AdBlocker, ITP and ETP with server-side tracking</li>
                <li>track more sessions</li>
                <li><strong>generates much better E-commerce data!</strong> 🚀</li>
              </ul>

            </div>
        </div>

  </div> <!-- end mdl-grid section-content -->
      
  
  <div class="mdl-grid section-content" style="background-color:#ebebeb">
			
      <div class="mdl-cell mdl-cell--12-col" style="padding-top:26px;">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text"><a name="supported-tags-triggers-and-variables">Supported tags, triggers and variables</a></h2>
            </div>
            <div class="mdl-card__supporting-text">

              <p>
                <a name="supported-tags"><strong>Tags</strong></a>
              </p>
              
              <ul>
                <li>Universal Analytics Tag<ul>
                  <li>with pageviews and events</li>
                  <li>custom dimensions and metrics</li>
                  <li>Enhanced Ecommerce Tracking</li>
                  <li>overriding setting in tags not supported</li>
                </ul>
              </li>
            </ul>
            <p><em>everything else is ignored</em></p>
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="supported-trigger"><strong>Trigger</strong></a>
              </p>
              
              <ul>
                <li>Types
                  <ul>
                    <li>Page View</li>
                    <li>DOM Ready</li>
                    <li>Window Loaded</li>
                    <li>User-defined events</li>
                  </ul>
                </li>
                <li>Filters
                  <ul>
                    <li>all trigger filters, except the CSS_SELECTOR, are already handled</li>
                    <li>PHP interprets regular expressions, so you must translate all JavaScript RegEx patterns to PHP before exporting the GTM Container</li>
                  </ul>
                </li>
                <li>
                  <ul>
                    <li>all trigger filters, except the CSS_SELECTOR, are already handled</li>
                    <li>PHP interprets regular expressions, so you must translate all JavaScript RegEx patterns to PHP before exporting the GTM Container</li>
                  </ul>
                </li>
              </ul>
            <p><em>everything else is ignored</em></p>
            
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="supported-variables"><strong>Variables</strong></a>
              </p>
              
              <ul>
                <li>HTTP Referrer</li>
                <!--<li>URL</li>-->
                <li>Data Layer Variable</li>
                <li>Custom Event</li>
                <li>Google Analytics Settings</li>
                <li>Constant</li>
                <li>RegEx Table (use PHP RegEx patterns!)</li>
                <li>Lookup Table</li>
              </ul>
              
              <p><em>everything else is ignored</em></p>
            
            </div>

        </div>

        <!--
        <div class="mdl-cell mdl-cell--6-col" style="padding-top:26px;">

          <div class="mdl-card__supporting-text">
              <p>
                <a name="compatibility-check"><strong>GTM-Container compatibility check</strong></a>
              </p>
              <img src="img/tms-gtm-container-test-tool.png" alt="GTM-Container compatibility check" width="100%" class="lightbox_opener">
              <p>Check the compatibility of your GTM-Container JSON-File for<br>tags, variables and trigger with this tool:</p>
              <p><a href="demo/test.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--cta">GTM-Container Test-Tool</a></p>
          </div>
        </div>
  -->
              
			
  </div> <!-- end mdl-grid section-content -->
  

  <div class="mdl-grid section-content" style="background-color:#ebebeb">
			
      <div class="mdl-cell mdl-cell--12-col" style="padding-top:26px;">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text"><a name="tms-specials">TMS Specials</a></h2>
            </div>
            
            
            <div class="mdl-card__supporting-text">
            <p>A few little things of the Tag Manager Server make it easier or bring a significant performance boost. And are useful for many projects.</p>
              <p>
                <a name="special-variables"><strong>Individual Tag Manager Server Variables</strong></a>
              </p>
              <p>
              The Individual Tag Manager Server variables can be used in the GTM via a constant. It is important to use the correct variable name, as listed below, and to select the variable type constant.
              </p>

              <ul>
                <li><code style="background-color:#fff;">TMS_TIMESTAMP</code> (current ISO 8601 date)</li>
                <li><code style="background-color:#fff;">TMS_USERAGENT</code> (User agent of the visitor browser)</li>
                <li><code style="background-color:#fff;">TMS_SESSIONID</code> (GA Session ID for session scope custom dimension, example: 1591796057.bF6XNLuB)</li>
                <li><code style="background-color:#fff;">TMS_CONTAINER</code> (GTM Container filename)</li>
              </ul>
              
              <p><em>These variables should not be missing in any tracking setup, and more will follow.</em></p>
            
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="special-push-handling"><strong>Specific dataLayer-push-event handling</strong></a>
              </p>
              <p>
              With the Google Tag Manager it is almost irrelevant how many DataLayer Push events are generated. Because all events are only processed in JavaScript.
              <br>
              This is different with TMS because every push event generates a request to the server. For large pages with many push events, unnecessary server requests should be avoided.
              <br>
              You can define include and exclude event names at line 10 and 11 in tms.js.
              </p>

              <p>
              <i>Note: All data of the suppressed push events do not exist in the server-side DataLayer.</i>
              </p>

              <ul>
                <li><code style="background-color:#fff;">TMS.eventsInclude = [];</code><br>(only these events generate a request to the TMS, with an empty array all requests are sent.)</li>
                <li><code style="background-color:#fff;">	TMS.eventsExclude = ['gtm.scrollDepth', '...'];</code><br>(these events do not generate a request to the TMS)</li>
              </ul>
              
              <p><em>These variables should not be missing in any tracking setup, and more will follow.</em></p>
            
            </div>

        </div>
  </div> <!-- end mdl-grid section-content -->














  <div class="mdl-grid section-content">
			
      <div class="mdl-cell mdl-cell--12-col" style="padding-top:26px;">
            <div class="mdl-card__title">
                <h2 class="mdl-card__title-text"><a name="installation">Installation</a></h2>
            </div>
            <div class="mdl-card__supporting-text">

              <p>
                This Tag Manager Server can be implemented in three different ways:
              </p>
              
              <ol>
                <li><strong>Subfolder</strong>: website or online shop integration
                  <ul>
                    <li>no additional server required</li>
                    <li>access to first-party cookies via TMS</li>
                  </ul>
                </li>
                <li><strong>Subfolder proxy</strong>: for example via htaccess proxy
                  <ul>
                    <li>performance flexibility</li>
                    <li>centralize TMS tracking for multiple pages on one server</li>
                  </ul>
                </li>
                <li><strong>Subdomain</strong>:
                  <ul>
                    <li>performance flexibility</li>
                    <li>implementation for non PHP pages on another PHP server</li>
                  </ul>
                </li>
              </ol>
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="preparation"><strong>Preparation</strong></a>
              </p>
              
              <ol>
                <li><strong>Create a new Google Analytics Property</strong> for TMS Tracking</li>
                <li><strong>Build a Google Tag Manager Container</strong>
                  <ul>
                    <li><strong>or import</strong> the <i>/tms/GTM-Container/GTM-MIN.json</i> into GTM</li>
                    <li>If you <strong>use an existing container</strong> reduce it to the supportet <a href="#supported-tags-triggers-and-variables">supported tags, triggers and variables</a></li>
                    <li><strong>Set the GA-ID</strong> to your new TMS Property-ID</li>
                    <li><strong>Export this container</strong> to JSON-file (e.g. /GTM-Container/GTM-XXXXXXX.json)</li>
                    <li><strong>All changes</strong> to this container must be exported and uploaded again</li>
                  </ul>
                </li>
                <li>Make sure <strong>PHP curl requests incl. SSL is executable</strong> on your server (e.g. check your PHP configuration with <code>phpinfo()</code>)</li>
              </ol>
            
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="implementation"><strong>Implementation</strong></a>
              </p>
              
              <ol>
                <li><strong>All files in the subfolder /tms/ are necessary</strong> to get TMS running.
                  <ul>
                    <li><strong>Create a copy</strong> of this folder for your project</li>
                    <li>Google Tag Manager JSON-files are <strong>protected by an htaccess</strong> file in the subfolder /tms/GTM-Container</li>
                    <li><strong>Preview containers</strong> can also be loaded from this folder (via chrome extension: <a href="https://chrome.google.com/webstore/detail/tag-manager-server-debug/camhbggldedhfjbjabcndiggmpfghkdp" target="_blank">TMS Debug Console</a>)</li>
                  </ul>
                </li>
                <li><strong>Configure the TMS in your config.php file</strong>
                  <ul>
                    <li>
                      Set your <strong>account credentials</strong>
                      <ul>
                        <li><code>$aConfig['default']['email']</code></li>
                        <li><code>$aConfig['default']['key']</code></li>
                      </ul>
                    </li>
                    <li>
                      Set default <strong>TMS configuration</strong> 
                      <ul>
                        <li><code>$aConfig['default']['cookieHost']</code> root domain is often suitable</li>
                        <li><code>$aConfig['default']['cookieName']</code> name of your TMS clientID cookie</li>
                        <li><code>$aConfig['default']['gtmContainerFile']</code> path to the JSON-container file</li>
                        <li><code>$aConfig['default']['debugAuth']</code> auth code for the <a href="https://chrome.google.com/webstore/detail/tag-manager-server-debug/camhbggldedhfjbjabcndiggmpfghkdp" target="_blank">TMS Debug Console</a> chrome extension</li>
                        <li><code>$aConfig['default']['overWriteUserIP']</code> overwrite user IP address before sending to Google Analytics</li>
                      </ul>
                    </li>
                    <li>
                      You can overwrite all config values with a <strong>special domain configuration</strong> if needed
                      <ul>
                        <li><code>$aConfig['tagmanagerserver.com']['cookieHost'] = '_tms.com';</code></li>
                        <li><code>$aConfig['localhost']['gtmContainerFile'] = 'GTM-Container/GTM-TEST-loalhost.json'</code></li>
                      </ul>
                    </li>
                  </ul>
                </li>
                <li><strong>Upload your files</strong> to a subfolder on your tracking server (e.g. your-domain.com/tms )
                  <ul>
                    <li>tms.js (maybe minify before upload)</li>
                    <li>api.php</li>
                    <li>TMS.php</li>
                    <li>config.php</li>
                    <li>GTM-Container/GTM-XXXXXXX.json</li>
                    <li><strong>Set write permissions for PHP to /GTM-Container/ folder </strong>
                      <br>For performance reasons, the GTM container is automatically reduced to the supported functions and cached. For this reason, the folder containing the container files must be writable by php (e.g. 755).
                    </li>
                  </ul>
                </li>
                <li><strong>Include the tms.js Script</strong> in your HTML-code <strong>before your first dataLayer initialization and GTM integration</strong>
                  <ul>
                    <li>Example: <code>&lt;script src="/tms/tms.js"&gt;&lt;/script&gt;</code></li>
                    <li><strong>Never overwrite the dataLayer</strong> and use the <code>dataLayer.push()</code> function</li>
                  </ul>
                </li>
              </ol>
            
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="subfolder-variant"><strong>Subfolder variant</strong></a>
              </p>
              <p>
              <strong>Congratulation!</strong> You are ready to go.
                <br>
                <i>Generate your first page view and check the result in the Real-Time report of your Google Analytics property.</i>
              </p>
            
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="subfolder-variant"><strong>Subfolder proxy variant</strong></a>
              </p>
              <p>
                An alternative implementation to <strong>centralize TMS tracking</strong> for multiple pages on one server.
              </p>
              <p>
                Create a subfolder with this htaccess-proxy-file:
              <br>
              <code>
RewriteEngine On<br>
RewriteRule ^api.php$ https://your.tracking-domain.com/api.php [P]<br>
RewriteRule ^tms.js$ https://your.tracking-domain.com/tms.js [P]
              </code>
              </p>
              <p>
              You may need to activate some required Apache modules for mod_proxy.
              </p>
            </div>

            <div class="mdl-card__supporting-text">
              <p>
                <a name="subdomain-variant"><strong>Subdomain variant</strong></a>
              </p>
              <p>
                An alternative implementation for non PHP pages or for <strong>load balancing</strong> on another PHP server.
              </p>
              <p>
                Include the tms.js Script before your first dataLayer call and GTM integration from your tracking subdomain.
              </p>
              <code>
&lt;script src="https://tracking.your-domain.com/tms.js"&gt;&lt;/script&gt;
              </code>
              <p>
              To set first-party cookies from this subdomain, the site or online store may need to send additional header information:
              </p>
              <code>
access-control-allow-credentials: true
access-control-allow-origin: https://tracking.your-domain.com
              </code>
            </div>

        </div>
			
  </div> <!-- end mdl-grid section-content -->


  <div class="mdl-grid section-content" style="background-color:#ebebeb">
			
      

          <div class="mdl-cell mdl-cell--12-col">
          <div class="mdl-card__title">
                <h2 class="mdl-card__title-text"><a name="debugging">Debugging</a></h2>
            </div>
          </div>

          <div class="mdl-cell mdl-cell--6-col">
            <div class="mdl-card__supporting-text">
              <p>
                <a name="debug-console"><strong>TMS Debug Console (chrome extension)</strong></a>
              </p>
              <ol class="more_space">
                <li>
                  Download the Chrome extension from chrome web store:<br>
                  <a href="https://chrome.google.com/webstore/detail/tag-manager-server-debug/camhbggldedhfjbjabcndiggmpfghkdp" target="_blank">https://chrome.google.com/webstore/detail/tag-manager-server-debug/camhbggldedhfjbjabcndiggmpfghkdp</a>
                </li>
                <li>
                  Set the <em>debugAuth</em> code in your <b>config.php</b>:<br>
                  <code style="background-color:#fff;">$aConfig['default']['debugAuth'] = '...';</code>
                </li>
                <li>Navigate to your Page with TMS implementation</li>
                <li>Open the TMS Debug Console Settings with a click on the extension icon</li>
                <ul>
                  <li>Set the same authCode as before</li>
                  <li>Activate Debug Console and debugMode if needed</li>
                  <li>Test new container from your server</li>
                </ul>
                
              </ol>
            </div>
          </div>
            
          <div class="mdl-cell mdl-cell--6-col">
            <div class="mdl-card__supporting-text">
              <img src="documentation/img/tms-debug-console-01.png" alt="TMS Debug Console example with activated debugMode and GTM preview container." width="100%" class="lightbox_opener">
            </div>
          </div>


          <div class="mdl-cell mdl-cell--6-col">
            <div class="mdl-card__supporting-text">
              <p>
                <b>Debug Console settings &amp; information</b>
              </p>
              <p>
              The <strong>coloured background</strong> of the numbers shows the status of the current server-side tracking:
              </p>
              <ul>
              <li><strong>grey:</strong> inactive Debug Console</li>
              <li><strong>green:</strong> Browser console logs activated</li>
              <li><strong>orange:</strong> activated debug mode or container preview</li>
              <li><strong>red:</strong> invalid debugAuth code or TMS error</li>
              </ul>
              <p>
              The <strong>two numbers</strong> show:
              </p>
              <ul>
              <li><strong>Tag Manager Server Input</strong> shows the submitted data from the browser to the TMS API.</li>
              <li><strong>Tag Manager Server Action</strong> shows the server request of the TMS via the measurement protocol to Google Analytics.</li>
              </ul>
            </div>
          </div>
            
          <div class="mdl-cell mdl-cell--6-col">
            <div class="mdl-card__supporting-text">
              <img src="documentation/img/tms-debug-console-02.png" alt="grey [- : -] &gt;&gt; inactive Debug Console: " width="30%" style="margin-right:5px;" class="lightbox_opener">
              <img src="documentation/img/tms-debug-console-03.png" alt="green [2 : 2] &gt;&gt; activated Browser console logs with 2 TMS inputs and 2 TMS actions" width="30%" style="margin-right:5px;" class="lightbox_opener">
              <img src="documentation/img/tms-debug-console-04.png" alt="orange [2 : 2] &gt;&gt; activated Browser console logs with DEBUG_MODE=0, to test live environments" width="30%" style="margin-right:5px;" class="lightbox_opener">
              <img src="documentation/img/tms-debug-console-05.png" alt="purple [8 : 8] &gt;&gt; activated Browser console logs with DEBUG_MODE=0 and container preview" width="30%" style="margin-right:5px;" class="lightbox_opener">
              <img src="documentation/img/tms-debug-console-06.png" alt="red [auth] &gt;&gt; invalid debugAuth code" width="30%" style="margin-right:5px;" class="lightbox_opener">
              <img src="documentation/img/tms-debug-console-07.png" alt="red [error] &gt;&gt; Tag Manager Server error, more information in browser log" width="30%" style="margin-right:5px;" class="lightbox_opener">
            </div>
          </div>

          <div class="mdl-cell mdl-cell--10-col">
            <div class="mdl-card__supporting-text">
              <p>
                <b>Browser console output</b>
              </p>
              <p>
                Once you have activated the Debug Console with the appropriate debugAuth code, the browser console will display the most important tracking information.
              </p>
            </div>
          </div>



          <div class="mdl-cell mdl-cell--6-col">
            <div class="mdl-card__supporting-text">
            <p>
                <strong>Pageview example with Enhanced Ecommerce detail view</strong><br>
              </p>
              <img src="documentation/img/tms-debug-console-log1.png" alt="Browser console with pageview example with Enhanced Ecommerce product detail view" width="100%" class="lightbox_opener">
            </div>
          </div>

          <div class="mdl-cell mdl-cell--6-col">
            <div class="mdl-card__supporting-text">
              <p>
                <strong>Event example with Enhanced Ecommerce add to cart action</strong>
              </p>
              <img src="documentation/img/tms-debug-console-log2.png" alt="Browser console with event example with Enhanced Ecommerce add to cart action" width="100%" class="lightbox_opener">
              
            </div>
         

        </div>


        <div class="mdl-cell mdl-cell--12-col" style="padding-top:26px;">
            <div class="mdl-card__supporting-text">

              <p>
                <a name="troubleshooting-your-first-run"><strong>Troubleshooting your first run</strong></a>
              </p>
              
              <ol>
                <li>Generate your first page view</li>
                <li>Check the Real-Time in Google Analytics</li>
                <li>Make sure you did not reinitialize dataLayer after tms.js integration
                  <ul>
                    <li>For this reason, use such a template during dataLayer initialization: window.dataLayer = window.dataLayer [];</li>
                    <li>And then use dataLayer.push calls to extend the DataLayer</li>
                  </ul>
                </li>
                <li>Check Browser console messages in your browser DevTools for
                  <ul>
                    <li>JavaScript errors</li>
                    <li>Use the <a href="https://chrome.google.com/webstore/detail/tag-manager-server-debug/camhbggldedhfjbjabcndiggmpfghkdp" target="_blank">TMS Debug Console</a> chrome extension</li>
                  </ul>
                </li>
                <li>Check the presence and status code (200) for two requests in the network communication
                  <ul>
                    <li>tms.js</li>
                    <li>api.php
                      <ul>
                        <li>check the response of this XHR request</li>
                      </ul>
                    </li>
                  </ul>
                </li>
                <li>Make sure that your Google Tag Manager export JSON-file is representing a valid JSON syntax</li>
                <li>Check write permissions of the /GTM-Container/ folder</li>
                <li>Verify the UA-ID in your Google Tag Manager container</li>
                <li>Make sure you installed the PHP curl module and not to block it in your php.ini (e. g. using disable_functions).</li>
                <li>Start from the beginning with this test example: /test/index.php
                  <ul>
                    <li>So you can compare the functionality of your Tag Manager Container with the TMS version.</li>
                  </ul>
                </li>
              </ul>
            </div>
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
