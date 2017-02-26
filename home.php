<?php

//echo("hello");
include_once 'vendor/autoload.php';
include_once "config/security.php";

$client = init();

//var_dump($client)
if(!$client->getAccessToken()){
    //var_dump($client->getAccessToken());
    header('Location: '.$client->createAuthUrl());
}
?>
<html lang="en" >
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0-rc2/angular-material.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/angular-material/1.0.0-rc3/angular-material.min.css"  />
    <link href="http://logbon72.github.io/angular-material-datetimepicker/css/material-datetimepicker.css"  />
    <link rel="stylesheet" href="css/index.css">
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular-animate.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular-aria.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular-messages.min.js"></script>
    <script src="bower_components/ngclipboard/dist/ngclipboard.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0-rc2/angular-material.min.js"></script>
    <script src="js/index.js"></script>
    <script src="js/BroadcastingService.js"></script>

    <script src="bower_components/angular-qrcode/angular-qrcode.js"></script>
    <script src="bower_components/qrcode-generator/js/qrcode.js"></script>
    <script src="bower_components/qrcode-generator/js/qrcode_UTF8.js"></script>
</head>
<body ng-app="BlankApp"   data-ng-app="monospaced.qrcode" ng-cloak class="bodyCenter">
<div ng-controller="AppCtrl" ng-cloak>
<div ng-cloak>
  <md-content>
    <md-tabs md-dynamic-height md-border-bottom>
      <md-tab label="Principal">
        <ng-include src="'tabPrincipal.html'"></ng-include>
      </md-tab>
      <md-tab label="Configuraciones">
        <ng-include src="'tabConfiguration.html'"></ng-include>
      </md-tab>
    </md-tabs>
  </md-content>
</div>
</div>
</body>
</html>