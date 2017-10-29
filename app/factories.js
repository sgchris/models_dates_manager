
// the colors service
webApp.factory('colorsService', [function() {
	// list of available colors
	var colors = {
		'default': '#F6F6F6',
		magenta: '#e1bee7',
		lightgreen: '#dcedc8',
		orange: '#ffccbc',
		blue: '#b3e5fc',
		turquoise: '#a7ffeb'
	};

	return {
		getColorNumber: function(colorName) {
			if (!colorName || typeof(colors[colorName]) == 'undefined') {
				return colors['default'];
			}
			
			return colors[colorName];
		},

		getAvailableColors: function() {
			return colors;
		}
	};
}]);

// models' categories service
webApp.factory('modelsCategoriesService', ['$http', '$q', function($http, $q) {
	
	// models categories will be stored here
	var modelsCategoriesCache = false;
	
	return {
		load: function(callbackFn, forceReload) {
			var loadModelsCategories;
			
			// load models categories from the server or from the cache
			if (!modelsCategoriesCache || forceReload) {
				loadModelsCategories = $http({
					method: 'get',
					url: 'api/get_models_categories.php'
				});
			} else {
				// take the cached results
				loadModelsCategories = $q(function(resolve, reject) {
					var retObj = {
						data: {
							result: 'ok',
							models_categories: modelsCategoriesCache
						}
					};
					
					resolve(retObj);
				});
			}
			
			// implement promise resolve
			loadModelsCategories.then(function(res) {
				if (res.data && res.data.result == 'ok') {
					// set the data in the cache
					modelsCategoriesCache = res.data.models_categories;
					
					// trigger the callback
					if (typeof(callbackFn) == 'function') {
						callbackFn(res.data.models_categories);
					}
				}
			}, function(res) {
				alert('error loading models categories');
				console.error('error loading models categories', res);
			});
		}
	};
}]);



webApp.factory('smallImagesService', ['$http', '$rootScope', function($http, $rootScope) {
	return {
		getSmall: function(imageName) {
			if (!imageName) {
				return '';
			}
			
			var smallImageName = imageName.replace(/\.jpg$/, '_small.jpg');
			if (window.SMALL_IMAGES_DATA && window.SMALL_IMAGES_DATA[smallImageName]) {
				return window.SMALL_IMAGES_DATA[smallImageName];
			} else {
				return $rootScope.IMAGES_BASE_URL + '/' + imageName;
			}
		},
		
		getMedium: function(imageName) {
			if (!imageName) {
				return '';
			}
			
			// add "_medium" suffix
			var smallImageName = imageName.replace(/\.jpg$/, '_medium.jpg');
			// add "medium" folder
			var smallImageName = smallImageName.replace(/\/([^\/]*?)\.jpg$/, '/medium/$1.jpg');
			
			return smallImageName;
		}
	};
	
}]);
