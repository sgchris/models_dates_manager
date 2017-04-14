webApp.controller('HomepageController', ['$rootScope', '$scope', '$http', '$uibModal', 
	function($rootScope, $scope, $http, $modal) {

	$scope.models = {
		inProgress: false,
		
		// new model related
		newModelName: '',
		addNewModel: function() {
			console.log('adding new model', $scope.models.newModelName);
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