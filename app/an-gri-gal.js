

webApp.directive('imageFitScreenSize', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			element.bind('load', function() {
				var $el = angular.element(element);
				
				// get image source
				var imgSrc = attrs.imageFitScreenSize && attrs.imageFitScreenSize.length > 0 ? attrs.imageFitScreenSize : $el.attr('src');
				if (!imgSrc) {
					return;
				}
				
				// get image dimensions (by name)
				var dims = /(\d+)x(\d+)/.exec(imgSrc);
				if (!dims || dims.length < 3) {
					return;
				}
				
				// git image ratio
				var imgX = dims[1];
				var imgY = dims[2];
				var imgRatio = parseInt(imgX) / parseInt(imgY);
				
				// get window ratio
				var winRatio = window.innerWidth / window.innerHeight;
				// set width/height
				if (imgRatio > winRatio) {
					$el.attr('width', '100%');
					$el.attr('height', 'auto');
				} else {
					$el.attr('width', 'auto');
					$el.attr('height', '100%');
				}
			});
		}
	};
});

webApp.directive('imageOnLoad', function() {
	return {
		restrict: 'A',
		scope: {
			imageOnLoad: '&?'
		},
		link: function(scope, element, attrs) {
			element.bind('load', function() {
				if (scope.imageOnLoad) {
					scope.imageOnLoad();
				}
			});
		}
	};
});

webApp.directive('anGriGal', ['$window', 'smallImagesService', function($window, smallImagesService) {
	return {
		replace: true,
		scope: {
			// list of images
			images: '=',
			// start with image number
			initialIndex: '=?',
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
			
			// images loading steps
			scope.getSmallImage = function(imgSrc) {
				var smallImagesSrc = smallImagesService.getSmall(imgSrc);
				return smallImagesSrc;
			};
			
			scope.getMediumImage = function(imgSrc) {
				var mediumImageSrc = smallImagesService.getMedium(imgSrc);
				return mediumImageSrc;
			};
			scope.mediumImageLoaded = {};
			scope.markMediumImageLoaded = function(imgSrc) {
				scope.$apply(function() {
					scope.mediumImageLoaded[imgSrc] = true;
				});
			};
			scope.isMediumImageLoaded = function(imgSrc) {
				return !!scope.mediumImageLoaded[imgSrc];
			}
			
			
			scope.bigImageLoaded = {};
			scope.markBigImageLoaded = function(imgSrc) {
				scope.$apply(function() {
					scope.bigImageLoaded[imgSrc] = true;
				});
			};
			scope.isBigImageLoaded = function(imgSrc) {
				return !!scope.bigImageLoaded[imgSrc];
			}
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
					// small image
				'<img ng-show="!isMediumImageLoaded(mainImage) && !isBigImageLoaded(mainImage)" ' + 
				'		image-fit-screen-size="{{mainImage}}" ' + 
				'		ng-src="{{getSmallImage(mainImage)}}" ' + 
				'		ng-click="next();" />' +
					// medium image
				'<img ng-show=" isMediumImageLoaded(mainImage) && !isBigImageLoaded(mainImage)" ' + 
				'		image-fit-screen-size ' + 
				'		src="{{getMediumImage($root.IMAGES_BASE_URL + \'/\' + mainImage)}}" ' + 
				'		image-on-load="markMediumImageLoaded(mainImage)" ' + 
				'		ng-click="next();" />' +
					// big image
				'<img ng-show=" isBigImageLoaded(mainImage)" ' + 
				'		image-fit-screen-size ' + 
				'		src="{{$root.IMAGES_BASE_URL + \'/\' + mainImage}}" ' + 
				'		image-on-load="markBigImageLoaded(mainImage)" ' + 
				'		ng-click="next();" />' +
				'</div>' +
			'</div>'
	};
}]);
