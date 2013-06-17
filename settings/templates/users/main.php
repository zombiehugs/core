<?php

/**
 * Copyright (c) 2013, Raghu Nayyar <raghu.nayyar.007@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

// Takes angular from 3rdparty angular branch.

\OCP\Util::addScript('settings','vendor/angular/angular');
// \OCP\Util::addScript( 'settings', 'users' );
// \OCP\Util::addScript( 'settings', 'users' );
\OCP\Util::addScript('settings','users/controllers/addgroupController');
\OCP\Util::addScript( 'core', 'multiselect' );
\OCP\Util::addScript( 'core', 'singleselect' );
\OCP\Util::addScript('core', 'jquery.inview');
\OCP\Util::addStyle( 'settings', 'settings' );
\OCP\Util::addStyle('core', 'styles' );

?>

<div id="user-settings" ng-app="users">
	
	<div id="app-navigation" style="position:absolute;"> <!--Remove Inline CSS-->
		<ul>
			<?php print_unescaped($this->inc('users/add-group')); ?>
			<?php print_unescaped($this->inc('users/user-sidebar')); ?>
		</ul>
	</div>
	<div id="user-content" style="margin-left:250px;"> <!--Remove Inline CSS-->
		<div id="hascontrols">
			<?php print_unescaped($this->inc('users/add-user')); ?>
		</div>
		<div id="user-table">
			<?php print_unescaped($this->inc('users/user-list')); ?>
		</div>
	</div>
</div>