<?php 
/**
 * Copyright (c) 2013, Raghu Nayyar <raghu.nayyar.007@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
?>
<!-- TODO: Implement Sorting-->
<li class="user-groups" ng-controller="grouplistController" ng-repeat= "group in groupname">
	<a href="#">{{group.groupname}}</a>
</li>