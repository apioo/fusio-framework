<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611074044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $commentTable = $schema->createTable('app_todo');
        $commentTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $commentTable->addColumn('completed', 'integer', ['default' => 0, 'notnull' => false]);
        $commentTable->addColumn('title', 'string');
        $commentTable->addColumn('insert_date', 'datetime');
        $commentTable->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
