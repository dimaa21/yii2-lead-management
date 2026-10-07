<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Leads. manager_id references user.id, declared inside CREATE TABLE so the
 * constraint exists on MySQL and on SQLite (SQLite cannot add a foreign key later).
 * ON DELETE RESTRICT keeps a manager from being removed while they still own leads.
 */
class m261007_120001_create_lead_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%lead}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull(),
            'phone' => $this->string(32)->null(),
            'source' => $this->string(32)->notNull(),
            'status' => $this->string(32)->notNull(),
            'comment' => $this->text()->null(),
            'manager_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[manager_id]]) REFERENCES {{%user}} ([[id]]) ON DELETE RESTRICT ON UPDATE CASCADE',
        ], $this->tableOptions());
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%lead}}');
    }

    private function tableOptions(): ?string
    {
        if ($this->db->driverName === 'mysql') {
            return 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        return null;
    }
}
