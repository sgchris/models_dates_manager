webApp.controller('DateController', ['$rootScope', '$scope', '$state', '$stateParams', '$http', '$location', '$q', 
function($rootScope, $scope, $state, $stateParams, $http, $location, $q) {
	
	// identify the date by its haash
	$scope.hash = $stateParams['hash'];
	
	// hash MUST be provided
	if (!$scope.hash) {
		$state.go('home');
		return;
	}
	
	// the selected category
	// for unauthorized user that should be defined!
	$scope.category = $stateParams['category'] || false;
	
	$scope.data = {
		date: {},
		
		// models relevant for the date
		availableModels: [],
		excludedModels: [],
		chosenModels: [],
		
		// all the rest
		models: [],
		
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
			if (!confirm('Put "' + model.name + '" on stand-by?')) {
				return;
			}
			
			var apiData = {
				date: date.date_ts,
				model_id: model.id
			};
			
			$http({
				method: 'post',
				url: 'api/exclude_model_from_date.php',
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
			if (!confirm('Remove "' + model.name + '" from stand-by?')) {
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
		
		changeAvailability: function(date, model, makeModelAvailable) {
			var apiFileName = 'make_model_available_for_date.php';
			if (!makeModelAvailable) {
				apiFileName = 'make_model_unavailable_for_date.php';
			}
			
			// call the API
			$http({
				method: 'post',
				url: 'api/' + apiFileName,
				data: {
					date: date.date_ts,
					model_id: model.id
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.data.load();
					return;
				}
				
				alert('Error changing model availability');
				console.error('Error changing model availability', res);
			}, function(res) {
				console.error('Error changing model availability', res);
			});
		},
		
		makeAvailable: function(date, model) {
			$scope.data.changeAvailability(date, model, true);
		},
		
		makeAllAvailable: function(date, category) {
			// call the API
			$http({
				method: 'post',
				url: 'api/make_all_models_unavailable_for_date.php',
				data: {
					date: date.date_ts,
					category: category
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.data.load();
					return;
				}
				
				alert('Error changing model availability');
				console.error('Error changing model availability', res);
			}, function(res) {
				console.error('Error changing model availability', res);
			});
		},
		
		makeUnavailable: function(date, model) {
			$scope.data.changeAvailability(date, model, false);
		},
		
		loadModels: function(dateTs) {
			
			// get all models in restricted mode / relevant models for free mode
			var apiParams = {};
			if (!$rootScope.hasRestrictedAccess) {
				apiParams.date_hash = $scope.hash;
			}
			
			var promise = $http({
				method: 'get',
				url: 'api/get_models.php',
				params: apiParams
			});
			
			promise.then(function(res) {
				if (res.data && res.data.result == 'ok') {
					// check the included models
					$scope.data.models = res.data.models;
					
				} else {
					alert('error loading models');
					console.error(res.data);
				}
			}, function(res) {
				alert('error loading models');
				console.error(res);
			});
			
			return promise;
		},
		
		loadDate: function() {
			// get date info
			var promise = $http({
				method: 'get',
				url: 'api/get_date.php',
				params: {
					hash: $scope.hash
				}
			});
			
			promise.then(function(res) {
				if  (res.data && res.data.result == 'ok') {
					$scope.data.date = res.data.date;
				} else {
					alert('error getting data');
				}
			});
			
			return promise;
		},
		
		load: function() {
			$q.all([
				$scope.data.loadDate(), 
				$scope.data.loadModels()
			]).then(function(dateRes, modelsRes) {
				
				// convert models to object where keys are the IDs
				var modelsObj = {};
				$scope.data.models.forEach(function(modelData) {
					modelsObj[modelData.id] = modelData;
				});
				
				// update the models lists (excluded, chosen, available);
				$scope.data.availableModels = [];
				$scope.data.date.available_models.forEach(function(availableModelId) {
					if (typeof(modelsObj[availableModelId]) != 'undefined') {
						$scope.data.availableModels.push(modelsObj[availableModelId]);
						
						// remove her from the "rest" models list
						$scope.data.models.forEach(function(modelData, i) {
							if (modelData.id == availableModelId) {
								$scope.data.models.splice(i, 1);
							}
						});
					}
				});
				
				// update the models lists (excluded, chosen, available);
				$scope.data.chosenModels = [];
				$scope.data.date.chosen_models.forEach(function(chosenModelId) {
					if (typeof(modelsObj[chosenModelId]) != 'undefined') {
						$scope.data.chosenModels.push(modelsObj[chosenModelId]);
						
						// remove her from the "rest" models list
						$scope.data.models.forEach(function(modelData, i) {
							if (modelData.id == chosenModelId) {
								$scope.data.models.splice(i, 1);
							}
						});
					}
				});
				
				// update the models lists (excluded, chosen, available);
				$scope.data.excludedModels = [];
				$scope.data.date.excluded_models.forEach(function(excludedModelId) {
					if (typeof(modelsObj[excludedModelId]) != 'undefined') {
						$scope.data.excludedModels.push(modelsObj[excludedModelId]);
						
						// remove her from the "rest" models list
						$scope.data.models.forEach(function(modelData, i) {
							if (modelData.id == excludedModelId) {
								$scope.data.models.splice(i, 1);
							}
						});
					}
				});
			});
		}
	};
	
	$scope.tabs = {
		// current tab to filter the models
		current: '',
		
		// callback from the tabs directive
		tabClicked: function(newTab) {
			$state.go('date_category', {
				hash: $scope.hash, 
				category: newTab.name
			}, {
				location: 'replace'
			});
		},
		
		dataLoaded: function(modelsCategories) {
			// when no category provided, redirect to the first one.
			if (!$scope.category && modelsCategories && modelsCategories.length > 0) {
				$state.go('date_category', {
					hash: $scope.hash, 
					category: modelsCategories[0].name
				}, {
					location: 'replace'
				});
				return;
			}
			
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