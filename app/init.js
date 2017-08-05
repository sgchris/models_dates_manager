webApp = angular.module('WebApp', ['ui.route', 'ui.bootstrap', 'ngFileUpload']);

webApp.config(['$httpProvider', function($httpProvider) {

	// Use x-www-form-urlencoded Content-Type
	$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

	/**
	 * The workhorse; converts an object to x-www-form-urlencoded serialization.
	 * @param {Object} obj
	 * @return {String}
	 */
	var param = function(obj) {
		var query = '',
			name, value, fullSubName, subName, subValue, innerObj, i;

		for (name in obj) {
			value = obj[name];

			if (value instanceof Array) {
				for (i = 0; i < value.length; ++i) {
					subValue = value[i];
					fullSubName = name + '[' + i + ']';
					innerObj = {};
					innerObj[fullSubName] = subValue;
					query += param(innerObj) + '&';
				}
			} else if (value instanceof Object) {
				for (subName in value) {
					subValue = value[subName];
					fullSubName = name + '[' + subName + ']';
					innerObj = {};
					innerObj[fullSubName] = subValue;
					query += param(innerObj) + '&';
				}
			} else if (value !== undefined && value !== null) {
				query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
			}
		}

		return query.length ? query.substr(0, query.length - 1) : query;
	};

	// Override $http service's default transformRequest
	$httpProvider.defaults.transformRequest = [function(data) {
		return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
	}];

}]);


webApp.run(['$rootScope', '$window', '$http', function($rootScope, $window, $http) {
	
	// models' images base URL
	$rootScope.IMAGES_BASE_URL = window.IMAGES_BASE_URL;
	
	$rootScope.loginInProcess = true;
	
	$rootScope.hasRestrictedAccess = false;
	
	// store the controller name in the rootScope
	$rootScope.$on('$routeChangeSuccess', function(ev, data) {
		if (data && data.controller) {
			var controller = data.controller;
			controller = controller.charAt(0).toLowerCase() + controller.slice(1);
			controller = controller.replace(/Controller/g, '');
			$rootScope.controller = controller;
		}
	});
	
	// callback for FB authentication
	$window.statusChangeCallback = function(response) {
		var accessToken = response && response.authResponse && response.authResponse.accessToken ? 
			response.authResponse.accessToken : false;
		
		$rootScope.$apply(function() {
			if (response.status === 'connected') {
				
				// temporarily set the logged in user
				$rootScope.loggedInUser = true;
				
				// update the access token on the server side
				$http({
					method: 'post', 
					url: 'api/set_access_token.php',
					data: {
						access_token: accessToken
					}
				}).then(function(res) {
					$rootScope.hasRestrictedAccess = res.data.has_restricted_access;
				}, function() {
					alert('could not authenticate the server');
				});
				
				// get logged in user details
				FB.api('/me', function(response) {
					$rootScope.$apply(function() {
						$rootScope.loggedInUser = response;
					});
				});
				
			} else {
				$rootScope.loggedInUser = false;
			}
			
			$rootScope.loginInProcess = false;
		});
	};
	
	$window.fbAsyncInit = function() {
		FB.init({
			appId: '1464574370233315',
			cookie: true,
			xfbml: true,
			version: 'v2.8'
		});
		
		FB.AppEvents.logPageView();
		
		FB.getLoginStatus(function(response) {
			statusChangeCallback(response);
		});
	};

	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {
			return;
		}
		js = d.createElement(s);
		js.id = id;
		js.src = "//connect.facebook.net/en_US/sdk.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	
}]);

