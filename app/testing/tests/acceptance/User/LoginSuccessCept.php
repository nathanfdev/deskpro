<?php
$scenario->group('StateTests');

$I = new WebGuy($scenario);
$I->wantToTest("Valid login credentials");
$I->enableDatabaseSet('FreshDb');
$I->resetCookie('dpsid');
$I->amOnPage('/login');
$I->fillField('#user_type_exist_form input[name="email"]', 'admin@example.com');
$I->fillField('#user_type_exist_form input[name="password"]', 'pass');
$I->click('#user_type_exist_form [type="submit"]');
$I->see("Welcome back, Admin");