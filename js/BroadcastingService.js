var broadcastingServices = angular.module("BroadcastingService", [])

.service('Broadcaster', function($http, $q) {
   this.listBroadcast = function () {
        var defered = $q.defer();
        var promise = defered.promise;

        $http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/listBroadcast.php'
        }).then(function successCallback(response) {
            console.log("cargo datos de broadcasting existentes")
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
	          method: 'GET',
	          url: 'http://localhost/www/broadcasting/api.php?title='+ title +'&init_timestamp=' + init_time + '&finish_timestamp=' + finish_time
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
 
      	createdBroadcast(title, init_time, finish_time);

        return promiseTotal
    }

    this.changeStatus = function(idStream, status){
    	var defered = $q.defer();
        var promise = defered.promise;

    	$http({
          method: 'GET',
          url: 'http://localhost/www/broadcasting/changeStatus.php?id=' + idStream + '&status=' + status
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
	          	url: 'http://localhost/www/broadcasting/getBroadcast.php?id=' + id
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
          url: 'http://localhost/www/broadcasting/getStatus.php?id=' + id
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
}); 
