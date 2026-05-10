<?php

declare(strict_types = 1);

namespace App\Model;

use PSX\Schema\Attribute\Description;

#[Description('Represents a todo')]
class Todo implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    protected ?User $user = null;
    protected ?string $title = null;
    protected ?bool $completed = null;
    protected ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
    public function getTitle(): ?string
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
    /**
     * @return \PSX\Record\RecordInterface<mixed>
     */
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('user', $this->user);
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

