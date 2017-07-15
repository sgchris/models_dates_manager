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
		.when('/date/:hash/:category', {
			templateUrl: 'views/date.html',
			controller: 'DateController'
		})
		.when('/model/:modelHash', {
			templateUrl: 'views/model.html',
			controller: 'ModelController'
		})
		.when('/models_categories', {
			templateUrl: 'views/models_categories.html',
			controller: 'ModelsCategoriesController'
		})
		.otherwise({
			redirectTo: '/'
		});
}]);