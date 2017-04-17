webApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/about', {
			templateUrl: 'views/about.html',
			controller: 'AboutController'
		})
		.when('/date/:hash', {
			templateUrl: 'views/date.html',
			controller: 'DateController'
		})
		.when('/model/:modelId', {
			templateUrl: 'views/model.html',
			controller: 'ModelController'
		})
		.otherwise({
			templateUrl: 'views/homepage.html',
			controller: 'HomepageController'
		});
}]);