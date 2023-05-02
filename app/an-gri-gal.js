

webApp.directive('imageFitScreenSize', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			var $el = angular.element(element);
			
			// get image source
			var imgSrc = attrs.imageFitScreenSize && attrs.imageFitScreenSize.length > 0 ? attrs.imageFitScreenSize : $el.attr('src');
			if (!imgSrc) {
				console.error('Cannot get resource URL from element', element);
				return;
			}

			// check if video
			const isVideoFile = $el[0].tagName == 'VIDEO';
			const eventName = isVideoFile ? 'loadedmetadata' : 'load';

			element.on(eventName, function(evt) {
				// get image dimensions (by name)
				var dims = isVideoFile ?
					[null, this.videoWidth, this.videoHeight] :
					/(\d+)x(\d+)/.exec(imgSrc);
				if (!dims || dims.length < 3) {
					return;
				}

				// git image ratio
				var imgX = dims[1];
				var imgY = dims[2];
				var imgRatio = 1.0 * parseInt(imgX) / parseInt(imgY);
				
				// get window ratio
				var winRatio = 1.0 * window.innerWidth / window.innerHeight;

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
			const eventName = element && element[0].tagName == "IMG" ? "load" : "loadedmetadata"
			element.on(eventName, function() {
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

			scope.isVideoFile = function(imgSrc) {
				let parts = imgSrc.split('.');
				if (parts && parts.length > 0) {
					const ext = parts[parts.length - 1];
					if (['mp4', 'wmv', 'mpeg', 'mpg'].includes(ext)) {
						return true;
					}
				}
				
				return false;
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
				'<img ng-if="!isVideoFile(mainImage)" ng-show="!isMediumImageLoaded(mainImage) && !isBigImageLoaded(mainImage)" ' + 
				'		image-fit-screen-size="{{getSmallImage(mainImage)}}" ' + 
				'		ng-src="{{getSmallImage(mainImage)}}" ' + 
				'		ng-click="next();" />' +
					// medium image
				'<img ng-if="!isVideoFile(mainImage)" ng-show=" isMediumImageLoaded(mainImage) && !isBigImageLoaded(mainImage)" ' + 
				'		image-fit-screen-size="{{getMediumImage($root.IMAGES_BASE_URL + \'/\' + mainImage)}}" ' + 
				'		ng-src="{{getMediumImage($root.IMAGES_BASE_URL + \'/\' + mainImage)}}" ' + 
				'		image-on-load="markMediumImageLoaded(mainImage)" ' + 
				'		ng-click="next();" />' +
					// big image
				'<img ng-if="!isVideoFile(mainImage)" ng-show=" isBigImageLoaded(mainImage)" ' + 
				'		image-fit-screen-size="{{mainImage}}" ' + 
				'		ng-src="{{$root.IMAGES_BASE_URL + \'/\' + mainImage}}" ' + 
				'		image-on-load="markBigImageLoaded(mainImage)" ' + 
				'		ng-click="next();" />' +
				'<video controls autoplay muted ' +
				'		ng-if=" isVideoFile(mainImage)" ng-show="isBigImageLoaded(mainImage)" ' + 
				'		image-fit-screen-size="{{mainImage}}" ' + 
				'		ng-src="{{$root.IMAGES_BASE_URL + \'/\' + mainImage}}" ' + 
				'		image-on-load="markBigImageLoaded(mainImage)" ' + 
				'		ng-click="next();" ></video>' +
				'</div>' +
			'</div>'
	};
}]);
