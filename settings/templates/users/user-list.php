<?php 
/**
 * Copyright (c) 2013, Raghu Nayyar <raghu.nayyar.007@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
?>
<table>
	<thead>
		<tr>
			<th class="table-head login-name"><?php p($l->t('Login Name')); ?></th>
			<th class="table-head display-name"><?php p($l->t('Display Name')); ?></th>
			<th class="table-head user-pass"><?php p($l->t('Password')); ?></th>
			<th class="table-head groups"><?php p($l->t('Groups')); ?></th>
			<th class="table-head local-storage"><?php p($l->t('Local Storage')); ?></th>
			<th class="table-head delete-user"><!--Place for Delete Button--></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="login-name"></td>
			<td class="display-name"></td>
			<td class="user-pass"></td>
			<td class="local-storage"></td>
			<td class="delete-user"></td>
		</tr>
	</tbody>
</table>
