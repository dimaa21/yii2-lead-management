<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Indexes for the columns the lead list filters and sorts on.
 *
 * manager_id is listed here on purpose. MySQL also indexes it for the foreign
 * key, and this named index keeps every lookup column in one migration.
 */
class m261007_120002_add_indexes extends Migration
{
    public function safeUp(): void
    {
        $this->createIndex('idx_lead_email', '{{%lead}}', 'email');
        $this->createIndex('idx_lead_status', '{{%lead}}', 'status');
        $this->createIndex('idx_lead_source', '{{%lead}}', 'source');
        $this->createIndex('idx_lead_manager_id', '{{%lead}}', 'manager_id');
        $this->createIndex('idx_lead_created_at', '{{%lead}}', 'created_at');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx_lead_created_at', '{{%lead}}');
        $this->dropIndex('idx_lead_manager_id', '{{%lead}}');
        $this->dropIndex('idx_lead_source', '{{%lead}}');
        $this->dropIndex('idx_lead_status', '{{%lead}}');
        $this->dropIndex('idx_lead_email', '{{%lead}}');
    }
}
