<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\Dto\Message\GroupSkillsMessage;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class GroupSkillsMessageHandler extends AbstractMessageHandler implements ConsumerInterface
{
    public function __construct(
        EntityManagerInterface $entity_manager,
        LoggerInterface $logger,
        private readonly GroupServiceInterface $group_service,
        private readonly SkillServiceInterface $skill_service,
        private readonly ProducerInterface $domain_events_producer,
    ) {
        parent::__construct($entity_manager, $logger);
    }

    public function execute(AMQPMessage $msg): int
    {
        return $this->processMessage($msg);
    }

    protected function processMessage(AMQPMessage $msg): int
    {
        try {
            $message = GroupSkillsMessage::fromArray(json_decode($msg->getBody(), true));

            $this->logger->info('Processing group skills message', [
                'group_id' => $message->getGroupId(),
                'skills_count' => count($message->getSkills())
            ]);

            $group = $this->group_service->findById(new EntityId($message->getGroupId()));
            if ($group === null) {
                $this->logger->error('Group not found', [
                    'group_id' => $message->getGroupId()
                ]);
                return self::MSG_REJECT;
            }

            // Удаляем старые навыки
            foreach ($group->getSkills() as $skill) {
                $this->group_service->removeSkill($group, $skill->getSkill());

                // Публикуем событие об удалении навыка
                $this->domain_events_producer->publish(
                    json_encode([
                        'event' => 'group.skill_removed',
                        'payload' => [
                            'group_id' => $group->getId(),
                            'skill_id' => $skill->getSkill()->getId()
                        ]
                    ]),
                    'group.skill_removed'
                );
            }

            // Добавляем новые навыки
            foreach ($message->getSkills() as $skill_data) {
                $skill = $this->skill_service->findById(new EntityId($skill_data['skill_id']));
                if ($skill !== null) {
                    $level = new ProficiencyLevel($skill_data['level']);
                    $this->group_service->addSkill($group, $skill, $level);

                    // Публикуем событие о добавлении навыка
                    $this->domain_events_producer->publish(
                        json_encode([
                            'event' => 'group.skill_added',
                            'payload' => [
                                'group_id' => $group->getId(),
                                'skill_id' => $skill->getId(),
                                'level' => $level->getValue()
                            ]
                        ]),
                        'group.skill_added'
                    );
                } else {
                    $this->logger->warning('Skill not found', [
                        'skill_id' => $skill_data['skill_id']
                    ]);
                }
            }

            $this->logger->info('Group skills updated successfully', [
                'group_id' => $message->getGroupId()
            ]);

            return self::MSG_ACK;
        } catch (\Exception $e) {
            $this->logger->error('Error processing group skills message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::MSG_REJECT;
        }
    }
}
