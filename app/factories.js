
// models' categories service
webApp.factory('modelsCategoriesService', ['$http', '$q', function($http, $q) {
	
	// models categories will be stored here
	var modelsCategoriesCache = false;
	
	return {
		load: function(callbackFn) {
			var loadModelsCategories;
			
			// load models categories from the server or from the cache
			if (!modelsCategoriesCache) {
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