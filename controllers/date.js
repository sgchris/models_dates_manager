webApp.controller('DateController', ['$rootScope', '$scope', '$routeParams', '$http', '$location', function($rootScope, $scope, $routeParams, $http, $location) {
	
	$scope.hash = $routeParams['hash'];
	
	$scope.data = {
		date: {},
		
		models: [],
		excludedModels: [],
		
		loadModels: function(dateTs) {
			$http({
				method: 'get',
				url: 'api/get_models.php',
				params: {
					date: dateTs
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					// check the included models
					$scope.data.models = res.data.models;
					
					// check the excluded models
					if (res.data.excluded_models) {
						$scope.data.excludedModels = res.data.excluded_models;
					}
					
					return;
				}
				
				alert('error loading models');
				console.error(res.data);
			}, function(res) {
				alert('error loading models');
				console.error(res);
			});
		},
		
		excludeModel: function(date, model) {
			if (!confirm('Exclude ' + model.name + '?')) {
				return;
			}
			
			var apiData = {
				date: date.date_ts,
				model_id: model.id
			};
			
			$http({
				method: 'post',
				url: 'api/exlude_model_from_date.php',
				data: apiData
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.data.load();
					return;
				}
				
				alert('Error excluding model');
				console.error('Error excluding model', res);
			}, function(res) {
				console.error('Error excluding model', res);
			});
		},
		
		// remove the model from the "Excluded" list (i.e. include the model in that date)
		includeModel: function(date, model) {
			if (!confirm('Re-include ' + model.name + '?')) {
				return;
			}
			
			var apiData = {
				date: date.date_ts,
				model_id: model.id
			};
			
			$http({
				method: 'post',
				url: 'api/include_model_in_date.php',
				data: apiData
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.data.load();
					return;
				}
				
				alert('Error including model');
				console.error('Error including model', res);
			}, function(res) {
				console.error('Error including model', res);
			});
		},
		
		deleteDate: function() {
			if (!confirm('Delete the date?')) {
				return;
			}
			
			$http({
				method: 'post',
				url: 'api/delete_date.php',
				data: {
					date: $scope.data.date.date_ts
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					// redirect to the homepage
					$location.path('#/');
					return;
				}
				
				alert('Error deleting the date');
				console.error('Error deleting the date', res);
			}, function(res) {
				alert('Error deleting the date');
				console.error('Error deleting the date', res);
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
	
	$scope.tabs = {
		current: '',
		tabChanged: function(newTab) {
			$scope.tabs.current = newTab;
		}
	}
	
	
	$scope.data.load();
	
}]);