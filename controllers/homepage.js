webApp.controller('HomepageController', ['$rootScope', '$scope', '$http', '$uibModal', 
	function($rootScope, $scope, $http, $modal) {

	$scope.models = {
		inProgress: false,
		
		// new model related
		newModelName: '',
		newModelError: false,
		addNewModel: function() {
			if (!$rootScope.loggedInUser) {
				return;
			}
			
			if ($scope.models.newModelName.trim().length == 0) {
				$scope.models.newModelError = 'Please type model name in the input above';
				return;
			}
			$scope.models.newModelError = false;
			
			$scope.models.inProgress = true;
			$http({
				method: 'post',
				url: 'api/add_model.php',
				data: {
					name: $scope.models.newModelName.trim(),
					category: $scope.tabs.current || ''
				}
			}).then(function(res) {
				if (!res.data) {
					$scope.dates.newDateError = 'add new model: cannot receive response';
					return console.error('add new model: cannot receive response', res);
				}
				
				if (res.data.result == 'error') {
					$scope.models.newModelError = res.data.error || 'error occurred'
					return;
				}
				
				if (res.data.result == 'ok') {
					// clear the new date
					$scope.models.newModelError = false;
					$scope.models.newModelName = '';
					
					// reload the models list
					$scope.models.load();
					
					return;
				}
				
				$scope.models.newModelError = 'error occurred';
			}, function() {
				$scope.models.newModelError = 'server error';
			}).finally(function() {
				$scope.models.inProgress = true;
			});
			
		},
		
		// models list
		data: [],
		
		// check if there are uncategorized models
		thereAreUncategorizedModels: function(modelsCategories) {
			if (!modelsCategories || modelsCategories.length === 0) {
				return false;
			}
			
			if ($scope.models.data.length > 0) {
				var maxModelsCategoryId = 0,
					isUncategorized = false,
					thereAreUncategorized = false;
				
				$scope.models.data.forEach(function(modelData) {
					isUncategorized = true;
					
					modelsCategories.forEach(function(categoryData) {
						if (categoryData.id > maxModelsCategoryId) {
							maxModelsCategoryId = categoryData.id;
						}
						
						if (modelData.category == categoryData.id) {
							isUncategorized = false;
						}
					});
					
					if (isUncategorized) {
						thereAreUncategorized = true;
					}
				});
				
				if (thereAreUncategorized) {
					$scope.tabs.addUncategorizedTab();
				}
			}
		},
		
		// check if there are models in the specific category
		thereAreModelsInCategory: function(modelsCategoryId) {
			var thereAreModelsInTheCategory = false;
			
			$scope.models.data.forEach(function(modelData) {
				if (modelData.category == modelsCategoryId) {
					thereAreModelsInTheCategory = true;
				}
			});
			
			return thereAreModelsInTheCategory;
		},
		
		load: function() {
			if (!$rootScope.loggedInUser) {
				return;
			}
			
			$scope.models.inProgress = true;
			
			$http.get('api/get_models.php').then(function(res) {
				$scope.models.data = res.data && res.data.models ? res.data.models : [];
				
				// check if uncategorized tab should be added
				if ($scope.models.thereAreUncategorizedModels($scope.tabs.data)) {
					console.log('models: there are uncategorized');
					$scope.tabs.addUncategorizedTab();
				}
				
			}).finally(function() {
				$scope.models.inProgress = false;
			});
		}
	};
	
	$scope.tabs = {
		data: [],
		
		otherTabCaption: 'Other',
		
		addUncategorizedTab: function() {
			var otherTabExists = false;
			
			// find max ID of the models categories
			$scope.tabs.data.forEach(function(tabData) {
				if (tabData.name == $scope.tabs.otherTabCaption) {
					otherTabExists = true;
				}
			});
			
			// add to the tabs
			if (!otherTabExists) {
				$scope.tabs.data.push({
					id: -1, 
					name: $scope.tabs.otherTabCaption
				});
			}
		},
		
		current: '',
		
		load: function() {
			$http({
				method: 'get',
				url: 'api/get_models_categories.php'
			}).then(function(res) {
				if (res && res.data && res.data.models_categories) {
					
					// set the tabs
					$scope.tabs.data = res.data.models_categories;
					
					// check if there are uncategorized models
					if ($scope.models.thereAreUncategorizedModels(res.data.models_categories)) {
						console.log('tabs: there are uncategorized');
						$scope.tabs.addUncategorizedTab();
					}
					
					
					if ($scope.tabs.data.length > 0) {
						$scope.tabs.current = $scope.tabs.data[0].id;
					}
					
				} else {
					alert('error reading models categories');
				}
			}, function() {
				alert('Network error');
			});
		}
	};
	
	$scope.dates = {
		inProgress: false,
		
		// add new date - dates picker
		newDate: '',
		newDateError: false,
		newDateOpen: false,
		newDateOptions: {
			
		},
		addNewDate: function() {
			if (!$rootScope.loggedInUser) {
				return;
			}
			
			// validate the new date
			if (!$scope.dates.newDate) {
				$scope.dates.newDateError = 'Please select date! Click on the icon or on the input above';
				return;
			}
			$scope.dates.newDateError = false;
			
			// get the timestamp from the selected date
			var newDateTs = Math.floor($scope.dates.newDate.getTime() / 1000);
			
			$scope.dates.inProgress = true;
			$http({
				method: 'post',
				url: 'api/add_date.php',
				data: {
					date: newDateTs
				}
			}).then(function(res) {
				if (!res.data) {
					$scope.dates.newDateError = 'add_new_date: cannot receive response';
					return console.error('add_new_date: cannot receive response', res);
				}
				
				if (res.data.result == 'error') {
					$scope.dates.newDateError = res.data.error || 'error occurred'
					return;
				}
				
				if (res.data.result == 'ok') {
					// clear the new date
					$scope.dates.newDateError = false;
					$scope.dates.newDate = '';
					
					// reload the dates list
					$scope.dates.load();
					
					return;
				}
				
				$scope.dates.newDateError = 'error occurred';
			}, function() {
				$scope.dates.newDateError = 'server error';
			}).finally(function() {
				$scope.dates.inProgress = true;
			});
			
		},
		
		'delete': function(dateTs) {
			if (!confirm('Delete the dates with all its settings?')) {
				return;
			}
			
			
		},
		
		data: [],
		load: function() {
			if (!$rootScope.loggedInUser) {
				return;
			}
			
			$scope.dates.inProgress = true;
			
			$http.get('api/get_dates.php').then(function(res) {
				$scope.dates.data = res.data && res.data.dates ? res.data.dates : [];
			}).finally(function() {
				$scope.dates.inProgress = false;
			});
		}
		
	};
	
	$rootScope.$watch('hasRestrictedAccess', function(hasRestrictedAccess) {
		if (hasRestrictedAccess) {
			$scope.models.load();
			$scope.dates.load();
			$scope.tabs.load();
		}
	});
	
}]);

webApp.filter('filterModelsByCategory', [function() {
	return function(models, category) {
		console.log('models', models, 'category', category);
		var filtered = [];
		
		models.forEach(function(model) {
			if (model.category == category || (!model.category && category == -1)) {
				filtered.push(model);
			}
		});
		
		return filtered;
	};
}]);