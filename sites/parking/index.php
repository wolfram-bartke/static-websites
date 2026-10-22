<?php
$sDomain = $_SERVER['HTTP_HOST'];
if($sDomain == 'norawolfi.de'){
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: https://www.hokata.de/");
  exit();
}
?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="de" />

	<meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,follow" />
    
    <!-- Bootstrap core CSS -->
    <link href="interface/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="interface/bootstrap/docs.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="interface/bootstrap/navbar-fixed-top.css" rel="stylesheet">
    
    <link rel="stylesheet" href="interface/font-awesome/css/font-awesome.min.css">
    <link href="css/stuttgart.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="interface/bootstrap/ie10-viewport-bug-workaround.js"></script>
    
	<script type="text/javascript" src="/jquery.js"></script>
  <title><?php print $sDomain;?></title>

  <!--<script src="tms/tms.js"></script>-->
  <script>window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event':'gtm.js'})</script>

</head>

<body style="padding:0;">


<div class="row" style="padding-top:20px;padding-bottom:20px;margin:0;">
  <div class="col-md-4 col-md-offset-4">
    <div class="panel panel-c1">
      <div class="panel-heading">
        <h1 class="panel-title"><i class="fa fa-laptop"></i> <?php print $sDomain;?></h1><a name="demo"></a>
      </div>
      <div class="panel-body">
<p>
Wolfram Bartke
<br>
<b>HOKATA Online Marketing</b>
<br>
Online mehr erreichen.
<br>
<br>
Wildunger Str. 47
<br>
70372 Stuttgart
<br>
<br>
Tel.: +49 (0)711 870 38 25
<br>
E-Mail:
<a href="mailto:kontakt@hokata.de">kontakt@hokata.de</a>
<br>
<a href="http://www.hokata.de">www.hokata.de</a> | <a href="http://www.hokata.de/kontakt/">Datenschutz</a>
<br>
<br>
USt-IdNr.: DE262863461
</p>

      </div>
    </div>
  </div>
  
  
</div>

</body>
</html>
