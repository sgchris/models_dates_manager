
// convert seconds to milliseconds
webApp.filter('secondsToMilliseconds', function() {
	return function(seconds) {
		return seconds * 1000;
	}
});

// filter model (which has "category" field) by the given category  (number)
webApp.filter('filterModelsByCategory', [function() {
	return function(models, category) {
		var filtered = [];
		
		if (models && models.length > 0) {
			models.forEach(function(model) {
				if (model.category == category || (!model.category && category == -1)) {
					filtered.push(model);
				}
			});
		}
		
		return filtered;
	};
}]);