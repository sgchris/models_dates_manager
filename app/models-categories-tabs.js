/**
 * Tabs with models categories
 */
webApp.directive('modelsCategoriesTabs', ['$http', function($http) {
	
	var modelsCategories = false;
	
	var otherTabCaption = 'Other';
	
	return {
		//require: 'ngModel',
		replace: true,
		scope: {
			addUncategorized: '=?',
			
			// callbacks (expressions)
			onSelect: '&',
		},
		link: function(scope, element, attributes) {
			
			// load models categories from the server
			if (modelsCategories === false) {
				$http({
					method: 'get',
					url: 'api/get_models_categories.php'
				}).then(function(res) {
					if (res.data && res.data.result == 'ok') {
						scope.modelsCategories = modelsCategories = res.data.models_categories;
						
						// the currently selected tab
						scope.current = (scope.modelsCategories && scope.modelsCategories.length > 0) ? scope.modelsCategories[0].id : '';
						
						// initial tab select
						if (scope.current) {
							scope.onSelect({newSelectedTab: scope.current});
						}
					}
				});
			} else {
				// take the cached results
				scope.modelsCategories = modelsCategories;
				
			}
			
			// check if adding "uncategorized" tab needed
			if (scope.addUncategorized) {
				scope.modelsCategories.push({
					id: -1,
					name: otherTabCaption
				});
			}
			
			// the currently selected tab
			scope.current = (scope.modelsCategories && scope.modelsCategories.length > 0) ? scope.modelsCategories[0].id : '';
			
			// callback when tab changes
			scope.tabChanged = function(newTabId) {
				// set the new current tab ID
				scope.current = newTabId;
				
				// invoke the callback
				scope.onSelect({newSelectedTab: newTabId});
			}
			
			// initial tab select
			if (scope.current) {
				scope.onSelect({newSelectedTab: scope.current});
			}
		},
		templateUrl: 'views/directives/models-categories-tabs.html',
	};
}]);