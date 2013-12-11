<?php
$scenario->group('StateTests');

$I = new WebGuy($scenario);
$I->wantToTest("Failed login credentials");
$I->resetCookie('dpsid');
$I->amOnPage('/login');
$I->fillField('#user_type_exist_form input[name="email"]', 'admin@example.com');
$I->fillField('#user_type_exist_form input[name="password"]', 'wrong_password');
$I->click('#user_type_exist_form [type="submit"]');
$I->see("password you entered is invalid");