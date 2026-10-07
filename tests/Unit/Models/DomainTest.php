<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\components\LeadStatus;
use app\models\Lead;
use app\models\LeadSearch;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\User;

final class DomainTest extends \Codeception\Test\Unit
{
    public function testPasswordHashIsNotPartOfThePublicFields(): void
    {
        $fields = (new User())->fields();

        $this->assertNotContains('password_hash', $fields);
        $this->assertContains('email', $fields);
    }

    public function testManagerCannotMassAssignTheOwner(): void
    {
        $lead = new Lead();
        $lead->setScenario(Lead::SCENARIO_MANAGER);

        $this->assertNotContains('manager_id', $lead->safeAttributes());
        $this->assertContains('name', $lead->safeAttributes());
    }

    public function testAdminCanAssignTheOwner(): void
    {
        $lead = new Lead();
        $lead->setScenario(Lead::SCENARIO_ADMIN);

        $this->assertContains('manager_id', $lead->safeAttributes());
    }

    public function testStatusesMatchThePipeline(): void
    {
        $this->assertSame(
            ['new', 'in_progress', 'converted', 'rejected'],
            LeadStatus::values(),
        );
    }

    public function testSortWhitelistRejectsUnknownColumns(): void
    {
        $this->assertTrue(LeadSearch::isSortValid('-created_at'));
        $this->assertTrue(LeadSearch::isSortValid('name,-email'));
        $this->assertFalse(LeadSearch::isSortValid('password_hash'));
        $this->assertFalse(LeadSearch::isSortValid('name;drop'));
    }

    public function testBlankLoginDoesNotAskTheDatabase(): void
    {
        $form = new LoginForm();

        $this->assertFalse($form->login());
        $this->assertArrayHasKey('email', $form->getErrors());
        $this->assertArrayHasKey('password', $form->getErrors());
    }

    public function testSignupRejectsAShortPasswordBeforeItTouchesStorage(): void
    {
        $form = new SignupForm([
            'name' => 'A',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_repeat' => 'different',
        ]);

        $this->assertFalse($form->validate());
        $this->assertArrayHasKey('name', $form->getErrors());
        $this->assertArrayHasKey('email', $form->getErrors());
        $this->assertArrayHasKey('password', $form->getErrors());
        $this->assertArrayHasKey('password_repeat', $form->getErrors());
    }
}
