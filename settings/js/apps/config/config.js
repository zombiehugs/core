/*
 * ownCloud - Core
 *
 * @author Raghu Nayyar
 * @author Bernhard Posselt
 * @copyright 2013 Raghu Nayyar <raghu.nayyar.007@gmail.com>
 * @copyright 2013 Bernhard Posselt <nukeawhale@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


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
			controller : 'detailcontroller',
			resolve : {
				app : ['$route', '$q', function ($route, $q) {
					var deferred = $q.defer();
					var appId = $route.current.params.appId;

					return deferred.promise;
				}]
			}
		}).otherwise({
			redirectTo: '/'
		});
	}
]);