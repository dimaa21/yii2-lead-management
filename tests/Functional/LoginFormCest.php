<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\tests\Support\FunctionalTester;

final class LoginFormCest
{
    public function openLoginPage(FunctionalTester $I): void
    {
        $I->amOnRoute('site/login');
        $I->see('Sign in', 'h1');
        $I->see('Create an account');
    }

    public function loginWithEmptyCredentials(FunctionalTester $I): void
    {
        $I->amOnRoute('site/login');
        $I->submitForm('#login-form', []);
        $I->see('Email cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function guestsCannotOpenTheDashboard(FunctionalTester $I): void
    {
        $I->amOnRoute('site/index');
        $I->see('Sign in', 'h1');
    }
}
