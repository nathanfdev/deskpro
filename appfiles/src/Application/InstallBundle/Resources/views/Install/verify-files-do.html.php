<html>
<head>
<script type="text/javascript">
	if (window.parent != window) {
		var status = {
			update: function(info) {
				window.parent.status.update(info);
			},
			doneBatch: function(batch) {
				window.parent.status.doneBatch(batch);
			}
		};

		status.update(<?php echo json_encode($results) ?>);
		status.doneBatch(<?php echo $batch ?>);
	}
</script>
</head>
<body>
</body>
</html>
