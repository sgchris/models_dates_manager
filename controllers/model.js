webApp.controller('ModelController', ['$rootScope', '$routeParams', '$scope', '$http', '$timeout', 'Upload', 
	function($rootScope, $routeParams, $scope, $http, $timeout, Upload) {
	
	$scope.model = {
		inProgress: false,
		
		id: $routeParams.modelId,
		
		details: {},
		
		deleteImage: function(imageUrl) {
			if (!confirm('Delete the image?')) {
				return;
			}
			
			$scope.model.inProgress = true;
			
			$http({
				method: 'post',
				url: 'api/delete_model_image.php',
				data: {
					model_id: $scope.model.id,
					image_url: imageUrl
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.model.load();
					return;
				}
				
				alert('cannot delete model\'s image. error:' + JSON.stringify(res.data));
			}).finally(function() {
				$scope.model.inProgress = false;
			});
		},
		
		makeMainImage: function(imageUrl) {
			$scope.model.inProgress = true;
			
			$http({
				method: 'post',
				url: 'api/make_main_model_image.php',
				data: {
					model_id: $scope.model.id,
					image_url: imageUrl
				}
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					$scope.model.load();
					return;
				}
				
				alert('cannot delete model\'s image. error:' + JSON.stringify(res.data));
			}).finally(function() {
				$scope.model.inProgress = false;
			});
		},
		
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
		errorMessage: '',
		
		uploadFiles: function(files, errFiles) {
			$scope.uploader.files = files;
			
			angular.forEach(files, function(file) {
				file.upload = Upload.upload({
					url: 'api/upload_model_images.php',
					data: {
						model_id: $scope.model.id,
						file: file
					}
				});

				file.upload.then(function (response) {
					if (response.data && response.data.result == 'ok') {
						// reload the model
						$scope.model.load();
						return;
					}
					
					$scope.uploader.errorMessage = ''
					
					if (response.data.error && typeof(response.data.error) == 'string') {
						$scope.uploader.errorMessage = response.data.error;
					} else {
						Object.keys(response.data).forEach(function(k) {
							$scope.uploader.errorMessage+= k + ':' + JSON.stringify(response.data[k]) + '; ';
						});
					}
					
				}, function (response) {
					if (response.status > 0) {
						$scope.uploader.errorMessage = response.status + ': ' + response.data;
					}
				}, function (evt) {
					file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
					console.log(file.progress);
				});
			});

		},
	};
	
	$scope.model.load();
	
}]);