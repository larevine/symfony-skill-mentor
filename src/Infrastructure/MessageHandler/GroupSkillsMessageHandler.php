<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\Dto\Message\GroupSkillsMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

readonly class GroupSkillsMessageHandler implements ConsumerInterface
{
    public function __construct(
        private GroupServiceInterface $group_service,
        private SkillServiceInterface $skill_service,
        private CacheItemPoolInterface $group_pool,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(AMQPMessage $msg): int
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

            // Удаляем старые требуемые навыки
            foreach ($group->getRequiredSkills() as $skill) {
                $this->group_service->removeRequiredSkill($group, $skill->getSkill());
            }

            // Добавляем новые требуемые навыки
            foreach ($message->getSkills() as $skill_data) {
                $skill = $this->skill_service->findById(new EntityId($skill_data['id']));
                if ($skill === null) {
                    $this->logger->warning('Skill not found', [
                        'skill_id' => $skill_data['id']
                    ]);
                    continue;
                }

                $this->group_service->addRequiredSkill(
                    $group,
                    $skill,
                    ProficiencyLevel::fromInt($skill_data['level'])
                );
            }

            // Инвалидируем кэш
            $this->group_pool->deleteItem('group_' . $message->getGroupId());

            return self::MSG_ACK;
        } catch (\Throwable $e) {
            $this->logger->error('Error processing group skills message: ' . $e->getMessage());
            return self::MSG_REJECT;
        }
    }
}
