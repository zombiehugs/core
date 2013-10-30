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
	
	}
]);
appSettings.controller('applistController', ['$scope', 'AppListService',
	function($scope,AppListService){
		// Returns the List of All Apps.
		$scope.allapps = AppListService.listAllapps;
		console.log($scope.allapps);
	}
]);
appSettings.controller('detailController', ['$scope',
	function($scope){
		
	}
]);
appSettings.factory('AppListService', ['$q', '$resource',
	function($q,$resource) {
		return {
			listAllapps : function() {
				var deferred = $q.defer();
				var AppList = $resource(OC.filePath('settings', 'ajax', 'applist.php'));
				Applist.get(function(response) {
					deferred.resolve(response);
				});
				return deferred.promise;
			}
		};
	}
]);