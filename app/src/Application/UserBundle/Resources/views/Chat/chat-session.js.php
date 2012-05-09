<?php if ($conversation): ?>
	DpChatWidget.doResume = true;
	<?php if ($conversation->is_window): ?>
		DpChatWidget.isWindowChat = true;
	<?php endif ?>
<?php endif ?>
DpChatWidget.initWidget('<?php echo $session_id ?>');
