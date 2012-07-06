<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
?>
<td id="<?php echo $_['new'] ? 'new' : 'edit' ?>calendar_dialog" title="<?php echo $_['new'] ? $l->t("New calendar") : $l->t("Edit calendar"); ?>" colspan="6">
<table>
<tr>
	<td></td>
	<td>
		<div id="calendartype" style="text-align:center;">
		<input type="radio" id="calendartype_caldav" name="calendartype" /><label for="calendartype_caldav">CalDAV</label>
		<input type="radio" id="calendartype_oc" name="calendartype" /><label for="calendartype_oc">ownCloud Calendar</label>
		<input type="radio" id="calendartype_webcal" name="calendartype" /><label for="calendartype_webcal">Webcal</label>
		</div>
	</td>
</tr>
<tr>
	<td>
		<input id="colorpicker" type="hidden">
		<?php if (!$_['new']): ?><input id="edit_active_<?php echo $_['calendar']['id'] ?>" type="checkbox"<?php echo $_['calendar']['active'] ? ' checked="checked"' : '' ?>>
		<label for="edit_active_<?php echo $_['calendar']['id'] ?>"><?php echo $l->t('Active') ?></label><?php endif; ?>
	</td>
	<td>
		<input id="displayname_<?php echo $_['calendar']['id'] ?>" type="text" placeholder="<?php echo $l->t('Displayname') ?>" style="width:450px;float:right;" value="<?php echo $_['calendar']['displayname'] ?>">
	</td>
</tr>
<tr>
	<td></td>
	<td>
		<div id="calendar_import_defaultcolors" style="text-align: center;">
		<?php
		foreach($_['defaultcolors'] as $color){
			echo '<span class="calendar-colorpicker-color" rel="' . $color . '" style="background-color: ' . $color .  ';"></span>';
		}
		?>
		</div>
	</td>
</tr>
</table>
<input style="float: left;" type="button" onclick="Calendar.UI.Calendar.submit(this, <?php echo $_['new'] ? "'new'" : $_['calendar']['id'] ?>);" value="<?php echo $_['new'] ? $l->t("Save") : $l->t("Submit"); ?>">
<input style="float: left;" type="button" onclick="Calendar.UI.Calendar.cancel(this, <?php echo $_['new'] ? "'new'" : $_['calendar']['id'] ?>);" value="<?php echo $l->t("Cancel"); ?>">
</td>
