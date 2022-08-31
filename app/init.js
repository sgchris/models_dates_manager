webApp = angular.module('WebApp', ['ui.router', 'ui.bootstrap', 'ngFileUpload', 'ngclipboard', 'ngCookies']);

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


webApp.run(['$rootScope', '$window', '$http', '$cookies', function($rootScope, $window, $http, $cookies) {

	// check if is mobile (from http://detectmobilebrowsers.com/)
	$rootScope.isMobileFn = function() {
		return (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(navigator.userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4)));
	};
	$rootScope.isMobile = $rootScope.isMobileFn();

	// add country code to the phone number
	$rootScope.addCountryCode = function(phoneNumber) {
		if (!phoneNumber) {
			return phoneNumber;
		}
		if (phoneNumber.indexOf('05') == 0) {
			return '972' + phoneNumber.substr(1);
		}
		return phoneNumber;
	};

	$rootScope.loginFormUsername = "";
	$rootScope.loginFormPassword = "";
	$rootScope.loginInProcess = false;
	$rootScope.loggedInUser = false;	
	$rootScope.hasRestrictedAccess = false;
	
	$rootScope.loginFormSubmit = () => {
		const username = $rootScope.loginFormUsername; 
		const password = $rootScope.loginFormPassword; 
		$rootScope.loginInProcess = true;

		// update the access token on the server side
		$http({
			method: 'post', 
			url: 'api/set_access_token2.php',
			withCredentials: true,
			data: { username, password }
		}).then(function(res) {
			if (res && res.data && res.data.result == "ok") {
				$rootScope.hasRestrictedAccess = true;
				$rootScope.loggedInUser = res.data.name;
				
				// set cookie
				var now = new Date();
				var expires = new Date(now.getFullYear(), now.getMonth() + 3, now.getDate());
				$cookies.put('access_token', res.data.access_token, {expires})
			} else if (res && res.data && res.data.result == "error") {
				if (res.data.error) {
					alert(res.data.error);
				}
			}
		}, function() {
			alert('Authentication failed');
		}).finally(() => {
			$rootScope.loginInProcess = false;
		});
	}

	$rootScope.checkIsLoggedIn = () => {
		$rootScope.loginInProcess = true;

		// update the access token on the server side
		$http({
			method: 'get', 
			url: 'api/is_logged_in.php'
		}).then(function(res) {
			if (res && res.data && res.data.result == "ok") {
				if (res.data.isLoggedIn) {
					$rootScope.hasRestrictedAccess = true;
					$rootScope.loggedInUser = res.data.name;
				}
			}
		}).finally(() => {
			$rootScope.loginInProcess = false;
		})
	}
	$rootScope.checkIsLoggedIn();
	
	// models' images base URL
	$rootScope.IMAGES_BASE_URL = window.IMAGES_BASE_URL;
	
	// store the controller name in the rootScope
	$rootScope.$on('$routeChangeSuccess', function(ev, data) {
		if (data && data.controller) {
			var controller = data.controller;
			controller = controller.charAt(0).toLowerCase() + controller.slice(1);
			controller = controller.replace(/Controller/g, '');
			$rootScope.controller = controller;
		}
	});
	
	// check the resolution and determine mobile access
	var checkResolution = function() {
		$rootScope.isMobile = false;
		if ($window.innerWidth < 992) {
			$rootScope.isMobile = true;
		}
	};
	
	checkResolution();
	angular.element($window).on('resize', function() {
		$rootScope.$apply(function() {
			checkResolution();
		});
	});
}]);

