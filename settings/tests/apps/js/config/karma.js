/*
 * ownCloud - App Settings
 *
 * @author Raghu Nayyar
 * @author Bernhard Posselt
 * @copyright 2013 Raghu Nayyar <raghu.nayyar.007@gmail.com>
 * @copyright 2013 Bernhard Posselt <dev@bernhard-posselt.com> 
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

module.exports = function(config) {
	config.set({
		frameworks: ['jasmine'],
		basePath: '../../../../',
		files: [
			'../core/js/jquery-1.10.0.min.js',
			'js/vendor/angular/angular.js',
			'js/vendor/angular-resource/angular-resource.js',
			'js/vendor/angular-mocks/angular-mocks.js',
			'js/vendor/angular-route/angular-route.js',
			'tests/apps/js/stubs/stubs.js',
			'js/apps/config/config.js',
			'js/apps/app/**/*.js',
			'tests/apps/js/unit/**/*.js'
		],

		exclude: [],
		port: 8000,
		reporters: ['progress'],
		colors: true,
		autoWatch: true,
		browsers: ['Chrome'],
		captureTimeout: 5000,
		singleRun: false
	});
}