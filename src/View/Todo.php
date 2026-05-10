<?php

namespace App\View;

use App\Table;
use Fusio\Impl\Table\Generated\UserTable;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

class Todo extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null): mixed
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $condition = Condition::withAnd();

        if ($search !== null && $search !== '') {
            $condition->like(Table\Generated\TodoTable::COLUMN_TITLE, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Todo::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'items' => $builder->doCollection([$this->getTable(Table\Todo::class), 'findAll'], [$condition, $startIndex, $count], [
                'id' => $builder->fieldInteger(Table\Generated\TodoTable::COLUMN_ID),
                'user' => $builder->doEntity([$this->getTable(UserTable::class), 'find'], [new Reference(Table\Generated\TodoTable::COLUMN_USER_ID)], [
                    'id' => $builder->fieldInteger(UserTable::COLUMN_ID),
                    'name' => UserTable::COLUMN_NAME,
                ]),
                'title' => Table\Generated\TodoTable::COLUMN_TITLE,
                'insertDate' => $builder->fieldDateTime(Table\Generated\TodoTable::COLUMN_INSERT_DATE),
                'links' => [
                    'self' => $builder->fieldFormat('id', '/todo/%s'),
                ]
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id): mixed
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Todo::class), 'find'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\TodoTable::COLUMN_ID),
            'user' => $builder->doEntity([$this->getTable(UserTable::class), 'find'], [new Reference(Table\Generated\TodoTable::COLUMN_USER_ID)], [
                'id' => $builder->fieldInteger(UserTable::COLUMN_ID),
                'name' => UserTable::COLUMN_NAME,
            ]),
            'title' => Table\Generated\TodoTable::COLUMN_TITLE,
            'insertDate' => $builder->fieldDateTime(Table\Generated\TodoTable::COLUMN_INSERT_DATE),
            'links' => [
                'self' => $builder->fieldFormat('id', '/comment/%s'),
            ]
        ]);

        return $builder->build($definition);
    }
}
