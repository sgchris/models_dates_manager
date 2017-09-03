webApp.controller('DateController', ['$rootScope', '$scope', '$state', '$stateParams', '$http', '$location', 
function($rootScope, $scope, $state, $stateParams, $http, $location) {
	console.log('dates controller', $stateParams);
	
	// identify the date by its haash
	$scope.hash = $stateParams['hash'];
	
	// the selected category
	// for unauthorized user that should be defined!
	$scope.category = $stateParams['category'] || false;
	
	$scope.data = {
		date: {},
		
		models: [],
		excludedModels: [],
		chosenModels: [],
		
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
					
					// check the chosen models
					if (res.data.chosen_models) {
						$scope.data.chosenModels = res.data.chosen_models;
					}
					
				} else {
					alert('error loading models');
					console.error(res.data);
				}
			}, function(res) {
				alert('error loading models');
				console.error(res);
			});
		},
		
		chooseModel: function(date, model) {
			if (!confirm('Choose ' + model.name + ' for this date?')) {
				return;
			}
			
			var apiData = {
				date: date.date_ts,
				model_id: model.id
			};
			
			$http({
				method: 'post',
				url: 'api/choose_model_for_date.php',
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
		unChooseModel: function(date, model) {
			if (!confirm('Un-choose ' + model.name + ' for the date?')) {
				return;
			}
			
			var apiData = {
				date: date.date_ts,
				model_id: model.id
			};
			
			$http({
				method: 'post',
				url: 'api/unchoose_model_for_date.php',
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
				} else {
					alert('error getting data');
				}
				
			});
		}
	};
	
	$scope.tabs = {
		// current tab to filter the models
		current: '',
		
		// callback from the tabs directive
		tabClicked: function(newTab) {
			console.log('newTab', newTab);
			console.log('#/date/' + $scope.hash + '/' + encodeURIComponent(newTab.name));
			$state.go('date_category', {hash: $scope.hash, category: newTab.name});
			//$location.path('#/date/' + $scope.hash + '/' + encodeURIComponent(newTab.name));
		},
		
		dataLoaded: function(modelsCategories) {
			console.log('models categories loaded', modelsCategories);
			
			// if no tabs data, just exit, nothing to do with it
			if (!modelsCategories || modelsCategories.length === 0) {
				alert('No models categories!');
				return;
			}
			
			// take the category from the URL
			if ($scope.category) {
				// select the required tab
				modelsCategories.forEach(function(categoryData) {
					if (categoryData.name == $scope.category) {
						$scope.tabs.current = categoryData.id;
					}
				});
			}
			
			// if still tab selected, take the first one
			if (!$scope.tabs.current) {
				$scope.tabs.current = modelsCategories[0].id;
			}
		}
	}
	
	$rootScope.$watch('hasRestrictedAccess', function(hasRestrictedAccess) {
		$scope.data.load();
	});
	
}]);