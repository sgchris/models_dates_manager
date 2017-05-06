webApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/', {
			templateUrl: 'views/homepage.html',
			controller: 'HomepageController'
		})
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
			redirectTo: '/'
		});
}]);