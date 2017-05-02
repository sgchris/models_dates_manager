
webApp.directive('anGriGal', ['$window', function($window) {
	return {
		replace: true,
		scope: {
			// list of images
			images: '=',
			// start with image number
			initialIndex: '=',
			// on close callback
			onClose: '&'
		},
		link: function(scope, element, attrs) {
			
			// receive initial index
			if (!scope.initialIndex || scope.initialIndex < 0 || scope.initialIndex > scope.images.length) {
				scope.initialIndex = 0;
			}
			
			scope.currentImage = scope.initialIndex;
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
			
			scope.closeGal = function(e) {
				if (typeof(scope.onClose) == 'function') {
					scope.onClose();
				}
			};
			
		},
		template: '<div class="ang-gri-gal-wrapper">' + 
				'<div class="ang-gri-gal-close" ng-click="closeGal();">' + 
				'	<i class="fa fa-remove"></i>' + 
				'</div>' + 
				'<div class="ang-gri-gal-next" ng-click="next();">' + 
				'	<i class="fa fa-chevron-right"></i>' + 
				'</div>' + 
				'<div class="ang-gri-gal-prev" ng-click="prev();">' + 
				'	<i class="fa fa-chevron-left"></i>' + 
				'</div>' +
				'<div class="ang-gri-gal-main-image">' + 
				'	<img ng-src="{{$root.IMAGES_BASE_URL}}/{{mainImage}}" ng-click="next();" />' +
				'</div>' +
			'</div>'
	};
}]);
