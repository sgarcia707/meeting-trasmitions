<?php

//echo("hello");
include_once 'vendor/autoload.php';
include_once "config/security.php";

$client = init();


//var_dump($client);
if(!$client->getAccessToken()){
    var_dump($client->getAccessToken());
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
        <md-content class="md-padding">
          <md-toolbar class="md-info">
              <div class="md-toolbar-tools">
                <h2 class="md-flex">Datos de la <md-input-container><input ng-model="view.title" class="event" value="{{view.title}}"></md-input-container></h2>
                <md-select placeholder="{{view.listBroadcasting}}" ng-model="broadcast" class="listEvent">
                  <md-option ng-value="broadcast" ng-repeat="broadcast in broadcasts" ng-click="getBroadcast(broadcast.id_stream)" >{{broadcast.title}} {{broadcast.status}}</md-option>
                </md-select>
              </div>
          </md-toolbar>
          <md-content class='createEvent'>
              <label>Fecha:</label><md-datepicker ng-model="view.date" md-placeholder="Ingrese la fecha actual" class="datePickerMargin"></md-datepicker>
              <md-input-container class="md-block" flex-gt-sm>
                  <label>Hora Inicio (Formato 00:00)</label>
                  <input ng-model="view.init_time" class="inputDateTime">
              </md-input-container>
              <md-input-container class="md-block" flex-gt-sm>
                  <label>Hora Fin (Formato 00:00)</label>
                  <input ng-model="view.finish_time" class="inputDateTime">
              </md-input-container>
              <md-button class="md-raised" ng-model="btnCreate" ng-click="created()" ng-disabled="view.btnCreateDisabled">Crear</md-button>    
          </md-content>
          <md-toolbar class="md-info" ng-hide="view.hideDataTransmision">
            <div class="md-toolbar-tools">
              <h2 class="md-flex">Datos para la Transmision</h2>
            </div>
          </md-toolbar>
          <md-content flex layout-padding ng-hide="view.hideDataTransmision">
            <div>
                <label><strong>Link Youtube:</strong></label>
                <span><a href="{{transmision.urlYoutube}}"> {{transmision.urlYoutube}}</a></span>
                 <md-button class="md-raised" ng-model="btnQR"  ng-mouseover="show= (show)?false:true" ng-init="show=false">QR</md-button>
                <span ng-hide="show">
                    <qrcode data="{{transmision.urlYoutube}}" href="{{transmision.urlYoutube}}" class="qrmargin"  size="200" ng-mouseleave="console.log('prueba')"></qrcode>
                </span>
            </div>
            <div>
                <label><strong>Titulo:</strong></label>
                <span>{{transmision.title}}</span>
            </div>
            <div>
                <label><strong>Link Trasmision:</strong></label>
                <span><a href="{{transmision.urlPanel}}"> {{transmision.urlPanel}}</a></span>
            </div>
            <div>
                <label><strong>Nombre Trasmision:</strong></label>
                <span>{{transmision.name}}</span>
            </div>
            <div>
                <label><strong>Broadcast Id:</strong></label>
                <span>{{transmision.broadcastId}}</span>
            </div>
             <div>
                <label><strong>Status:</strong></label>
                <span style="color:{{view.status.color}}">{{transmision.status}}</span>
            </div>
            <div ng-hide="view.hideTest">
                <md-button class="md-raised" class="buttonGreen" ng-model="view.hideTest" ng-click="test()">Test</md-button>  
            </div>
            <div ng-hide="view.hideEmision">
                <md-button class="md-raised" class="buttonBlue" ng-model="view.hideEmision" ng-click="emision()">Emitir</md-button>  
            </div>
            <div ng-hide="view.hideComplete">
                <md-button class="md-raised" class="buttonRed" ng-model="view.hideComplete" ng-click="detenerEmision()">Detener Emision</md-button>  
            </div>
          </md-content>
          <br>
          <div loading-msg></div>
        </md-content>
      </md-tab>
      <md-tab label="Configuraciones">
        <md-content class="md-padding">
        <md-input-container class="md-block">
          <label>Descripcion</label>
          <input ng-model="configurations.description"> 
        </md-input-container>

        <md-input-container class="md-block">
            <label>FFMPEG Configuraciones</label>
            <input ng-model="configurations.ffmpeg"> 
            <span class="spaceffmpegConfig">ffmpeg {{configurations.ffmpeg}} -f flv rtmp://a.rtmp.youtube.com/live2/YOUTUBE_KEY</span>
        </md-input-container>

        <md-content layout="column" class="md-padding" class="newLine">
          <p class="marginNegative10">
            Configuraciones de <code>FFMPEG</code> guardadas anteriormente
          </p>
          <table border="0" class="marginNegative10">
            <tr>
              <th>Descripcion</th> 
              <th>Configuracion</th>
              <th>Status</th>
            </tr>
            <tr ng-repeat="config in configuration.config_ffmpeg">
              <td>{{ config.description }}</td>
              <td>{{ config.config }}</td>
              <td>
                <md-checkbox ng-model="config.active" aria-label="Checkbox 1" ng-click="updateStatusConfiguration(config._id, config.active)"></md-checkbox>
              </td>
            </tr>
          </table>
        </md-content>
          <div class="newLine marginNegative10">
              <md-button class="buttonGreen md-raised" ng-model="configurations.save" ng-click="saveConfig()">Guardar</md-button>
          </div>
      </md-content>
      </md-tab>
    </md-tabs>
  </md-content>
</div>
</div>
</body>
</html>