<?php

declare(strict_types = 1);

namespace App\Model;

use PSX\Schema\Attribute\Description;
/**
 * @extends Collection<Todo>
 */
#[Description('A collection of all todos')]
class TodoCollection extends Collection implements \JsonSerializable, \PSX\Record\RecordableInterface
{
}

