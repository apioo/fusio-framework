<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260510082730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the todo table';
    }

    public function up(Schema $schema): void
    {
        $todoTable = $schema->createTable('app_todo');
        $todoTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $todoTable->addColumn('user_id', 'integer');
        $todoTable->addColumn('completed', 'integer', ['default' => 0, 'notnull' => false]);
        $todoTable->addColumn('title', 'string');
        $todoTable->addColumn('insert_date', 'datetime');
        $todoTable->setPrimaryKey(['id']);

        $todoTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'todo_user_id');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('app_todo');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
