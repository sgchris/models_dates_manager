/**
 * Tabs with models categories
 */
webApp.directive('modelsCategoriesTabs', ['$http', '$q', function($http, $q) {
	
	var modelsCategoriesCache = false;
	
	var otherTabCaption = 'Other';
	
	return {
		//require: 'ngModel',
		replace: true,
		scope: {
			addUncategorized: '=?',
			
			// initial value
			initialValue: '&?',
			
			// callbacks (expressions)
			onSelect: '&',
			
			// fire a callback when the data is loaded from the server
			onLoad: '&?',
		},
		link: function(scope, element, attributes) {
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
					// set the data in the scope
					scope.modelsCategories = modelsCategories = res.data.models_categories;
					
					// check if adding "uncategorized" tab needed
					if (scope.addUncategorized) {
						scope.modelsCategories.push({
							id: -1,
							name: otherTabCaption
						});
					}
					
					// Check if the initial value was provided
					if (scope.initialValue) {
						// find the value in the models categories list
						if (scope.modelsCategories && scope.modelsCategories.length > 0) {
							scope.modelsCategories.forEach(function(modelCategory) {
								if (modelCategory.name == scope.initialValue) {
									// assign the object
									scope.current = modelCategory;
								}
							});
						}
					} 
					
					// fallback - take the first value
					if (!scope.current) {
						scope.current = (scope.modelsCategories && scope.modelsCategories.length > 0) ? scope.modelsCategories[0] : '';
					}
					
					// call user callback
					if (typeof(scope.onLoad) == 'function') {
						scope.onLoad({modelsCategories: scope.modelsCategories});
					}
				}
			});
			
			// callback when tab is clicked
			scope.tabClicked = function(newTab) {
				// set the new current tab ID
				scope.current = newTab;
				
				// invoke the scope callback
				scope.onSelect({newSelectedTab: newTab});
			};
			
		},
		template: 
			'<div>' + 
				'<ul class="nav nav-tabs">' + 
					'<li ng-repeat="modelCategory in modelsCategories" ng-class="{\'active\': modelCategory.id == current.id}">' + 
						'<a href="javascript:;" ng-click="tabClicked(modelCategory);">{{ modelCategory.name }}</a>' + 
					'</li>' + 
				'</ul>' + 
			'</div>'
	};
}]);