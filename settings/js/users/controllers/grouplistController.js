/**
 * Copyright (c) 2013, Raghu Nayyar <raghu.nayyar.007@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
var users = angular.module('users-manage', []);
users_manage.controller('grouplistController',
	function($scope, $http) {
		$http.get(OC.filePath('settings','ajax','userlist.php')).success(function(data, status, headers, config) {
			$scope.users = data;
		});
	});