webApp.controller('ModelController', ['$rootScope', '$routeParams', '$scope', '$http', 
	function($rootScope, $routeParams, $scope, $http) {
	
	$scope.model = {
		inProgress: false,
		
		id: $routeParams.modelId,
		
		details: {},
		
		load: function() {
			
			$scope.model.inProgress = true;
			
			$http({
				method: 'get',
				url: 'api/get_models.php'
			}).then(function(res) {
				var foundModel = false;
				if (res.data && res.data.result == 'ok') {
					// find the current model
					res.data.models.forEach(function(modelObj) {
						if (modelObj.id == $scope.model.id) {
							$scope.model.details = modelObj;
							foundModel = true;
						}
					});
				}
				
				if (!foundModel) {
					alert('cannot load model details');
				}
			}).finally(function() {
				$scope.model.inProgress = false;
			});
		}
	};
	
	
	$scope.uploader = {
		
		files: false,
		
		uploadFiles: function(files) {
			console.log('file', $scope.uploader.files);
		},
	};
	
	$scope.model.load();
	
}]);