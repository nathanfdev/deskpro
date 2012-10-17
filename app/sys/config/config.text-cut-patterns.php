<?php return array(
	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	# This file should not be edited directly. If you want
	# to add custom patterns, create a new file named
	# config.text-cut-patterns.php in the same directory
	# as your config.php file.
	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	'outlook_1'                          => 'lang:#(\-\-\-\-\-\s*Original Message\s*\-\-\-\-\-)?\s%From%: (.*?)\s+%Sent%: (.*?)\s+%To%: (.*?)\s+(%CC%: (.*?)\s+)?%Subject%: (.*?)\s+#i',

	'sparrow_1'                          => '#On (.*?), ([0-9]+) (.*?) ([0-9]{4}) at (.*?), (.*?) wrote:\s+#',

	'thunderbird_1'                      => '#On ([0-9]+)/([0-9]+)/([0-9]+) ([0-9]+):([0-9a-zA-Z]+), (.*?) wrote:#',
	'thunderbird_2'                      => '#\-\-\- Original Message Follows \-\-\-\s+Sender:(.*?)\s+Date:(.*?)\s+#',

	'applemail_1'                        => '#On (.*?), ([0-9]+), at (.*?), (.*?) wrote:\s+#',

	'zimbra_1'                           => '#\-\-\-\-\-\s+Original Message\s+\-\-\-\-\-\sFrom: (.*?)\sTo: (.*?)#',
);