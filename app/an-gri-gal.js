
webApp.directive('anGriGal', [function() {
	return {
		replace: true,
		scope: {
			images: '=',
			onClose: '&'
		},
		link: function(scope, element, attrs) {
			scope.currentImage = 0;
			scope.mainImage = scope.images[scope.currentImage];
			
			scope.next = function() {
				if (scope.currentImage < scope.images.length - 1) {
					scope.currentImage++;
					scope.mainImage = scope.images[scope.currentImage];
				}
			};
			
			scope.prev = function() {
				if (scope.currentImage > 0) {
					scope.currentImage--;
					scope.mainImage = scope.images[scope.currentImage];
				}
			};
		},
		template: '<div class="ang-gri-gal-wrapper">' + 
				'<div class="ang-gri-gal-close" ng-click="onClose();">&times;</div>' + 
				'<div class="ang-gri-gal-next" ng-click="next();">&gt;</div>' + 
				'<div class="ang-gri-gal-prev" ng-click="prev();">&lt;</div>' +
				'<div class="ang-gri-gal-main-image">' + 
				'	<img ng-src="{{$root.IMAGES_BASE_URL}}/{{mainImage}}" ng-click="next();" />' +
				'</div>' +
			'</div>'
	};
}]);
