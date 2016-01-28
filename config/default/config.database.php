<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

$DB_CONFIG = [];

// The database server. Three formats are accepted:
// - Host/IP: localhost, db.myhost.com, 192.168.1.1
// - Host/IP with port: db.myhost.com:10086
// - Socket: unix_socket:/var/run/mysqld/mysqld.sock
//
// Note: If you are using Windows and your MySQL
// server is on the same machine, it it important
// to specify 127.0.0.1 (not localhost) for performance.
$DB_CONFIG['host'] = 'localhost';

// The database username and password
$DB_CONFIG['user']     = 'root';
$DB_CONFIG['password'] = 'root';

// The database name
$DB_CONFIG['dbname'] = 'deskpro';
