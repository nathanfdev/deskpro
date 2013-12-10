<?php
$scenario->group('StateTests');

$I = new WebGuy($scenario);
$I->wantToTest("Session active");
$I->enableDatabaseSet('FreshDb');
$I->amOnPage('/agent');
$I->fillField('#email', 'admin@example.com');
$I->fillField('#password', 'password');
$I->click('#normal_view [type="submit"]');
$I->see('Loading Interface');