<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;

final class LoginCest
{
    public function loginPageIsReachable(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->see('Northline');
        $I->see('Sign in');
    }
}
