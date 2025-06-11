<?php

namespace App\Table\Generated;

enum TodoColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \App\Table\Generated\TodoTable::COLUMN_ID;
    case COMPLETED = \App\Table\Generated\TodoTable::COLUMN_COMPLETED;
    case TITLE = \App\Table\Generated\TodoTable::COLUMN_TITLE;
    case INSERT_DATE = \App\Table\Generated\TodoTable::COLUMN_INSERT_DATE;
}