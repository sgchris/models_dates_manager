
webApp.config(['$stateProvider', '$urlRouterProvider', '$locationProvider', function($stateProvider, $urlRouterProvider, $locationProvider) {
	
	$urlRouterProvider.otherwise("/");
	
	$stateProvider
		.state('home', { 
			url: "/", 
			templateUrl: 'views/homepage.html',
			controller: 'HomepageController'
		})
		.state('about', {
			url: '/about', 
			templateUrl: 'views/about.html',
			controller: 'AboutController'
		})
		.state('date', {
			url: '/date/:hash',
			templateUrl: 'views/date.html',
			controller: 'DateController'
		})
		.state('date.category', {
			url: '/date/:hash/:category',
			templateUrl: 'views/date.html',
			controller: 'DateController'
		})
		.state('model', {
			url: '/model/:modelHash',
			templateUrl: 'views/model.html',
			controller: 'ModelController'
		})
		.state('models_categories', {
			url: '/models_categories',
			templateUrl: 'views/models_categories.html',
			controller: 'ModelsCategoriesController'
		});
		
		$locationProvider.hashPrefix('');
}]);