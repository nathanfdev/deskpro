<?php
$scenario->group('StateTests');
$scenario->group('ProductTests');

$label_id1 = "DPTEST_" . mt_rand(10000, 99999);
$label_id2 = "DPTEST_ALT_" . mt_rand(10000, 99999);

$I = new WebGuy($scenario);
$I->wantToTest("Load labels");
$I->openAdminInterface();
$I->amOnAdminPage('/tickets/labels/create');
$I->fillField('label', $label_id1);
$I->click('Save');
//$I->reloadPage();
$I->waitForAdminLoad();
$I->see($label_id1);
