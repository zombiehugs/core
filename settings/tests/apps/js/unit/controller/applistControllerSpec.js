/*
 * ownCloud - App Settings
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

describe('applistController', function() {
	var controller,scope,service,http,routeParams,location;

	beforeEach(module('appSettings'));

	beforeEach(inject(function ($controller, $routeScope, $httpBackend, AppListService) {
		http = $httpBackend;
		scope = $routeScope.$new();
		routeParams = {
			appId: any_valid_app
		};
		service = AppListService;
		location = {
			path: jasmine.createSpy('path')
		};
	}));

	it ('should load App Details and attach them to scope', function() {
		var apps = [
			{id: any_valid_app}
		];
		http.expectGET('/').respond(200, apps);

		controller = controller('applistController', {
			$routeParams: routeParams,
			$scope : scope,
			$location : location,
			AppListService: service
		});

		http.flush(1);
		expect(scope.route).toBe(routeParams);
	});

	afterEach(function() {
		http.verifyNoOutstandingExpectation();
		http.verifyNoOutstandingRequest();
	});
});