webApp.controller('HomepageController', ['$rootScope', '$scope', '$http', '$uibModal', 
	function($rootScope, $scope, $http, $modal) {

	$scope.models = {
		inProgress: false,
		
		// new model related
		newModelName: '',
		newModelError: false,
		addNewModel: function() {
			if ($scope.models.newModelName.trim().length == 0) {
				$scope.models.newModelError = 'Please type model name in the input above';
				return;
			}
			$scope.models.newModelError = false;
			
			$scope.models.inProgress = true;
			$http({
				method: 'post',
				url: 'api/add_model.php',
				data: {
					name: $scope.models.newModelName.trim()
				}
			}).then(function(res) {
				if (!res.data) {
					$scope.dates.newDateError = 'add new model: cannot receive response';
					return console.error('add new model: cannot receive response', res);
				}
				
				if (res.data.result == 'error') {
					$scope.models.newModelError = res.data.error || 'error occurred'
					return;
				}
				
				if (res.data.result == 'ok') {
					// clear the new date
					$scope.models.newModelError = false;
					$scope.models.newDate = '';
					
					// reload the models list
					$scope.models.load();
					
					return;
				}
				
				$scope.models.newModelError = 'error occurred';
			}, function() {
				$scope.models.newModelError = 'server error';
			}).finally(function() {
				$scope.models.inProgress = true;
			});
			
		},
		
		data: [],
		
		load: function() {
			$scope.models.inProgress = true;
			
			$http.get('api/get_models.php').then(function(res) {
				$scope.models.data = res.data;
			}).finally(function() {
				$scope.models.inProgress = false;
			});
		}
	};
	
	$scope.dates = {
		inProgress: false,
		
		// add new date - dates picker
		newDate: '',
		newDateError: false,
		newDateOpen: false,
		newDateOptions: {
			
		},
		addNewDate: function() {
			// validate the new date
			if (!$scope.dates.newDate) {
				$scope.dates.newDateError = 'Please select date! Click on the icon or on the input above';
				return;
			}
			$scope.dates.newDateError = false;
			
			// get the timestamp from the selected date
			var newDateTs = Math.floor($scope.dates.newDate.getTime() / 1000);
			
			$scope.dates.inProgress = true;
			$http({
				method: 'post',
				url: 'api/add_date.php',
				data: {
					date: newDateTs
				}
			}).then(function(res) {
				if (!res.data) {
					$scope.dates.newDateError = 'add_new_date: cannot receive response';
					return console.error('add_new_date: cannot receive response', res);
				}
				
				if (res.data.result == 'error') {
					$scope.dates.newDateError = res.data.error || 'error occurred'
					return;
				}
				
				if (res.data.result == 'ok') {
					// clear the new date
					$scope.dates.newDateError = false;
					$scope.dates.newDate = '';
					
					// reload the dates list
					$scope.dates.load();
					
					return;
				}
				
				$scope.dates.newDateError = 'error occurred';
			}, function() {
				$scope.dates.newDateError = 'server error';
			}).finally(function() {
				$scope.dates.inProgress = true;
			});
			
		},
		
		'delete': function(dateTs) {
			if (!confirm('Delete the dates with all its settings?')) {
				return;
			}
			
			
		},
		
		data: [],
		load: function() {
			$scope.dates.inProgress = true;
			
			$http.get('api/get_dates.php').then(function(res) {
				$scope.dates.data = res.data && res.data.dates ? res.data.dates : [];
			}).finally(function() {
				$scope.dates.inProgress = false;
			});
		}
		
	};
	
	// init 
	$scope.models.load();
	$scope.dates.load();
	
}]);