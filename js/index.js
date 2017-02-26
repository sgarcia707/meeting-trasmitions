var http
var id

var myapp = angular.module('BlankApp',['ngMaterial', 'ngMessages','monospaced.qrcode', 'loadingMsgDirective', 'BroadcastingService']);

myapp.controller('AppCtrl', function($scope, $http, $mdDialog, $interval, Broadcaster) {

    $scope.view = new Object();
    $scope.view.date = new Date();
    $scope.view.init_time = "";
    $scope.view.finish_time = "";
    $scope.view.title = "Reunion";
    $scope.view.hideDataTransmision = true;
    $scope.view.btnCreateDisabled = false;

    $scope.view.hideTest = false;
    $scope.view.hideEmision = true;
    $scope.view.hideComplete = true;

    $scope.view.status = new Object();
    $scope.view.status.color = "gray"

    $scope.transmision = new Object();

    $scope.view.listBroadcasting = "cargando..."

    $scope.configuration = new Object();

    Broadcaster.getConfigurationFfmpeg().then(function(response) {
            console.log("cargando datos de estado de la transmision")
            console.log(response.data)
            $scope.configuration.config_ffmpeg = response.data;
    })
    .catch(function(err) {
        console.log("fallo al cargar datos de estado de la transmision: ")
        console.log(err.data)
    })

    Broadcaster.listBroadcast().then(function(response) {
        $scope.broadcasts = response.data;
        $scope.view.listBroadcasting = "Seleccione una Reunion"
    })
    .catch(function(err) {
        // Tratar el error
        console.log("fallo al obtener broadcasting: ")
        console.log(err)
    })

    $scope.created = function(){
        console.log("click")
        $scope.view.btnCreateDisabled = true;
        var month = $scope.view.date.getUTCMonth() + 1; 
        var day = $scope.view.date.getUTCDate();
        var year = $scope.view.date.getUTCFullYear();
        var title = $scope.view.title.replace(/ /g,"_");
        name = title + "_"  +  ((day < 10)? "0" + day : day) + "-" + ((month < 10)? "0" + month : month) +  "-" + year
        init_time = year + "-" + ((month < 10)? "0" + month : month) + "-" + ((day < 10)? "0" + day : day) + "T" + $scope.view.init_time + ":00.000-03:00"
        finish_time = year + "-" + ((month < 10)? "0" + month : month) + "-" + ((day < 10)? "0" + day : day) + "T" + $scope.view.finish_time + ":00.000-03:00"

        
        Broadcaster.created(name, init_time, finish_time).then(function(response) {
            console.log("todo ok")
            $scope.view.hideDataTransmision = false;
            console.log(response)

            $scope.transmision.idStream = response.data.id_stream
            $scope.transmision.urlYoutube = "https://youtu.be/" + response.data.id_stream
            $scope.transmision.title = response.data.title
            $scope.transmision.urlPanel = response.data.url
            $scope.transmision.name = response.data.streaming_name
            $scope.transmision.broadcastId = response.data.broadcast_id
            id = response.data.broadcast_id
            $scope.view.btnCreateDisabled= false;
        })
        .catch(function(err) {
            console.log("error :(");
            console.log(err.data)
            $scope.view.btnCreateDisabled = false;
            showDialog(":: Error ::","Error al intentar crear la transmision")
        })

        http = $http
        $scope.view.status.color = "gray";
        $scope.transmision.status = "Verificando..."
        $interval(getStatus, 10000);
    }

    $scope.test = function(){
        Broadcaster.changeStatus($scope.transmision.idStream, "testing").then(function(response) {
            console.log("paso a test")
            console.log(response.data)
            $scope.view.hideTest = true;
            $scope.view.hideEmision = false;
            $scope.view.hideComplete = true;
        })
        .catch(function(err) {
            console.log("fallo el Test:")
            console.log(err.data)
            showDialog(":: Error ::","Error al intentar pasar la transmision al estado 'TEST'")
        })
    }

    $scope.emision = function(){
        Broadcaster.changeStatus($scope.transmision.idStream, "live").then(function(response) {
            console.log("paso a emitir")
            console.log(response.data)
            $scope.view.hideTest = true;
            $scope.view.hideEmision = true;
            $scope.view.hideComplete = false;
        })
        .catch(function(err) {
            console.log("fallo la emision: ")
            console.log(err.data)
            showDialog(":: Error ::","Error al intentar pasar la transmision al estado 'LIVE'")
        })
    }
    $scope.detenerEmision = function(){
        Broadcaster.changeStatus($scope.transmision.idStream, "complete").then(function(response) {
            console.log("detuvo emision")
            console.log(response.data)
            
            $scope.view.hideTest = true;
            $scope.view.hideEmision = true;
            $scope.view.hideComplete = true;
        })
        .catch(function(err) {
            console.log("fallo al detener la emision: ")
            console.log(err.data)
            showDialog(":: Error ::","Error al intentar pasar la transmision al estado 'COMPLETE")
        })

        Broadcaster.stopBroadcaster();
    }
    $scope.getBroadcast = function($id){
        console.log('http://localhost/www/broadcasting/getBroadcast.php?id=' + $id)
        
        Broadcaster.getBroadcast($id).then(function(response) {
            console.log("obtuvo datos emision")
            console.log(response.data)
            $scope.view.hideDataTransmision = false;

            $scope.transmision.idStream = response.data.id_stream
            $scope.transmision.urlYoutube = "https://youtu.be/" + response.data.id_stream
            $scope.transmision.title = response.data.title
            $scope.transmision.urlPanel = response.data.url
            $scope.transmision.name = response.data.streaming_name
            $scope.transmision.broadcastId = response.data.broadcast_id
            id = response.data.broadcast_id
            $scope.view.btnCreateDisabled= false;

            if(response.data.status != "inactive"){
                if(response.data.status == "testing"){
                    $scope.view.hideTest = true;
                    $scope.view.hideEmision = false;
                    $scope.view.hideComplete = true;
                }

                if(response.data.status == "live"){
                    $scope.view.hideTest = true;
                    $scope.view.hideEmision = true;
                    $scope.view.hideComplete = false;
                }
            }
        })
        .catch(function(err) {
            console.log("fallo al obtener broadcasting: ")
            console.log(err.data)
        })

        http = $http
        $scope.view.status.color = "gray";
        $scope.transmision.status = "Verificando..."

        $interval(getStatus, 10000);
    }

    $scope.saveConfig = function(){
        var description = $scope.configurations.description;
        var configuration = $scope.configurations.ffmpeg;
        $scope.configurations.description = "";
        $scope.configurations.ffmpeg = "";

        Broadcaster.addConfigurationFfmpeg(description, configuration).then(function(response) {
            console.log("Agregando una nueva configuracion ffmpeg:")
            console.log(response.data)
        })
        .catch(function(err) {
            console.log("error al agregar una configuracion ffmpeg:")
            console.log(err.data)
        })


        Broadcaster.getConfigurationFfmpeg().then(function(response) {
            console.log("cargando datos de las configuraciones ffmpeg")
            console.log(response.data)
            $scope.configuration.config_ffmpeg = response.data;
        })
        .catch(function(err) {
            console.log("fallo al cargar datos de las configuraciones ffmpeg:")
            console.log(err.data)
        })
    }

    $scope.updateStatusConfiguration = function(id, status){
        var json = { "$set": {"active":!status } };
        var condition = { "_id": id }
        
        console.log("json put status configuracion ffmpeg:")
        console.log(json)
        console.log("condicion actualizacion configuracion ffmpeg:")
        console.log(condition)

        Broadcaster.updateConfigurationFfmpeg(condition, json).then(function(response) {
            console.log("Actualizando una configuracion ffmpeg:")
            console.log(response.data)
        })
        .catch(function(err) {
            console.log("error al actualizar una configuracion ffmpeg:")
            console.log(err.data)
        })


        Broadcaster.getConfigurationFfmpeg().then(function(response) {
            console.log("cargando datos de las configuraciones ffmpeg")
            console.log(response.data)
            $scope.configuration.config_ffmpeg = response.data;
        })
        .catch(function(err) {
            console.log("fallo al cargar datos de las configuraciones ffmpeg:")
            console.log(err.data)
        })
    }

    function showDialog(title, text) {
        $mdDialog.show(
          $mdDialog.alert()
            .parent(angular.element(document.querySelector('body')))
            .clickOutsideToClose(true)
            .title(title)
            .textContent(text)
            .ok('Aceptar')
        );
    }

    function getStatus(){
        Broadcaster.getStatus(id).then(function(response) {
            console.log("cargando datos de estado de la transmision")
            console.log(response.data)
            $scope.transmision.status = response.data.status
            statusColor(response.data.status)
        })
        .catch(function(err) {
            console.log("fallo al cargar datos de estado de la transmision: ")
            console.log(err.data)
            $scope.transmision.status = "Error";
            $scope.view.status.color = "red";
        })
    }

    function statusColor(status){
        console.log("status pased: "+ status)
        switch (status) {
          case "good":
            $scope.view.status.color = "darkgreen";
            break;
          case "ok":
            $scope.view.status.color = "green";
            break;
          case "bad":
            $scope.view.status.color = "orange";
            break;
          case "noData":
            $scope.view.status.color = "red";
            break;
        }

        if(status == null){
            $scope.view.status.color = "gray";
            $scope.transmision.status = "Verificando..."
        }
    }
});



var loadingMsgDirective = angular.module('loadingMsgDirective', []);

loadingMsgDirective.config(['$httpProvider', function($httpProvider) {
    $httpProvider.interceptors.push(function($q, $rootScope) {
      return {
       'request': function(config) {
           $rootScope.$broadcast('REQUEST_START');

           return config;
        },
        'response': function(response) {
           $rootScope.$broadcast('REQUEST_END');

           return response;
        },
        'responseError': function(rejection) {
           $rootScope.$broadcast('REQUEST_END');

           return $q.reject(rejection);
        }
      };
    });
}]);

loadingMsgDirective.directive('loadingMsg', [function() {
    return {
      template: '<span ng-show="pending">Cargando...</span>',
      scope: {},
      link: function(scope, element, attrs) {
        scope.pending = 0;

        scope.$on('REQUEST_START', function() {
            scope.pending+=1;
        });

        scope.$on('REQUEST_END', function() {
            scope.pending-=1;
        });
      }
    };
}]);

