var http
var id

angular.module('BlankApp',['ngMaterial', 'ngMessages','monospaced.qrcode', 'loadingMsgDirective']).controller('AppCtrl', function($scope, $http, $mdDialog, $interval) {

    $scope.view = new Object();
    $scope.view.date = new Date();
    //$scope.view.init_time = "";
    //$scope.view.finish_time = "";
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
    $http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/listBroadcast.php'
        }).then(function successCallback(response) {
            console.log("cargo datos de broadcasting existentes")
            console.log(response.data)
            $scope.broadcasts = response.data;
            $scope.view.listBroadcasting = "Seleccione una Reunion"

          }, function errorCallback(response) {
            console.log("fallo al obtener broadcasting: ")
            console.log(response.data)
    });   

    $scope.created = function(){
        console.log("click")
        $scope.view.btnCreateDisabled = true;
        var month = $scope.view.date.getUTCMonth() + 1; 
        var day = $scope.view.date.getUTCDate();
        var year = $scope.view.date.getUTCFullYear();
        var title = $scope.view.title.replace(/ /g,"_");
        name = title + "_"  +  ((day < 10)? "0" + day : day) + "-" + ((month < 10)? "0" + month : month) +  "-" + year
        init_time = year + "-" + ((month < 10)? "0" + month : month) + "-" + ((day < 10)? "0" + day : day) + "T22:00:00.000Z"
        finish_time = year + "-" + ((month < 10)? "0" + month : month) + "-" + ((day < 10)? "0" + day : day) + "T23:00:00.000Z"

        $http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/api.php?title='+ name +'&init_timestamp=' + init_time + '&finish_timestamp=' + finish_time
        }).then(function successCallback(response) {
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

            var name = response.data.streaming_name
            if(!['good', 'ok', 'bad'].includes($scope.transmision.status)){
                 $http({
                  method: 'GET',
                  url: 'http://localhost:5000/streaming/' + name
                })
            } 

          }, function errorCallback(response) {
            console.log("error :(");
            console.log(response.data)
            $scope.view.btnCreateDisabled = false;
            showDialog(":: Error ::","Error al intentar crear la transmision")
        });

        http = $http
        $scope.view.status.color = "gray";
        $scope.transmision.status = "Verificando..."
        
        $http({
          method: 'GET',
          url: 'http://localhost:5000/streaming/' + $scope.transmision.idStream
        })

        $interval(getStatus, 10000);
    }

    $scope.test = function(){
        $http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/changeStatus.php?id=' + $scope.transmision.idStream + '&status=testing'
        }).then(function successCallback(response) {
            console.log("paso a test")
            console.log(response.data)
            $scope.view.hideTest = true;
            $scope.view.hideEmision = false;
            $scope.view.hideComplete = true;
            
          }, function errorCallback(response) {
            console.log("fallo el Test:")
            console.log(response.data)
            showDialog(":: Error ::","Error al intentar pasar la transmision al estado 'TEST'")
        });
    }

    $scope.emision = function(){
        $http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/changeStatus.php?id=' + $scope.transmision.idStream + '&status=live'
        }).then(function successCallback(response) {
            console.log("paso a emitir")
            console.log(response.data)
            $scope.view.hideTest = true;
            $scope.view.hideEmision = true;
            $scope.view.hideComplete = false;

          }, function errorCallback(response) {
            console.log("fallo la emision: ")
            console.log(response.data)
            showDialog(":: Error ::","Error al intentar pasar la transmision al estado 'LIVE'")
        });
    }
    $scope.detenerEmision = function(){
        $http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/changeStatus.php?id=' + $scope.transmision.idStream + '&status=complete'
        }).then(function successCallback(response) {
            console.log("detuvo emision")
            console.log(response.data)
            
            $scope.view.hideTest = true;
            $scope.view.hideEmision = true;
            $scope.view.hideComplete = true;

            var name = response.data.streaming_name
            if(!['good', 'ok', 'bad'].includes($scope.transmision.status)){
                 $http({
                  method: 'GET',
                  url: 'http://localhost:5001/streaming/stop'
                })
            } 

          }, function errorCallback(response) {
            console.log("fallo al detener la emision: ")
            console.log(response.data)
            showDialog(":: Error ::","Error al intentar pasar la transmision al estado 'COMPLETE")
        });

        $http({
          method: 'GET',
          url: 'http://localhost:5001/streaming/stop'
        })
    }
    $scope.getBroadcast = function($id){
        console.log('http://localhost/www/broadcasting/getBroadcast.php?id=' + $id)
        $http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/getBroadcast.php?id=' + $id
        }).then(function successCallback(response) {
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
            
            var name = response.data.streaming_name
            if(!['good', 'ok', 'bad'].includes($scope.transmision.status)){
                 $http({
                  method: 'GET',
                  url: 'http://localhost:5000/streaming/' + name
                })
            } 

          }, function errorCallback(response) {
            console.log("fallo al obtener broadcasting: ")
            console.log(response.data)
        });
        http = $http
        $scope.view.status.color = "gray";
        $scope.transmision.status = "Verificando..."

        $interval(getStatus, 10000);
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
        http({
              method: 'GET',
              url: 'http://localhost/www/broadcasting/getStatus.php?id=' + id
            }).then(function successCallback(response) {
                console.log("cargando datos de estado de la transmision")
                console.log(response.data)
                $scope.transmision.status = response.data.status
                statusColor(response.data.status)
              }, function errorCallback(response) {
                console.log("fallo al cargar datos de estado de la transmision: ")
                console.log(response.data)
                $scope.transmision.status = "Error";
                $scope.view.status.color = "red";
            });
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


