

webApp.filter('secondsToMilliseconds', function() {
	return function(seconds) {
		return seconds * 1000;
	}
});