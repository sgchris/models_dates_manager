webApp.directive('modelBox', ['$http', function($http) {
	return {
		//require: 'ngModel',
		replace: true,
		scope: {
			model: '=ngModel',
			
			// callbacks (expressions)
			onDelete: '&?',
			onRelocate: '&?',
			onDismiss: '&?',
			onInclude: '&?'
		},
		link: function(scope, element, attributes) {
			scope.defaultImage = 'images/model_silhouette.png';
			
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
			
			// check if "dismiss" callback is defined
			scope.showDismissButton = (typeof(scope.onDismiss) == 'function');
			
			// check if "include" callback is defined
			scope.showIncludeButton = (typeof(scope.onInclude) == 'function');
			
			// check if "relocate" callback is defined
			scope.showRelocateButtons = (typeof(scope.onRelocate) == 'function');
			
			// check if "delete" callback is defined
			scope.showDeleteButtons = (typeof(scope.onDelete) == 'function');
			
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
						console.log('scope.onRelocate', scope.onRelocate, typeof(scope.onRelocate))
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
}]);