<?php
/**
 * ownCloud - App Settings
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
?>

<div class="appinfo">
	<loading></loading>
	<div class="infocontent" ng-class="{hidden : loading }">
		<strong>
			<span class="name">{{ appname }}</span>
		</strong>
		<span class="version">{{ vers }}</span>
		<div class="preview">
			<img src="{{ preview }}" alt="<?php p($l->t('No Preview Avaialable')); ?>" />		
		</div>
		<p class="description">{{ desc }}</p>
		<p class="appslink">
			<a href="#" target="_blank">
				<?php p($l->t('See application page at apps.owncloud.com'));?>
			</a>
		</p>
		<p class="licence">
			<span>
				<strong>{{ licence }}</strong>
			</span>
			<?php
				print_unescaped($l->t('-licensed by'));
			?>
			<span class="author">
				<strong>{{ authorname }}</strong>
			</span>
		</p>

		<!-- TODO : Put a check for already enabled app. -->
		<button class="enable" ng-click="enable(appId)">
			<?php p($l->t('Enable')); ?>
		</button>
		
		<!-- TODO : Put a check for already enabled app. -->
		<button class="disable" ng-click="disable(appId)">
			<?php p($l->t('Disable')); ?>
		</button>
		
		<!-- TODO :  Put a chech for already updated apps. -->
		<button class="update" ng-click="update(appId)">
			<?php p($l->t('Update')); ?>
		</button>
	</div>
</div>