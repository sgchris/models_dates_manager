webApp.controller('ListController', ['$rootScope', '$scope', '$state', '$stateParams', '$http', '$location', '$q', 
function($rootScope, $scope, $state, $stateParams, $http, $location, $q) {
	
	// identify the date by its haash
	var hash = $stateParams['hash'];

	$scope.list = {
		// list info
		data: {},

		// list of models (their hashes)
		models_hashes: [],

		allModels: [],

		updateName: function() {
			$http.post('api/update_list.php', {
				list_id: $scope.list.data.id,
				name: $scope.list.data.name
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					return;
				}

				alert('Cannot update the name of the list');
			}, function() {
				alert('Network error');
			});
		},

		addModel: function(model) {
			$http.post('api/add_model_to_list.php', {
				list_id: $scope.list.data.id,
				model_id: model.id
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					$scope.list.load();
					return;
				}

				alert('Cannot add model to the list');
			}, function() {
				alert('Network error');
			});
		},

		removeModel: function(model) {
			$http.post('api/remove_model_from_list.php', {
				list_id: $scope.list.data.id,
				model_id: model.id
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					$scope.list.load();
					return;
				}

				alert('Cannot add model to the list');
			}, function() {
				alert('Network error');
			});
		},

		loadAllModels: function() {
			$http.get('api/get_models.php').then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					$scope.list.allModels = res.data.models;
					return;
				}

				alert('Cannot get the list details');
			}, function() {
				alert('Network error');
			});
		},

		loadModels: function() {
			if ($scope.list.models_hashes.length === 0) {
				return;
			}

			$http({
				method: 'get',
				url: 'api/get_models.php',
				params: {
					'models_hashes[]': $scope.list.models_hashes
				}
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					$scope.list.models = res.data.models;
					return;
				}

				alert('Cannot get list models details');
			}, function() {
				alert('Network error');
			});

		},

		load: function() {
			var getListUrl = 'api/get_list.php?hash=' + hash;
			$http({
				method: 'get',
				url: 'api/get_list.php',
				params: {
					hash: hash
				}
			}).then(function(res) {
				if (res && res.data && res.data.result == 'ok') {
					$scope.list.data = res.data.list;
					$scope.list.models_hashes = res.data.models_hashes;
					
					// load the models' details
					$scope.list.loadModels();
					return;
				}

				alert('Cannot get the list details');
			}, function() {
				alert('Network error');
			});
		}
	};

	// tabs related functions
	$scope.tabs = {
		// current tab to filter the models
		current: '',
		
		// callback from the tabs directive
		tabClicked: function(newTab) {
			$scope.tabs.current = newTab.id;
		},
		
		// trigger when tabs are loaded
		dataLoaded: function(modelsCategories) {
			if (modelsCategories && modelsCategories.length) {
				$scope.tabs.current = modelsCategories[0].id;
			}
		}
	};
	
	// load the data when the restrictions info is ready
	$rootScope.$watch('hasRestrictedAccess', function(hasRestrictedAccess) {
		if (hasRestrictedAccess) {
			$scope.list.loadAllModels();
		}
	});

	$scope.list.load();
}]);

