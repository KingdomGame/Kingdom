<?php

$I = new AcceptanceTester($scenario);

$I->amLoggedInAs('test');

$I->amOnPage('/character/1');
$I->see('Дата регистрации:');
$I->see('Последний раз был:');
$I->see('Одежда:');

$I->amOnPage('/character/0');
$I->see('Персонаж не найден!');
