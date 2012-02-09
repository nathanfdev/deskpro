<?php if (!empty($convo_messages) && $convo_messages): ?>
	<?php
		$messages = array();
		foreach ($convo_messages as $msg) {
			$messages[] = $msg->getInfo();
		}
	?>
	DpChat.setInitialMessages(<?php echo json_encode(array_reverse($messages)) ?>);
<?php endif ?>

<?php if ($conversation->agent): ?>DpChat.chatAssigned(<?php echo $conversation->agent->id ?>)<?php endif ?>

<?php if ($session->visitor): ?>
	<?php if ($session->visitor->name): ?>DpChat.setFormVar('name', <?php echo json_encode($session->visitor->name) ?>);<?php endif ?>
	<?php if ($session->visitor->email): ?>DpChat.setFormVar('email', <?php echo json_encode($session->visitor->email) ?>);<?php endif ?>
<?php endif ?>

<?php if (!empty($department_sel) && $department_sel): ?>
	DpChat.setDepartmentSelect(<?php echo json_encode($department_sel) ?>);
<?php endif ?>

<?php if (!empty($proactive) && $proactive): ?>
	DpChat.setSessionCode('<?php echo $session['session_code'] ?>', true);
<?php else: ?>
	DpChat.setSessionCode('<?php echo $session['session_code'] ?>');
<?php endif ?>
