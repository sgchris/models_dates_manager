webApp.controller('ModelsCategoriesController', ['$rootScope', '$scope', '$http', 'modelsCategoriesService', 
	function($rootScope, $scope, $http, modelsCategoriesService) {
	
	$scope.models_categories = {
		
		inProgress: false,
		
		new_models_category_name: '',
		
		data: [],
		
		add: function() {
			if (!$scope.models_categories.new_models_category_name) {
				return;
			}
			
			$scope.models_categories.inProgress = true;
			
			$http({
				method: 'post',
				url: 'api/add_models_category.php',
				data: {
					name: $scope.models_categories.new_models_category_name
				}
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					$scope.models_categories.new_models_category_name = '';
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
			});
		}
	};
	
		
	$rootScope.$watch('hasRestrictedAccess', function(hasRestrictedAccess) {
		if (hasRestrictedAccess) {
			$scope.models_categories.load();
		}
	});
	
}]);