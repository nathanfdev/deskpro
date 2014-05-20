<?php
$scenario->group('StateTests');

$I = new WebGuy($scenario);
$I->wantToTest("See the admin dashboard");
$I->enableDatabaseSet('FreshDb');
$I->openAdminInterface('admin@example.com');
$I->see('DeskPRO Updates', 'h3');