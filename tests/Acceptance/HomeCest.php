<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;

final class HomeCest
{
    public function guestsAreSentToSignIn(AcceptanceTester $I): void
    {
        $I->amOnPage('/dashboard');
        $I->see('Sign in');
    }
}
