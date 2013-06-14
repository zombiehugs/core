<?php

/**
 * Copyright (c) 2013, Raghu Nayyar <raghu.nayyar.007@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

// Takes angular from 3rdparty angular branch.

\OCP\Util::addScript('3rdParty/js/angular','angular');
// \OCP\Util::addScript( 'settings', 'users' );
// \OCP\Util::addScript( 'settings', 'users' );
\OCP\Util::addScript( 'settings', 'users/controllers/maincontroller' );
\OCP\Util::addScript( 'settings', 'users/directives/slidetoggle' );

\OCP\Util::addScript( 'core', 'multiselect' );
\OCP\Util::addScript( 'core', 'singleselect' );
\OCP\Util::addScript('core', 'jquery.inview');
\OCP\Util::addStyle( 'settings', 'settings' );
\OCP\Util::addStyle('core', 'styles' );

?>

<div id="user-settings" ng-app>
	<div id="app-navigation" ng-controller="" style="position:absolute;"> <!--Remove Inline CSS-->
		<ul>
			<?php print_unescaped($this->inc('users/add-group')); ?>
			<?php print_unescaped($this->inc('users/user-sidebar')); ?>
		</ul>
	</div>
	<div id="user-content">
		<div id="hascontrols" ng-controller="">
			<?php print_unescaped($this->inc('users/add-user')); ?>
		</div>
		<div id="user-table" ng-controller="">
			<?php print_unescaped($this->inc('users/user-list')); ?>
		</div>
	</div>
</div>