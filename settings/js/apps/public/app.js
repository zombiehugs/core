var appSettings = angular.module('appSettings', ['ngResource']).
config(['$httpProvider', '$routeProvider', '$windowProvider', '$provide',
	function($httpProvider,$routeProvider, $windowProvider, $provide) {
		
		// Always send the CSRF token by default
		$httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;

		var $window = $windowProvider.$get();
		var url = $window.location.href;
		var baseUrl = url.split('index.php')[0] + 'index.php/settings';

		$provide.value('Config', {
			baseUrl: baseUrl
		});

		$routeProvider.when('/:appId', {
			template : 'detail.html',
			controller : 'detailController'
		}).otherwise({
			redirectTo: '/'
		});
	}
]);
appSettings.controller('applistController', ['$scope', 'AppListService',
	function($scope, AppListService){
		/* Displays the list of files in the Left Sidebar */
		$scope.allapps = AppListService.listAllApps().get();
	}
]);
appSettings.controller('detailController', ['$scope', 'AppListService', 'AppActionService',
	function($scope,AppListService,AppActionService){
		
		$scope.enable = function (appId) {
			AppActionService.enableApp(appId);
		};

		$scope.disable = function (appId) {
			AppActionService.disableApp(appId);
		};

		$scope.update = function (appId) {
			AppActionService.updateApp(appId);
		};
	}
]);
appSettings.directive('loading',
	[ function() {
		return {
			restrict: 'E',
			replace: true,
			template:"<div class='loading'></div>",
			link: function($scope, element, attr) {
				$scope.$watch('loading', function(val) {
					if (val) {
						$(element).show();
					}
					else {
						$(element).hide();
					}
				});
			}		
		};
	}]
);
appSettings.factory('AppActionService', ['$resource',
	function ($resource) {
		return {
			enableApp : function(appId) {
				return ($resource(OC.filePath('settings', 'ajax', 'enableapp.php')).post(
					{ appid : appId }
				));
			},
			disableApp : function(appId) {
				return ($resource(OC.filePath('settings', 'ajax', 'disableapp.php')).post(
					{ appid : appId }
				));
			},
			updateApp : function(appId) {
				return ($resource(OC.filePath('settings', 'ajax', 'updateApp.php')).post(
					{ appid : appId }
				));
			}
		};
	}
]);
appSettings.factory('AppListService', ['$resource',
	function ($resource) {
		return {
			listAllApps : function() {
				return ($resource(OC.filePath('settings', 'ajax', 'applist.php')));
			}
		};
	}
]);