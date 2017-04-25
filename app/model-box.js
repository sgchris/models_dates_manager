webApp.directive('modelBox', ['$http', function($http) {
	return {
		//require: 'ngModel',
		replace: true,
		scope: {
			model: '=ngModel',
			
			// expressions
			onDelete: '&',
			onRelocate: '&'
		},
		link: function(scope, element, attributes) {
			console.log('scope', scope);
			scope.defaultImage = 'images/model_silhouette.png';
			
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