<html>
<head>
<script type="text/javascript">
	if (window.parent != window) {
		window.parent.DpStatus.update(<?php echo json_encode($results) ?>);
		window.parent.DpStatus.doneBatch(<?php echo $batch ?>);
	}
</script>
</head>
<body>
</body>
</html>
