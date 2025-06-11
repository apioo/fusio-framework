<?php

namespace App\Table\Generated;

class TodoRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $completed = null;
    private ?string $title = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setCompleted(?int $completed): void
    {
        $this->completed = $completed;
    }
    public function getCompleted(): ?int
    {
        return $this->completed;
    }
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    public function getTitle(): string
    {
        return $this->title ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "title" was provided');
    }
    public function setInsertDate(\PSX\DateTime\LocalDateTime $insertDate): void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate(): \PSX\DateTime\LocalDateTime
    {
        return $this->insertDate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "insert_date" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('completed', $this->completed);
        $record->put('title', $this->title);
        $record->put('insert_date', $this->insertDate);
        return $record;
    }
    public function jsonSerialize(): object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data): self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->completed = isset($data['completed']) && is_int($data['completed']) ? $data['completed'] : null;
        $row->title = isset($data['title']) && is_string($data['title']) ? $data['title'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}