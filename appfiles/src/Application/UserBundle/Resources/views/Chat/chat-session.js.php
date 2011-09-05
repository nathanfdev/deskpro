<?php if ($convo_messages):
	$messages = array();
	foreach ($convo_messages as $msg) {
		if ($msg->author && $msg->author['is_agent']) {
			$m = array(
				'name' => $msg->author['display_name'],
				'type' => 'agent'
			);
		} elseif ($msg['is_sys']) {
			$m = array(
				'name' => '*',
				'type' => 'sys'
			);
		} else {
			$m = array(
				'name' => 'You',
				'type' => 'user'
			);
		}

		if ($msg->is_html) {
			$m['message_html'] = $msg->content;
		} else {
			$m['message'] = $msg->content;
		}

		$messages[] = $m;
	}
?>
DpChat.setInitialMessages(<?php echo json_encode(array_reverse($messages)) ?>);
<?php endif ?>
<?php if ($department_sel): ?>
DpChat.setDepartmentSelect(<?php echo json_encode($department_sel) ?>);
<?php endif ?>
<?php if ($proactive): ?>
	DpChat.setSessionCode('<?php echo $session['session_code'] ?>', true);
<?php else: ?>
	DpChat.setSessionCode('<?php echo $session['session_code'] ?>');
<?php endif ?>
