<?php

declare(strict_types=1);

namespace app\commands;

use app\components\UserRole;
use app\models\Lead;
use app\models\User;
use app\services\RbacService;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Loads demo staff and leads. Safe to run more than once.
 */
class SeedController extends Controller
{
    public function actionIndex(): int
    {
        $rbac = new RbacService();
        $rbac->ensureRoles();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $admin = $this->ensureUser('Olena Kovalenko', 'admin@northline.test', 'Admin12345', UserRole::Admin);
            $mark = $this->ensureUser('Mark Feldman', 'mark@northline.test', 'Manager12345', UserRole::Manager);
            $sofia = $this->ensureUser('Sofia Melnyk', 'sofia@northline.test', 'Manager12345', UserRole::Manager);

            $rbac->syncAssignment($admin);
            $rbac->syncAssignment($mark);
            $rbac->syncAssignment($sofia);

            if (!Lead::find()->exists()) {
                $this->seedLeads($admin, $mark, $sofia);
                $this->stdout("Demo leads created.\n");
            } else {
                $this->stdout("Leads already exist. Lead seed skipped.\n");
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->stderr($e->getMessage() . "\n");

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Demo users are ready.\n");

        return ExitCode::OK;
    }

    private function ensureUser(string $name, string $email, string $password, UserRole $role): User
    {
        $user = User::findByEmail($email);
        if ($user !== null) {
            return $user;
        }

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->role = $role->value;
        $user->setPassword($password);
        if (!$user->save()) {
            throw new \RuntimeException('Could not create ' . $email . ': ' . json_encode($user->getErrors()));
        }

        $this->stdout("Created {$email}\n");

        return $user;
    }

    private function seedLeads(User $admin, User $mark, User $sofia): void
    {
        $rows = [
            ['Anna Petrova', 'anna.petrova@example.com', '+1 555 010 1001', 'facebook', 'new', 'Asked for pricing on the starter plan.', $sofia, 1],
            ['James Carter', 'james.carter@example.com', '+1 555 010 1002', 'google', 'new', 'Filled the website form after a search ad.', $mark, 1],
            ['Iryna Bondar', 'iryna.bondar@example.com', '+1 555 010 1003', 'telegram', 'new', 'Wrote in the Telegram channel.', $sofia, 2],
            ['David Nguyen', 'david.nguyen@example.com', null, 'website', 'new', null, $mark, 2],
            ['Olga Sydorenko', 'olga.sydorenko@example.com', '+1 555 010 1005', 'other', 'new', 'Referral from an existing customer.', $admin, 3],
            ['Chris Alvarez', 'chris.alvarez@example.com', '+1 555 010 1006', 'facebook', 'new', 'Wants a call next Tuesday.', $mark, 4],
            ['Maya Chen', 'maya.chen@example.com', '+1 555 010 1101', 'google', 'in_progress', 'Demo scheduled for Thursday.', $sofia, 5],
            ['Petro Ivanov', 'petro.ivanov@example.com', '+1 555 010 1102', 'website', 'in_progress', 'Comparing us with two other vendors.', $mark, 6],
            ['Helen Brooks', 'helen.brooks@example.com', '+1 555 010 1103', 'telegram', 'in_progress', 'Asked for a security questionnaire.', $admin, 7],
            ['Andriy Melnyk', 'andriy.melnyk@example.com', '+1 555 010 1104', 'facebook', 'in_progress', null, $sofia, 8],
            ['Sara Iqbal', 'sara.iqbal@example.com', '+1 555 010 1105', 'google', 'in_progress', 'Needs a proposal in Ukrainian.', $mark, 9],
            ['Tom Walker', 'tom.walker@example.com', null, 'other', 'in_progress', 'Waiting on budget approval.', $sofia, 11],
            ['Nina Kravets', 'nina.kravets@example.com', '+1 555 010 1201', 'website', 'converted', 'Signed the annual plan.', $mark, 12],
            ['Oliver Grant', 'oliver.grant@example.com', '+1 555 010 1202', 'google', 'converted', 'Paid the invoice yesterday.', $sofia, 14],
            ['Yulia Romanenko', 'yulia.romanenko@example.com', '+1 555 010 1203', 'facebook', 'converted', null, $admin, 16],
            ['Ben Carter', 'ben.carter@example.com', '+1 555 010 1204', 'telegram', 'converted', 'Started onboarding.', $mark, 18],
            ['Fatima Noor', 'fatima.noor@example.com', '+1 555 010 1205', 'website', 'converted', 'Expanded to a second workspace.', $sofia, 21],
            ['Leo Martin', 'leo.martin@example.com', '+1 555 010 1301', 'google', 'rejected', 'No budget this quarter.', $mark, 10],
            ['Kateryna Levchenko', 'kateryna.levchenko@example.com', '+1 555 010 1302', 'facebook', 'rejected', 'Chose an in-house tool.', $sofia, 15],
            ['Samir Haddad', 'samir.haddad@example.com', null, 'other', 'rejected', 'Unresponsive after three follow-ups.', $mark, 20],
            ['Emily Ross', 'emily.ross@example.com', '+1 555 010 1304', 'telegram', 'rejected', 'Out of our service area.', $admin, 25],
        ];

        foreach ($rows as $row) {
            [$name, $email, $phone, $source, $status, $comment, $manager, $daysAgo] = $row;
            $createdAt = time() - ($daysAgo * 86400) - 3600;

            $lead = new Lead(['scenario' => Lead::SCENARIO_ADMIN]);
            $lead->name = $name;
            $lead->email = $email;
            $lead->phone = $phone;
            $lead->source = $source;
            $lead->status = $status;
            $lead->comment = $comment;
            $lead->manager_id = $manager->getId();
            $lead->detachBehavior('timestamp');
            $lead->created_at = $createdAt;
            $lead->updated_at = $createdAt;

            if (!$lead->save()) {
                throw new \RuntimeException('Could not create lead ' . $email . ': ' . json_encode($lead->getErrors()));
            }
        }
    }
}
