
// check if the input/textarea contains hebrew letter, and change the input direction
webApp.directive('fixDirection', ['$timeout', function($timeout) {
	var hebrewLetters = 'אבגדהוזחטיכךלמםנןסעפצץקרשת';
	var hasHebrewLetters = function(str) {
		if (!str || !str.length) return false;

		var hasHebLetter = false;
		hebrewLetters.split('').forEach(function(hebLetter) {
			if (str.indexOf(hebLetter) >= 0) {
				hasHebLetter = true;
			}
		});

		return hasHebLetter;
	};

	var elemIsInput = function(elem) {
		return (elem.tagName == 'INPUT' || elem.tagName == 'TEXTAREA');
	};

	var fixElemDirection = function(elem, scope) {
		$timeout(function() {
			var val = elemIsInput(elem) && scope ? scope.ngModel : elem.textContent;
			var direction = hasHebrewLetters(val) ? 'rtl' : 'ltr';
			angular.element(elem).css('direction', direction);
		});
	};

	return {
		restrict: 'A',

		scope: {
			ngModel: '=?',
		},
		
		link: function(scope, element, attributes) {
			element = element[0];

			// check initial state
			fixElemDirection(element, scope);

			// watch for input changes
			if (elemIsInput(element)) {
				scope.$watch('ngModel', function(newVal) {
					fixElemDirection(element, scope);
				});
			}
		}
	}
}]);

