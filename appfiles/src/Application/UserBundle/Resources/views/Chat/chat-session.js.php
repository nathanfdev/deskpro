<?php if ($convo_messages):
	$messages = array();
	foreach ($convo_messages as $msg) {
		$messages[] = $msg->getInfo();
	}
?>
DpChat.setInitialMessages(<?php echo json_encode(array_reverse($messages)) ?>);
<?php endif ?>
<?php if ($session->visitor->name): ?>DpChat.setFormVar('name', <?php echo json_encode($session->visitor->name) ?>);<?php endif ?>
<?php if ($session->visitor->email): ?>DpChat.setFormVar('email', <?php echo json_encode($session->visitor->email) ?>);<?php endif ?>
<?php if ($department_sel): ?>
DpChat.setDepartmentSelect(<?php echo json_encode($department_sel) ?>);
<?php endif ?>
<?php if ($proactive): ?>
	DpChat.setSessionCode('<?php echo $session['session_code'] ?>', true);
<?php else: ?>
	DpChat.setSessionCode('<?php echo $session['session_code'] ?>');
<?php endif ?>
