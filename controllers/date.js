webApp.controller('DateController', ['$rootScope', '$scope', '$routeParams', '$http', function($rootScope, $scope, $routeParams, $http) {
	
	$scope.hash = $routeParams['hash'];
	
	$scope.data = {
		date: {},
		
		models: [],
		
		loadModels: function(dateTs) {
			$http({
				method: 'get',
				url: 'api/get_models.php',
				params: {
					date: dateTs
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.data.models = res.data.models;
					return;
				}
				
				alert('error loading models');
				console.error(res.data);
			});
		},
		
		load: function() {
			
			// get date info
			$http({
				method: 'get',
				url: 'api/get_date.php',
				params: {
					hash: $scope.hash
				}
			}).then(function(res) {
				if  (res.data && res.data.result == 'ok') {
					$scope.data.date = res.data.date;
					
					// load models
					$scope.data.loadModels(res.data.date.date_ts);
					
					return;
				}
				
				alert('error getting data');
			});
		}
	};
	
	$scope.data.load();
	
}]);