<?php
$scenario->group('StateTests');

$I = new WebGuy($scenario);
$I->wantToTest("Session active");
$I->enableDatabaseSet('FreshDb');
$I->startAgentSession('admin@example.com');
$I->amOnPage('/admin');
$I->see('DeskPRO News', 'h3');