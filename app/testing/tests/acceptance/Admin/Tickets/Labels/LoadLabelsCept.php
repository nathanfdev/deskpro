<?php
$scenario->group('StateTests');
$scenario->group('ProductTests');

$I = new WebGuy($scenario);
$I->wantToTest("Load labels");
$I->openAdminInterface();
$I->amOnAdminPage('/tickets/labels');
$I->see('Labels');
