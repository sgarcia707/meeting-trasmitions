var broadcastingServices = angular.module("BroadcastingService", [])

.service('Broadcaster', function($http, $q) {

  var baseURL = window.location.href 
  var n = baseURL.indexOf("broadcasting");
  var url = baseURL.substring(0, n) + 'broadcasting';

   this.listBroadcast = function () {
        var defered = $q.defer();
        var promise = defered.promise;

        $http({
          method: 'GET',
          url: url + '/list/broadcasting'
        }).then(function successCallback(response) {
            console.log("cargo datos de broadcasting existentes")
            console.log(response);
			       defered.resolve(response);

          }, function errorCallback(response) {
            console.log("fallo al obtener broadcasting: ")
            defered.reject(response);
    }); 

        return promise;
    }

    

    this.created = function(title, init_time, finish_time){
    	console.log("name: " + title + " init: " + init_time + " finish: " + finish_time)
  	  var defered = $q.defer();
      var promise = defered.promise;
      var name = ""
      var responseRequest = ""

 		  var createdBroadcast = function(title, init_time, finish_time){
			$http({
	          method: 'POST',
	          url: url + '/streaming/created',
            data: { "title": title, "init_time": init_time, "finish_time": finish_time }
	        }).then(function successCallback(response) {
            console.log("broadcasting creado exitosamente")
            console.log(response)
	        	responseRequest = response
	        	name = response.data.streaming_name
	            defered.resolve(response);
	          }, function errorCallback(response) {
              console.log("broadcasting no pudo ser creado")
              console.log(response)
	            defered.reject(response);
	        });
 		  }

      var callFFMPEG = function(){
         if(!['good', 'ok', 'bad'].includes(responseRequest.status)){
              $http({
               method: 'GET',
               url: url + 'http://localhost:5000/streaming/' + name
             })
         } 
         return responseRequest
      }
		  var promiseTotal= defered.promise.then(callFFMPEG);
 
      createdBroadcast(title, init_time, finish_time);

      return promiseTotal
    }

    this.changeStatus = function(idStream, status){
    	var defered = $q.defer();
        var promise = defered.promise;

    	$http({
          method: 'GET',
          url: url + '/streaming/change/status',
          data: { "idStream": idStream, "status": status }
        }).then(function successCallback(response) {
            defered.resolve(response);
            
          }, function errorCallback(response) {
            defered.reject(response);
        });

        return promise;
    }

    this.getBroadcast = function(id){
    	var defered = $q.defer();
        var promise = defered.promise;
        var responseRequest = ""
        var name = ""

        var getBroadcastForId = function(id){
        	$http({
	          	method: 'GET',
	          	url: url + '/broadcast/get/' + id
	        }).then(function successCallback(response) {
	        	responseRequest = response
	        	name = response.data.streaming_name
	            defered.resolve(response);
	            
	          }, function errorCallback(response) {
	            defered.reject(response);
	        });
        }

        var callFFMPEG = function(){
            if(!['good', 'ok', 'bad'].includes(responseRequest.status)){
                 $http({
                  method: 'GET',
                  url: 'http://localhost:5000/streaming/' + name
                })
            } 
            return responseRequest
        }
        
        var promiseTotal= defered.promise.then(callFFMPEG);
 
      	getBroadcastForId(id);

        return promiseTotal

    }

    this.getStatus = function(id){
    	var defered = $q.defer();
        var promise = defered.promise;
    	http({
          method: 'GET',
          url: url + '/broadcast/status/' + id
        }).then(function successCallback(response) {
            defered.resolve(response);
          }, function errorCallback(response) {
            defered.reject(response);
        });

        return promise;

    }

    this.stopBroadcaster = function(){
    	$http({
          method: 'GET',
          url: 'http://localhost:5001/streaming/stop'
        })
    }

    this.getConfigurationFfmpeg = function(){
        var defered = $q.defer();
        var promise = defered.promise;

        $http({
          method: 'GET',
          url: url + '/configuration/ffmpeg'
        }).then(function successCallback(response) {
            console.log("cargo datos de configuraciones ffmpeg" + response.data)
            defered.resolve(response);

        }, function errorCallback(response) {
           console.log("fallo al obtener configuraciones ffmpeg: ")
           console.log(response)
           defered.reject(response);
        }); 

        return promise;
    }

    this.addConfigurationFfmpeg = function(description, configuration){
        var defered = $q.defer();
        var promise = defered.promise;

        var data = { "description": description, "configuration": configuration }

        var conAjax = $http.post( url + "/configuration/ffmpeg/add", data);

        conAjax.success(function(response){
              console.log("Agregado de configuracion:")
              console.log(response)
              defered.resolve(response);
        });

        return promise;
    }

    this.updateConfigurationFfmpeg = function(condition, json){
        var defered = $q.defer();
        var promise = defered.promise;

        var data = { "condition": condition, "json": json }

        var conAjax = $http.put( url + "/configuration/ffmpeg/update", data);

        conAjax.success(function(response){
              console.log("Actualizacion de configuracion:")
              console.log(response)
              defered.resolve(response);
        });

        return promise;
    }
}); 
