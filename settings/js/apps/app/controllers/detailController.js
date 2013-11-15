/*
 * ownCloud - Core
 *
 * @author Raghu Nayyar
 * @copyright 2013 Raghu Nayyar <raghu.nayyar.007@gmail.com>
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

appSettings.controller('detailController', ['$scope', '$routeParams', 'AppListService', 'AppActionService',
	function($scope,$routeParams,AppListService,AppActionService){

		var appId = $routeParams.appId;
		var val;
		$scope.loading = true;
		$scope.allapps = AppListService.listAllApps().get(function(apps){
			$scope.allapps = apps.data;
			for (var i = 0; i <= $scope.allapps.length; i++) {
				if (appId == $scope.allapps[i].id) {
					val = i;
					break;
				}
			}
			$scope.appname = $scope.allapps[val].name;
			$scope.preview = $scope.allapps[val].preview;
			$scope.licence = $scope.allapps[val].licence;
			$scope.authorname = $scope.allapps[val].author;
			$scope.desc = $scope.allapps[val].description;
			$scope.vers = $scope.allapps[val].version;
			$scope.loading = false;
		});
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