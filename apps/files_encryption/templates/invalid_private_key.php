<ul>
	<li class='error'>
		<?php $location = \OC_Helper::linkToRoute( "settings_personal" ).'#changePKPasswd' ?>

		<?php p($l->t('Your private key is not valid  or wasn\'t initialized correctly! Maybe the your password was changed from outside. If your log-in password didn\'t change than first try to log out and log in back in order to initialize your encryption keys')); ?>
		<br/>
		<?php p($l->t('If this doesn\'t help you can unlock your private key in your ')); ?> <a href="<?php echo $location?>"><?php p($l->t('personal settings')); ?>.</a>
		<br/>
	</li>
</ul>
