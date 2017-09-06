/**
 * Tabs with models categories
 */
webApp.directive('modelsCategoriesTabs', ['$http', 'modelsCategoriesService', function($http, modelsCategoriesService) {
	
	var otherTabCaption = 'Other';
	
	return {
		//require: 'ngModel',
		replace: true,
		scope: {
			addUncategorized: '=?',
			
			// initial value
			initialValue: '@?',
			
			// callbacks (expressions)
			onSelect: '&',
			
			// fire a callback when the data is loaded from the server
			onLoad: '&?',
		},
		link: function(scope, element, attributes) {
			scope.modelsCategories = [];
			
			// implement promise resolve
			modelsCategoriesService.load(function(modelsCategories) {
				
				// set the data in the scope
				scope.modelsCategories = modelsCategories;
				
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