<?php if ($convo_messages):
	$messages = array();
	foreach ($convo_messages as $msg) {
		if ($msg->author && $msg->author['is_agent']) {
			$messages[] = array(
				'name' => $msg->author['display_name'],
				'message' => $msg['content'],
				'type' => 'agent'
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
DpChat.setInitialMessages(<?php echo json_encode($messages) ?>);
<?php endif ?>
DpChat.setVisitorCode('<?php echo $visitor['visitor_code'] ?>');