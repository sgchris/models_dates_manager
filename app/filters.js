
webApp.filter('nl2br', ['$sce', function($sce) {
	var tag = (/xhtml/i).test(document.doctype) ? '<br />' : '<br>';
	return function(msg) {
		// ngSanitize's linky filter changes \r and \n to &#10; and &#13; respectively
		msg = (msg + '').replace(/(\r\n|\n\r|\r|\n|&#10;&#13;|&#13;&#10;|&#10;|&#13;)/g, tag + '$1');
		return $sce.trustAsHtml(msg);
	};
}]);


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

// filter model (which has "category" field) by the given category  (number)
webApp.filter('filterModelsByName', [function() {
	return function(models, filterString) {
		if (!filterString || filterString.trim() == '') {
			return models;
		}
		
		// prepare the filter string
		filterString = filterString.trim().toLowerCase();
		
		// check the models list
		var filtered = [];
		if (models && models.length > 0) {
			models.forEach(function(model) {
				if (!filterString || model.name.toLowerCase().indexOf(filterString) >= 0) {
					filtered.push(model);
				}
			});
		}
		
		return filtered;
	};
}]);
