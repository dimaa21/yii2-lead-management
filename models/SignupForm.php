<?php

declare(strict_types=1);

namespace app\models;

use app\components\UserRole;
use app\services\RbacService;
use yii\base\Model;

class SignupForm extends Model
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_repeat = '';

    public function rules(): array
    {
        return [
            [['name', 'email', 'password', 'password_repeat'], 'required'],
            [['name', 'email'], 'trim'],
            ['name', 'string', 'min' => 2, 'max' => 255],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            ['email', 'filter', 'filter' => 'strtolower'],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'email',
                'message' => 'This email is already registered.',
            ],
            ['password', 'string', 'min' => 8, 'max' => 72],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Repeat password',
        ];
    }

    /**
     * Self-service registration always creates a manager.
     * Admin accounts are seeded, not self-assigned.
     */
    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->role = UserRole::Manager->value;
        $user->setPassword($this->password);

        if (!$user->save()) {
            foreach ($user->getErrors() as $attribute => $messages) {
                $target = $this->hasProperty($attribute) ? $attribute : 'email';
                foreach ($messages as $message) {
                    $this->addError($target, $message);
                }
            }

            return null;
        }

        (new RbacService())->syncAssignment($user);

        return $user;
    }
}
