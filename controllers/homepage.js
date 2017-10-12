webApp.controller('HomepageController', ['$rootScope', '$scope', '$http', '$state', '$uibModal', 'modelsCategoriesService',
	function($rootScope, $scope, $http, $state, $modal, modelsCategoriesService) {
	
	var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
	
	$scope.models = {
		inProgress: false,
		
		// new model related
		newModelName: '',
		newModelError: false,
		_mobile_addNewModel: function() {
			$scope.models.newModelName = prompt('New Model Name');
			$scope.models.addNewModel();
		},
		addNewModel: function() {
			if (!$rootScope.loggedInUser) {
				return;
			}
			
			// if no name provided, give according to the current date/time
			var emptyName = false;
			if ($scope.models.newModelName.trim().length == 0) {
				var now = new Date();
				$scope.models.newModelName = 'Model ' + now.getDate() + ' ' + days[now.getDay()] + ' ' + now.getHours() + '.' + now.getMinutes();
				
				emptyName = true;
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
					
					// the API returns the hash of the new model
					var newModelHash = res.data.hash;
					
					// reload the models list
					var promise = $scope.models.load();
					
					if (emptyName) {
						$state.go('model', {modelHash: newModelHash});
					}
					return;
				}
				
				$scope.models.newModelError = 'error occurred';
			}, function() {
				$scope.models.newModelError = 'server error';
			}).finally(function() {
				$scope.models.inProgress = true;
			});
			
		},
		
		relocateModel: function(modelId, newLocation) {
			if (!confirm('Move the model to the ' + newLocation + '?')) {
				return;
			}
			
			// call the API
			$http({
				method: 'post',
				url: 'api/relocate_model.php',
				data: {
					model_id: modelId,
					new_location: newLocation
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.models.load();
					return;
				}
				
				var errorString = res.data.error || 'Relocation failed';
				alert(errorString);
			}, function() {
				alert('Relocation failed - server error');
			});
		},
		
		deleteModel: function(modelId) {
			if (!confirm('Delete the model?')) {
				return;
			}
			
			// call the API
			$http({
				method: 'post',
				url: 'api/delete_model.php',
				data: {
					model_id: modelId
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.models.load();
					return;
				}
				
				var errorString = res.data.error || 'Delete failed';
				alert(errorString);
			}, function() {
				alert('Delete failed - server error');
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
			
			var promise = $http.get('api/get_models.php');
			
			promise.then(function(res) {
				$scope.models.data = res.data && res.data.models ? res.data.models : [];
				
				// check if uncategorized tab should be added
				if ($scope.models.thereAreUncategorizedModels($scope.tabs.data)) {
					$scope.tabs.addUncategorizedTab();
				}
				
			}).finally(function() {
				$scope.models.inProgress = false;
			});
			
			return promise;
		}
	};
	
	$scope.sidebar = {
		expanded: true,
		
		shrinkExpandHomepageSidebar: function() {
			$scope.sidebar.expanded = !$scope.sidebar.expanded;
			
			if (typeof(window.localStorage) != 'undefined') {
				window.localStorage.setItem('sidebarExpanded', $scope.sidebar.expanded ? '1' : '0');
			}
		},
		
		init: function() {
			if (typeof(window.localStorage) != 'undefined') {
				var currentValue = window.localStorage.getItem('sidebarExpanded');
				$scope.sidebar.expanded = (currentValue === null) ? true : !!parseInt(currentValue);
			}
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
			modelsCategoriesService.load(function(modelsCategories) {
				$scope.tabs.data = modelsCategories;
				
				// check if there are uncategorized models
				if ($scope.models.thereAreUncategorizedModels(modelsCategories)) {
					$scope.tabs.addUncategorizedTab();
				}
				
				if ($scope.tabs.data.length > 0) {
					$scope.tabs.current = $scope.tabs.data[0].id;
				}
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
	
	$scope.sidebar.init();
}]);
