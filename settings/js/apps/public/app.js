var appSettings = angular.module('appSettings', ['restangular']).
config(['$httpProvider', '$routeProvider', '$windowProvider', '$provide',
	function($httpProvider,$routeProvider, $windowProvider, $provide) {
		
		// Always send the CSRF token by default
		httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;

		var $window = $windowProvider.$get();
		var url = $window.location.href;
		var baseUrl = url.split('index.php')[0] + 'index.php/settings';

		$provide.value('Config', {
			baseUrl: baseUrl
		});
	
	}
]);