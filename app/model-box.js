webApp.directive('modelBox', [function() {
	return {
		require: 'ngModel',
		link: function(scope, element, attributes, ngModel) {
			console.log('ngModel', ngModel);
		},
		templateUrl: 'views/directives/model-box.html',
	}
}]);