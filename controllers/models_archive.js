webApp.controller('ModelsArchiveController', ['$rootScope', '$scope', '$http', 'colorsService',
function($rootScope, $scope, $http, colorsService) {

	$scope.data = {
		models: [],

		load: function() {
			$http.get('api/get_models_archive.php').then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					$scope.data.models = res.data.models;
					return;
				}

				alert('Error loading archive models');
			}, function() {
				alert('connection failed');
			});
		}
	}
	
	$rootScope.$watch('hasRestrictedAccess', function(hasRestrictedAccess) {
		$scope.data.load();
	});
	
}]);
