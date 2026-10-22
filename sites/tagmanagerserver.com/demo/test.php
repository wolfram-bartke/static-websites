<?php
$sBaseUrl = ($_SERVER["SERVER_NAME"] == 'localhost') ? 'http://localhost/TagManagerServer/webseite/' : 'https://www.tagmanagerserver.com/';
$sUploadFolder = "./uploads";

require_once( "FileUpload.php" );
$FileUpload = new FileUpload\FileUpload();
$FileUpload->setInput( "file" );
$FileUpload->setAllowMimeType("application/json");
$FileUpload->setDestinationDirectory($sUploadFolder);
$FileUpload->setAutoFilename();
$FileUpload->save();

?><!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Server-side tagging with Tag Manager Server. Check your GTM-Container JSON-File for compatible Tag, Variables and Trigger.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>Tag Manager Server compatibility checker for server-side tagging with GTM-Container JSON-Export</title>
    <base href="<?php print$sBaseUrl; ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="/fav/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/fav/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/fav/favicon-16x16.png">
    <link rel="mask-icon" href="/fav/safari-pinned-tab.svg" color="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <meta property="og:image" content="https://www.tagmanagerserver.com/img/tms-demo-fb.png" />
    <meta property="og:url" content="https://www.tagmanagerserver.com/demo/test.php" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Tag Manager Server compatibility checker for server-side tagging with GTM-Container JSON-Export" />
    <meta property="og:description" content="Server-side tagging with Tag Manager Server. Check your GTM-Container JSON-File for compatible Tag, Variables and Trigger." />
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Tag Manager Server compatibility checker for server-side tagging with GTM-Container JSON-Export">
    <meta name="twitter:description" content="Server-side tagging with Tag Manager Server. Check your GTM-Container JSON-File for compatible Tag, Variables and Trigger.">
    <meta name="twitter:site" content="@WolframBartke">
    <meta name="twitter:image" content="https://www.tagmanagerserver.com/img/tms-demo-share.png">
    <meta name="twitter:creator" content="@WolframBartke">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.grey-blue.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="tms.css?v=1" />
    <link rel="stylesheet" href="lightbox.css" />

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
    


    <div class="mdl-card__title">
      <h1 class="mdl-card__title-text">Tag Manager Server compatibility checker for server-side tagging.</h1>
    </div>
    <div class="mdl-card__supporting-text">
      <p>
      Check your GTM-Container JSON-File for compatible Tags, Variables and Trigger.
      </p>
      <ol>
        <li>Export and save your Container as described here:<br><a href="https://support.google.com/tagmanager/answer/6106997?hl=en" target="_blank">support.google.com/tagmanager/answer/6106997?hl=en</a></li>
        <li>Upload this File</li>
        <li>Compare your Google Tag Manager Container with this Test Result</li>
      </ol>
      </div>
    </div>
</div> <!-- end mdl-grid section-content -->

	<div class="mdl-grid section-content" style="background-color:#ebebeb">
			
			
			  <div class="mdl-cell mdl-cell--12-col">
<?php

$aHtml = array();
$oFileInfo = $FileUpload->getInfo();
#print "<pre>";
#print_r($oFileInfo);
#print "</pre>";
if (!$FileUpload->getStatus()) {
  $sHint = '';
  if(!empty($oFileInfo->filename) && $oFileInfo->mime != 'application/json'){
    $sHint = '<span style="color:#b00020;padding-left:20px;">Please upload a valid Google Tag Manager container.</span>';
    $sHint .= "
    <script>
    window.dataLayer.push({
      'event' : 'uaData',
      'eventCategory':'container-test',
      'eventAction':'upload',
      'eventLabel':'failed',
      'eventValue':1
    });
    </script>
    ";
  }
    

  print '<h2>Upload your GTM-Container to check the result</h2>';
  print '
<style>
  input[type="file"] {
    display: none;
}
.custom-file-upload {
    border: 1px solid #ccc;
    display: inline-block;
    padding: 6px 12px;
    cursor: pointer;
}
</style>
  <div style="padding:0 0 0 20px;"><form method="post" action="" enctype="multipart/form-data">
  <label for="file" id="file_label" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--cta">
  <i class="material-icons">insert_drive_file</i> <span id="file_selection">select JSON file</span>
</label>
  <input id="file" name="file" type="file" onchange="document.getElementById(\'file_selection\').innerHTML = this.files[0].name;document.getElementById(\'file_submit\').classList.add(\'mdl-button--cta\');document.getElementById(\'file_label\').classList.remove(\'mdl-button--cta\');" />
  <input type="submit" id="file_submit" value="Upload" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"/> '.$sHint.'
</form></div>
<p style="padding:10px 0 0 20px;"><i>The uploaded files will be used strictly for this check, and will be deleted immediately.</i></p>
';
}else{
  print '<h2>Test Results</h2>';
  print '<p style="padding-left:20px;">Only the supported tags, triggers and variables are listed here. All others are ignored.</p>';

  $aHtml = array();

  if(isset($oFileInfo->filename) && $oFileInfo->mime == 'application/json'){
    require_once('tms/TMS.php');
    #$aConfig['sGTM_ContainerFile'] = '../'.$sUploadFolder.'/'.$oFileInfo->filename; 	
    $aConfig['sGTM_ContainerFile'] = $oFileInfo->destination; 	
    $oTMS = new TMS($aConfig);
    #$oTMS->TMS_SetCookieHeader();
    $oTMS->TMS_Container_Import();

    $aHtml[] = '<div>';

    foreach($oTMS->aTrackingContainer as $sDataType => $aTypeData){
      
      if($sDataType == 'trigger' || $sDataType == 'tag' || $sDataType == 'variable'){
        $aHtml[] = '<div class="demo-card-wide mdl-card mdl-shadow--2dp" style="width:95%;margin:0 20px 20px 20px;">';
        $aHtml[] = '<div class="mdl-card__title" style="border-top:2px solid #ccc;border-bottom:1px solid #ccc;"><h3 class="mdl-card__title-text">'.$sDataType.'</h3></div>';
        $aHtml[] = '<div style="padding-bottom:20px;">';

        $aGTM_VariableNames = array();
        $aGTM_VariableNames['v'] = array('Constant', 'grey', '<span class="material-icons" style="line-height:32px;">description</span>');
        $aGTM_VariableNames['c'] = array('Data Layer Variable', 'grey', '<span class="material-icons" style="line-height:32px;">settings</span>');
        $aGTM_VariableNames['k'] = array('First-Party-Cookie', 'grey', '<span class="material-icons" style="line-height:32px;">settings</span>');
        $aGTM_VariableNames['smm'] = array('Lookup Table', 'grey', '<span class="material-icons" style="line-height:32px;">settings</span>');
        $aGTM_VariableNames['gas'] = array('Google Analytics Settings', 'blue', '<span class="material-icons" style="line-height:32px;">settings</span>');
        $aGTM_VariableNames['DEBUG_MODE'] = array('DEBUG_MODE', 'grey', '<span class="material-icons" style="line-height:32px;">settings</span>');
        $aGTM_VariableNames['PAGE_URL'] = array('PAGE_URL', 'grey', '<span class="material-icons" style="line-height:32px;">language</span>');
        $aGTM_VariableNames['PAGE_HOSTNAME'] = array('PAGE_HOSTNAME', 'grey', '<span class="material-icons" style="line-height:32px;">language</span>');
        $aGTM_VariableNames['PAGE_PATH'] = array('PAGE_PATH', 'grey', '<span class="material-icons" style="line-height:32px;">language</span>');
        $aGTM_VariableNames['REFERRER'] = array('REFERRER', 'grey', '<span class="material-icons" style="line-height:32px;">language</span>');
        $aGTM_VariableNames['EVENT'] = array('EVENT', 'grey', '<span class="material-icons" style="line-height:32px;">settings</span>');


        foreach($aTypeData as $sType => $aTypeData){
          foreach($aTypeData as $sTypeName => $aData){
            $sName = '';
            $sIcon = '';
            $sColor = '';
            $bBreak = false;

            if($sDataType == 'trigger' && $sType == 'gtm.js'){
              $sName = 'All Pages';
              $sIcon = '<span class="material-icons" style="line-height:32px;">remove_red_eye</span>';
              $sColor = 'blue';

            }else if($sDataType == 'trigger' && $sType == 'event'){
              $sName = $sTypeName;
              $sIcon = '<span class="material-icons" style="line-height:32px;">code</span>';
              $sColor = 'deep-orange';

            }else if($sDataType == 'tag' && $sType == 'ua'){
              $sName = $sTypeName;
              $sIcon = '<img src="demo/img/tag-ua.png" alt="" />';
              $sColor = 'white';
            
            }else if($sDataType == 'variable' && isset($aGTM_VariableNames[$aTypeData['type']])){
              $bBreak = true;
              $sName = $aTypeData['name'].' ('.$aGTM_VariableNames[$aTypeData['type']][0].')';
              $sIcon = $aGTM_VariableNames[$aTypeData['type']][2];
              $sColor = $aGTM_VariableNames[$aTypeData['type']][1];
            }

            if(!empty($sName)){
              $aHtml[] = '
              <div style="padding:10px 0 0 20px;display:inline-block;">
                <span class="mdl-chip mdl-chip--contact">
                  <span class="mdl-chip__contact mdl-color--'.$sColor.' mdl-color-text--white">'.$sIcon.'</span>
                  <span class="mdl-chip__text">'.$sName.'</span>
                </span>
              </div>';
            }

            if($bBreak)
              break;

          }
              
        }
        $aHtml[] = '</div>';
        $aHtml[] = '<div id="setting_'.$sDataType.'"></div>';
        $aHtml[] = '</div>';
      }






    }

    $aHtml[] = '</div>';
    print implode("\n", $aHtml);
  }
  

  if(unlink ($aConfig['sGTM_ContainerFile'])){
    print "
    <script>
    window.dataLayer.push({
      'event' : 'uaData',
      'eventCategory':'container-test',
      'eventAction':'upload',
      'eventLabel':'success',
      'eventValue':1
    });
    </script>
    ";
    print '
    <p style="padding-left:20px;">
      The uploaded file <i>'.$oFileInfo->original.'</i> has already been deleted from the server.
      <br>
      <a href="demo/test.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--cta">Reset Test</a>
    </p>';
  }
}
?>

          
          
			  </div>
  </div> <!-- end mdl-grid section-content -->

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

        <div class="mdl-cell mdl-cell--6-col contentData" style="padding-top:26px;">
            <img src="img/tag-manager-server.png" alt="Google Analytics Tracking with the Tag Manager Server" width="100%" class="">
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

    <script src="https://code.getmdl.io/1.0.6/material.min.js"></script>
</body>

</html>
