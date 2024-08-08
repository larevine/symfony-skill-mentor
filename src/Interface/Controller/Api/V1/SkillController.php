<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1;

use App\Application\Factory\ManageSkillDTOFactory;
use App\Application\Interface\Service\ISkillService;
use App\Application\Service\Management\SkillManagementService;
use App\Application\Exception\ValidationException;
use App\Application\Exception\SkillNotFoundException;
use App\Domain\Entity\Skill;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;

#[Route(path: '/api/v1/skills')]
class SkillController extends AbstractController
{
    public function __construct(
        private readonly ISkillService $skill_service,
        private readonly SkillManagementService $skill_management_service,
        private readonly ManageSkillDTOFactory $dto_factory,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getSkillsAction(Request $request): JsonResponse
    {
        $perPage = $request->query->getInt('per_page', 20);
        $page = $request->query->getInt('page', 1);

        $skills = $this->skill_service->findPaginated($page, $perPage);
        $status_code = count($skills) === 0 ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $skills_array = array_map(static fn (Skill $skill) => $skill->toArray(), $skills);

        return $this->json(['skills' => $skills_array], $status_code);
    }

    #[Route(path: '/{skill_id}', requirements: ['skill_id' => '\d+'], methods: ['GET'])]
    public function getSkillAction(int $skill_id): JsonResponse
    {
        $skill = $this->skill_service->findSkillById($skill_id);
        return $this->json(['success' => true, 'skill' => $skill->toArray()], Response::HTTP_OK);
    }

    #[Route(path: '', methods: ['POST'])]
    public function createSkillAction(Request $request): JsonResponse
    {
        try {
            $dto = $this->dto_factory->createFromRequest($request);
            $skill_id = $this->skill_management_service->saveSkillWithRelatedEntities($dto);
            return $this->json(['success' => true, 'skill_id' => $skill_id], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatViolations($e->getViolations()),
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while creating the skill.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{skill_id}', requirements: ['skill_id' => '\d+'], methods: ['PATCH'])]
    public function updateSkillAction(int $skill_id, Request $request): JsonResponse
    {
        try {
            $dto = $this->dto_factory->createFromRequest($request);
            $result = $this->skill_management_service->updateSkillWithRelatedEntities($skill_id, $dto);

            return $this->json(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatViolations($e->getViolations()),
            ], Response::HTTP_BAD_REQUEST);
        } catch (SkillNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while updating the skill.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{skill_id}', requirements: ['skill_id' => '\d+'], methods: ['DELETE'])]
    public function deleteSkillAction(int $skill_id): JsonResponse
    {
        try {
            $result = $this->skill_management_service->deleteSkillWithRelatedEntities($skill_id);

            return $this->json(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        } catch (SkillNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while deleting the skill.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return array<string>
     */
    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }
        return $errors;
    }
}
