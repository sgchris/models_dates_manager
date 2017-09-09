var modelBoxCoreFunction = function($http, modelsCategoriesService) {
	
	return {
		//require: 'ngModel',
		replace: true,
		scope: {
			model: '=ngModel',
			
			// callbacks (expressions)
			onDelete: '&?',
			onRelocate: '&?',
			onExclude: '&?',
			onInclude: '&?',
			onChoose: '&?',
			onUnChoose: '&?'
		},
		link: function(scope, element, attributes) {
			scope.defaultImage = 'images/model_silhouette.png';
			
			// load the category name of the model
			scope.modelCategoryName = '';
			if (scope.model.category && parseInt(scope.model.category) > 0) {
				modelsCategoriesService.load(function(modelsCategories) {
					modelsCategories.forEach(function(modelCategory) {
						if (modelCategory.id == scope.model.category) {
							scope.modelCategoryName = modelCategory.name;
						}
					});
				});
			}
			
			scope.galleryIsOpen = false;
			
			// set the initial main image
			scope.mainImage = scope.model.images[0] ? 
				(scope.$root.IMAGES_BASE_URL + '/' + scope.model.images[0]) : 
				scope.defaultImage;
				
			scope.setMainImage = function(imageNumber) {
				scope.mainImage = scope.$root.IMAGES_BASE_URL + '/' + scope.model.images[imageNumber];
			};
			
			scope.openGallery = function() {
				scope.galleryIsOpen = !scope.galleryIsOpen;
			};
			
			// check if "exclude" callback is defined
			scope.showExcludeButton = (typeof(scope.onExclude) == 'function');
			
			// check if "include" callback is defined
			scope.showIncludeButton = (typeof(scope.onInclude) == 'function');
			
			// check if "relocate" callback is defined
			scope.showRelocateButton = (typeof(scope.onRelocate) == 'function');
			
			// check if "delete" callback is defined
			scope.showDeleteButton = (typeof(scope.onDelete) == 'function');
			
			// check if "Choose" callback is defined
			scope.showChooseButton = (typeof(scope.onChoose) == 'function');
			
			// check if "UnChoose" callback is defined
			scope.showUnChooseButton = (typeof(scope.onUnChoose) == 'function');
			
			/**
			 * move the model to the top/bottom of the list
			 * @param int modelId
			 * @param string newLocation - "top" / "bottom"
			 */
			scope.relocateModel = function(modelId, newLocation) {
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
						if (typeof(scope.onRelocate) == 'function') {
							scope.onRelocate();
							return;
						}
						return;
					}
					
					var errorString = res.data.error || 'Relocation failed';
					alert(errorString);
				}, function() {
					alert('Relocation failed - server error');
				});
			};
			
			/**
			 * delete the model
			 * @param int modelId
			 */
			scope.deleteModel = function(modelId) {
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
						if (typeof(scope.onDelete) == 'function') {
							scope.onDelete();
							return;
						}
						return;
					}
					
					var errorString = res.data.error || 'Delete failed';
					alert(errorString);
				}, function() {
					alert('Delete failed - server error');
				});
			};
		},
		templateUrl: 'views/directives/model-box.html',
	}
};

webApp.directive('modelBox', ['$http', 'modelsCategoriesService', function($http, modelsCategoriesService) {
	return modelBoxCoreFunction($http, modelsCategoriesService);
}]);

webApp.directive('modelBoxSmall', ['$http', 'modelsCategoriesService', function($http, modelsCategoriesService) {
	var directiveObject = modelBoxCoreFunction($http, modelsCategoriesService);
	
	// replace the base template with the one for small box
	directiveObject.templateUrl = 'views/directives/model-box-small.html'
	
	return directiveObject;
}]);