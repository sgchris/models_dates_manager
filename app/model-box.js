webApp.directive('modelBox', [function() {
	return {
		//require: 'ngModel',
		replace: true,
		scope: {
			model: '=ngModel'
		},
		link: function(scope, element, attributes) {
			scope.defaultImage = 'images/model_silhouette.png';
			
			/**
			 * move the model to the top/bottom of the list
			 * @param int modelId
			 * @param string newLocation - "top" / "bottom"
			 */
			scope.relocateModel = function(modelId, newLocation) {
				
			};
			
			/**
			 * delete the model
			 * @param int modelId
			 */
			scope.deleteModel = function(modelId) {
				
			};
		},
		templateUrl: 'views/directives/model-box.html',
	}
}]);