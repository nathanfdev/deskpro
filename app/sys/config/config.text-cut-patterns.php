<?php return array(
	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	# This file should not be edited directly. If you want
	# to add custom patterns, create a new file named
	# config.text-cut-patterns.php in the same directory
	# as your config.php file.
	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	'outlook_1'                          => 'lang:#\s%From%: (.*?)\s+%Sent%: (.*?)\s+%To%: (.*?)\s+%Subject%: (.*?)\s+#',

	'sparrow_1'                          => '#On (.*?), ([0-9]+) (.*?) ([0-9]{4}) at (.*?), (.*?) wrote:\s+#',

	'thunderbird_1'                      => '#On ([0-9]+)/([0-9]+)/([0-9]+) ([0-9]+):([0-9a-zA-Z]+), (.*?) wrote:#',
);