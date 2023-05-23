
webApp.filter('nl2br', ['$sce', function($sce) {
	var tag = (/xhtml/i).test(document.doctype) ? '<br />' : '<br>';
	return function(msg) {
		// ngSanitize's linky filter changes \r and \n to &#10; and &#13; respectively
		msg = (msg + '').replace(/(\r\n|\n\r|\r|\n|&#10;&#13;|&#13;&#10;|&#10;|&#13;)/g, tag + '$1');
		return $sce.trustAsHtml(msg);
	};
}]);


webApp.filter('trustUrl', ['$sce', function($sce) {
	return function(recordingUrl) {
		return $sce.trustAsResourceUrl(recordingUrl);
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
webApp.filter('filterAlreadyChosenModels', [function() {
	return function(models, modelsList) {
		if (!modelsList || !modelsList.length) {
			return models;
		}
		
		// check the models list
		var filtered = [];
		if (models && models.length > 0) {
			models.forEach(function(model) {
				if (modelsList.indexOf(model.id) < 0) {
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

webApp.filter('linkify', function($sce) {
    return function(text) {
        var urlRegex = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
        var phoneRegex = /(\+?\d{1,4}?[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,9})/g;
        var result = text.replace(urlRegex, '<a href="$1" target="_blank">$1</a>');
        result = result.replace(phoneRegex, '<a href="tel:$1">$1</a>');
        return $sce.trustAsHtml(result);
    };
});
