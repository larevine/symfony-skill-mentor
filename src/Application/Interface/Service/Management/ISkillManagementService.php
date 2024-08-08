<?php

declare(strict_types=1);

namespace App\Application\Interface\Service\Management;

use App\Application\DTO\ManageSkillDTO;
use App\Application\Exception\SkillNotFoundException;
use App\Application\Exception\ValidationException;

interface ISkillManagementService
{
    /**
     * Создание нового навыка из DTO.
     *
     * @param ManageSkillDTO $dto
     * @return int ID созданного навыка
     * @throws ValidationException
     */
    public function saveSkillWithRelatedEntities(ManageSkillDTO $dto): int;

    /**
     * Обновление существующего навыка из DTO.
     *
     * @param int $skill_id
     * @param ManageSkillDTO $dto
     * @return bool Успешность операции
     * @throws ValidationException|SkillNotFoundException
     */
    public function updateSkillWithRelatedEntities(int $skill_id, ManageSkillDTO $dto): bool;

    /**
     * Удаление навыка по ID.
     *
     * @param int $skill_id
     * @return bool Успешность операции
     * @throws SkillNotFoundException
     */
    public function deleteSkillWithRelatedEntities(int $skill_id): bool;
}
