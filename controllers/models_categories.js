webApp.controller('ModelsCategoriesController', ['$rootScope', '$scope', '$http', 'modelsCategoriesService', 
	function($rootScope, $scope, $http, modelsCategoriesService) {
	
	$scope.models_categories = {
		
		inProgress: false,
		
		new_models_category_name: '',
		new_models_category_description: '',
		
		data: [],
		
		add: function() {
			if (!$scope.models_categories.new_models_category_name) {
				return;
			}

			$http({
				method: 'post',
				url: 'api/add_models_category.php',
				data: {
					name: $scope.models_categories.new_models_category_name,
					description: $scope.models_categories.new_models_category_description
				}
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					// reset the "new" form
					$scope.models_categories.new_models_category_name = '';
					$scope.models_categories.new_models_category_description = '';

					// reload models categories list
					$scope.models_categories.load();
				} else {
					alert('Error adding new models category');
				}
			}, function() {
				alert('Network error');
			});
		},
		
		update: function(modelsCategoryId) {

			// find the model category in the data
			var name = false, description;
			$scope.models_categories.data.forEach(function(modelsCategoryData) {
				if (modelsCategoryData.id == modelsCategoryId) {
					name = modelsCategoryData.name;
					description = modelsCategoryData.description;
				}
			});

			if (name === false) {
				alert('Cannot find the model category');
				return false;
			}

			$http({
				method: 'post',
				url: 'api/update_models_category.php',
				data: {
					id: modelsCategoryId,
					name: name,
					description: description
				}
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					// reload models categories list
					$scope.models_categories.load();
				} else {
					alert('Error updating the models category');
				}
			}, function() {
				alert('Network error');
			});
		},
		
		delete: function(modelsCategoryId) {
			if (!modelsCategoryId || !confirm('Delete?')) {
				return;
			}
			
			$scope.models_categories.inProgress = true;
			
			$http({
				method: 'post',
				url: 'api/delete_models_category.php',
				data: {
					id: modelsCategoryId
				}
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					// .. success
					$scope.models_categories.load();
				} else {
					alert('Error adding new models category');
				}
			}, function() {
				alert('Network error');
			}).finally(function() {
				$scope.models_categories.inProgress = false;
			});
		},
		
		load: function() {
			modelsCategoriesService.load(function(modelsCategories) {
				$scope.models_categories.data = modelsCategories;
			}, 'forceReload = true');
		}
	};
	
		
	$rootScope.$watch('hasRestrictedAccess', function(hasRestrictedAccess) {
		if (hasRestrictedAccess) {
			$scope.models_categories.load();
		}
	});
	
}]);
