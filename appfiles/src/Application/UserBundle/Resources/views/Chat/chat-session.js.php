<?php if ($convo_messages):
	$messages = array();
	foreach ($convo_messages as $msg) {
		if ($msg->author && $msg->author['is_agent']) {
			$messages[] = array(
				'name' => $msg->author['display_name'],
				'message' => $msg['content'],
				'type' => 'agent'
			);
		} elseif ($msg['is_sys']) {
			$messages[] = array(
				'name' => '*',
				'message' => $msg['content'],
				'type' => 'sys'
			);
		} else {
			$messages[] = array(
				'name' => 'You',
				'message' => $msg['content'],
				'type' => 'user'
			);
		}
	}
?>
DpChat.setInitialMessages(<?php echo json_encode(array_reverse($messages)) ?>);
<?php endif ?>
<?php if ($department_sel): ?>
DpChat.setDepartmentSelect(<?php echo json_encode($department_sel) ?>);
<?php endif ?>
DpChat.setSessionCode('<?php echo $session['session_code'] ?>');