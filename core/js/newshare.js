/**
 * ownCloud
 *
 * @author Michael Gapczynski
 * @copyright 2013 Michael Gapczynski mtgap@owncloud.com
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
 */

OC.Share = {
	showShareDialog:function(itemType, itemTypePlural, itemSource, shareOwner, possiblePermissions) {
		$('<div id="shareDialogContainer"></div>')
			.load(OC.filePath('core', 'templates', 'share.php'))
			.dialog();
		angular.bootstrap(document, ['shareDialog']);
	}
};

var shareDialog = angular.module('shareDialog', ['restangular']).config(
	function(RestangularProvider) {
    	RestangularProvider.setBaseUrl('http://localhost/owncloud/core/index.php');
	    RestangularProvider.setResponseExtractor(function(response, operation, what, url) {
	    	if (typeof response.status !== 'undefined') {
	    		if (response.status === 'success') {
	    			return response.data;
	    		} else {
	    			throw new Error(
	    				response.data.message+', for the operation '+operation+' '+url
	    			);
	    		}
	    	} else {
	    		throw new Error('Unknown error for the operation '+operation+' '+url);
	    	}
	    	return []
	    });
	}
);

shareDialog.controller('shareDialogController', ['$scope', 'Restangular', 'itemType',
	'itemTypePlural', function($scope, Restangular, itemType, itemTypePlural) {
		// TODO Mock variables
		var shareOwner = 'MTGap';
		// var itemType = 'file';
		var itemTypePlural = 'files';
		var itemSource = 33431;
		var possiblePermissions = 31;
		var item = Restangular.all('shares').all(itemTypePlural).all(itemSource);
		item.getList({
			shareOwner: shareOwner,
		}).then(function(shares) {
			$scope.shares = shares;
		});

		$scope.share = function(shareWith) {
			var share = {
				shareTypeId: shareWith.shareTypeId,
				shareOwner: shareOwner,
				shareWith: shareWith.shareWith,
				itemType: itemType,
				itemSource: itemSource,
				permissions: 31,
			};
			item.post(share).then(function(share) {
				$scope.shares.push(share);
			});
			$scope.shareWith = '';
		}
		$scope.unshare = function(share) {
			share.remove().then(function() {
    			$scope.shares = _.without($scope.shares, share);
 			});
		}
		$scope.update = function(share) {
			share.put();
		};
		$scope.shareListFilter = function(share) {
			return share.shareTypeId !== 'link';
		}
		$scope.isCreatable = function(share) {
			return (share.permissions & 4) !== 0;
		}
		$scope.toggleCreatable = function(share) {
			share.permissions ^= 4;
			$scope.update(share);
		}
		$scope.isUpdatable = function(share) {
			return (share.permissions & 2) !== 0;
		}
		$scope.toggleUpdatable = function(share) {
			share.permissions ^= 2;
			$scope.update(share);
		}
		$scope.isDeletable = function(share) {
			return (share.permissions & 8) !== 0;
		}
		$scope.toggleDeletable = function(share) {
			share.permissions ^= 8;
			$scope.update(share);
		}
		$scope.isSharable = function(share) {
			return (share.permissions & 16) !== 0;
		}
		$scope.toggleSharable = function(share) {
			share.permissions ^= 16;
			$scope.update(share);
		}
		$scope.searchForPotentialShareWiths = function(pattern) {
			// TODO filter based on existing shares
			var shareWiths = Restangular.all('sharewiths').all(itemType).getList({
				shareOwner: shareOwner,
				pattern: pattern,
				limit: 10,
			});

			return shareWiths;
		}
		$scope.shareLink = function() {
			var share = {
				shareOwner: shareOwner,
				shareTypeId: 'link',
				itemType: itemType,
				itemSource: itemSource,
				permissions: 31,
			};
			item.post(share).then(function(share) {
				$scope.shares.push(share);
			});
		}
		$scope.isResharingAllowed = function() {
			return true;
		}
	}
]);

/**
 * Adapted from http://www.abequar.net/jquery-ui-datepicker-with-angularjs/
 * available under a http://creativecommons.org/licenses/by/3.0/ license
 */
shareDialog.directive('datepicker1', function() {
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attrs, ngModelCtrl) {
            $(function() {
                element.datepicker({
                    minDate: 0,
					showOtherMonths: true,
			      	selectOtherMonths: true,
                    onSelect: function(date) {
                        ngModelCtrl.$setViewValue(date);
                        scope.$apply();
                    }
                });
            });
        }
    }
});

shareDialog.directive('timepicker1', function() {
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attrs, ngModelCtrl) {
            $(function() {
                element.timepicker({
                   	showLeadingZero: false,
					showPeriod: true,
					showPeriodLabels: false,
					showMinutes: false,
                    onSelect: function(time) {
                        ngModelCtrl.$setViewValue(time);
                        scope.$apply();
                    }
                });
            });
        }
    }
});