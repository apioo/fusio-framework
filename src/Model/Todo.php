<?php

declare(strict_types = 1);

namespace App\Model;

use PSX\Schema\Attribute\Description;

#[Description('A specific todo')]
class Todo implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    protected ?int $title = null;
    protected ?bool $completed = null;
    protected ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setTitle(?int $title): void
    {
        $this->title = $title;
    }
    public function getTitle(): ?int
    {
        return $this->title;
    }
    public function setCompleted(?bool $completed): void
    {
        $this->completed = $completed;
    }
    public function getCompleted(): ?bool
    {
        return $this->completed;
    }
    public function setInsertDate(?\PSX\DateTime\LocalDateTime $insertDate): void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate(): ?\PSX\DateTime\LocalDateTime
    {
        return $this->insertDate;
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('title', $this->title);
        $record->put('completed', $this->completed);
        $record->put('insertDate', $this->insertDate);
        return $record;
    }
    public function jsonSerialize(): object
    {
        return (object) $this->toRecord()->getAll();
    }
}

