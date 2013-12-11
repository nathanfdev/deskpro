<?php
$scenario->group('StateTests');
$scenario->group('ProductTests');

$I = new WebGuy($scenario);
$I->wantToTest("Load departments");
$I->openAdminInterface();
$I->amOnAdminPage('/tickets/labels');
$I->see('Labels');