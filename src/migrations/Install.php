<?php
namespace born05\twofactorauthentication\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\MigrationHelper;
use born05\twofactorauthentication\records\Session;
use born05\twofactorauthentication\records\User;

class Install extends Migration
{
    public function safeUp()
    {
        if ($this->upgradeFromCraft2()) {
            return;
        }

        // Fresh install code goes here...
        $this->createTable(Session::tableName(), [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'sessionId' => $this->integer()->notNull(),
            'dateVerified' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(User::tableName(), [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'secret' => $this->string(512)->notNull(),
            'dateVerified' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, Session::tableName(), ['userId', 'sessionId'], true);
        $this->createIndex(null, User::tableName(), ['userId'], true);

        $this->addForeignKey(null, Session::tableName(), ['userId'], '{{%users}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Session::tableName(), ['sessionId'], '{{%sessions}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, User::tableName(), ['userId'], '{{%users}}', ['id'], 'CASCADE', null);
    }

    protected function upgradeFromCraft2()
    {
        // Fetch the old plugin row, if it was installed
        $oldTableExists = $this->db->schema->getTableSchema('{{%twofactorauthentication_sessions}}', true);
        if (!isset($oldTableExists)) {
            return false;
        }

        // Any additional upgrade code goes here...
        $this->renameTable('{{%twofactorauthentication_sessions}}', Session::tableName());
        $this->renameTable('{{%twofactorauthentication_users}}', User::tableName());

        return true;
    }

    public function safeDown()
    {
        $this->dropTableIfExists(Session::tableName());
        $this->dropTableIfExists(User::tableName());

        return true;
    }
}
