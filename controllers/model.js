webApp.controller('ModelController', ['$rootScope', '$stateParams', '$scope', '$http', '$timeout', 'Upload', 'modelsCategoriesService',
	function($rootScope, $stateParams, $scope, $http, $timeout, Upload, modelsCategoriesService) {
	
	$scope.gallery = {
		isOpen: false,
		
		initialIndex: 0,
		setInitialIndex: function(newIndex) {
			$scope.gallery.initialIndex = newIndex;
		},
		
		close: function() {
			$scope.gallery.isOpen = false;
		},
		
		open: function() {
			$scope.gallery.isOpen = true;
		}
	};
		
	$scope.galleryIsOpen = false;
	$scope.galleryIsOpen = false;
		
	$scope.model = {
		inProgress: false,
		
		id: null,
		
		hash: $stateParams.modelHash,
		
		details: {},
		
		detailsForm: null,
		
		update: function() {
			$scope.model.inProgress = true;
			
			var data = {
				model_id: $scope.model.id,
				name: $scope.model.details.name,
				category: $scope.model.details.category,
				phone: $scope.model.details.phone,
				instagram: $scope.model.details.instagram,
				notes: $scope.model.details.notes,
				private_notes: $scope.model.details.private_notes,
				tags: $scope.model.details.tags,
			};
			
			$http({
				method: 'post',
				url: 'api/update_model.php',
				data: data
			}).then(function(res) {
				if (res.data && res.data.result == 'ok') {
					
					if ($scope.model.detailsForm) {
						$scope.model.detailsForm.$setPristine();
					}
					
					$scope.model.load();
					return;
				}
				
				alert('cannot update model notes. ' + JSON.stringify(res.data));
			}, function() {
				alert('Network error');
			}).finally(function() {
				$scope.model.inProgress = false;
			});
		},
		
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
			}, function() {
				alert('Network error');
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
			}, function() {
				alert('Network error');
			}).finally(function() {
				$scope.model.inProgress = false;
			});
		},
		
		load: function() {
			
			$scope.model.inProgress = true;
			
			$http({
				method: 'get',
				url: 'api/get_models.php',
				params: {
					model_hash: $scope.model.hash
				}
			}).then(function(res) {
				var foundModel = false;
				if (res.data && res.data.result == 'ok' && res.data.models && res.data.models.length === 1) {
					$scope.model.id = res.data.models[0].id;
					$scope.model.details = res.data.models[0];
				} else {
					alert('cannot load model details');
					console.error('res', res);
				}
				
			}, function() {
				alert('Network error');
			}).finally(function() {
				$scope.model.inProgress = false;
			});
		}
	};
	
	
	$scope.uploader = {
		
		files: false,
		errorMessage: '',
		
		uploadFiles: function(files) {
			if (!files) {
				files = $scope.uploader.files;
			}
			
			angular.forEach(files, function(file) {
				file.upload = Upload.upload({
					url: 'api/upload_model_images.php',
					data: {
						model_id: $scope.model.id,
						file: file
					}
				});

				file.upload.then(function (response) {
					$scope.uploader.errorMessage = '';
					
					if (response.data && response.data.result == 'ok') {
						// reload the model
						$scope.model.load();
						return;
					}
					
					
					if (response.data.error && typeof(response.data.error) == 'string') {
						$scope.uploader.errorMessage = response.data.error;
					} else if (response.data.error.errors && response.data.error.errors instanceof Object) {
						Object.keys(response.data.error.errors).forEach(function(k) {
							$scope.uploader.errorMessage+= JSON.stringify(response.data.error.errors[k]) + '; ';
						});
					} else {
						$scope.uploader.errorMessage = 'Error occurred';
						console.error(response.data);
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
	
	$scope.models_categories = {
		inProgress: false,
		
		data: [],
		
		load: function() {
			modelsCategoriesService.load(function(modelsCategories) {
				$scope.models_categories.data = modelsCategories;
			});
		}
	}
	
	$scope.$watch('uploader.files', function(newVal) {
		$scope.uploader.uploadFiles();
	});

	// load the model's data
	$scope.model.load();
	$scope.models_categories.load();
	
	$rootScope.$watch('hasRestrictedAccess', function(hasRestrictedAccess) {
		if (hasRestrictedAccess) {
			
		}
	});
	
}]);