<?php

namespace App\Service;

use App\Model;
use App\Table;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\DispatcherInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Impl\Repository\UserRepository;
use PSX\CloudEvents\Builder;
use PSX\DateTime\LocalDateTime;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Service which is responsible to create, update and delete a todo entry
 */
readonly class Todo
{
    public function __construct(
        private Table\Todo $table,
        private MailerInterface $mailer,
        private UserRepository $userRepository,
        private DispatcherInterface $dispatcher,
    ) {
    }

    public function create(Model\Todo $todo, ContextInterface $context): int
    {
        $this->assertTodo($todo);

        $row = new Table\Generated\TodoRow();
        $row->setUserId($context->getUser()->getId());
        $row->setCompleted($todo->getCompleted() ? 1 : 0);
        $row->setTitle($todo->getTitle() ?? throw new StatusCode\BadRequestException('No title provided'));
        $row->setInsertDate(LocalDateTime::now());
        $this->table->create($row);

        $id = $this->table->getLastInsertId();
        $this->dispatchEvent('todo_created', $row, $id);

        return $id;
    }

    public function update(int $id, Model\Todo $todo): int
    {
        $row = $this->table->find($id);
        if (!$row instanceof Table\Generated\TodoRow) {
            throw new StatusCode\NotFoundException('Provided todo entry does not exist');
        }

        $this->assertTodo($todo);

        $completed = $todo->getCompleted();
        $row->setCompleted($completed !== null ? ($completed ? 1 : 0) : $row->getCompleted());
        $row->setTitle($todo->getTitle() ?? $row->getTitle());
        $this->table->update($row);

        $this->dispatchEvent('todo_updated', $row, $row->getId());

        return $id;
    }

    public function delete(int $id): int
    {
        $row = $this->table->find($id);
        if (!$row instanceof Table\Generated\TodoRow) {
            throw new StatusCode\NotFoundException('Provided post does not exist');
        }

        $this->table->delete($row);

        $this->dispatchEvent('todo_deleted', $row, $row->getId());

        return $id;
    }

    public function reminder(): void
    {
        $todos = $this->table->findByCompleted(0);
        foreach ($todos as $todo) {
            $user = $this->userRepository->get($todo->getUserId());
            if (!$user instanceof UserInterface) {
                continue;
            }

            $userMail = $user->getEmail();
            if (empty($userMail)) {
                continue;
            }

            $email = new Email()
                ->from('info@my-api.com')
                ->to($userMail)
                ->subject('[REMINDER] ' . $todo->getTitle())
                ->text('This is a reminder to do the following task:' . "\n" . $todo->getTitle())
                ->html('<p>This is a reminder to do the following task:</p><p>' . $todo->getTitle() . '</p>');
            $this->mailer->send($email);
        }
    }

    private function dispatchEvent(string $type, Table\Generated\TodoRow $data, int $id): void
    {
        $event = new Builder()
            ->withId(Uuid::pseudoRandom())
            ->withSource('/todo/' . $id)
            ->withType($type)
            ->withDataContentType('application/json')
            ->withData($data)
            ->build();

        $this->dispatcher->dispatch($type, $event);
    }

    private function assertTodo(Model\Todo $todo): void
    {
        if ($todo->getCompleted() === null) {
            throw new StatusCode\BadRequestException('No completed provided');
        }

        if ($todo->getTitle() === null) {
            throw new StatusCode\BadRequestException('No title provided');
        }
    }
}
