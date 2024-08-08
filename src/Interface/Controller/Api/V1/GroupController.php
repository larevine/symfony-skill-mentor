<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1;

use App\Application\Exception\SkillNotFoundException;
use App\Application\Factory\ManageGroupDTOFactory;
use App\Application\Exception\GroupNotFoundException;
use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\IGroupService;
use App\Application\Interface\Service\Management\IGroupManagementService;
use App\Domain\Entity\Group;
use Exception;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/api/v1/groups')]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly IGroupService $group_service,
        private readonly IGroupManagementService $group_management_service,
        private readonly ManageGroupDTOFactory $dtoFactory,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getGroupsAction(Request $request): JsonResponse
    {
        $per_page = $request->query->getInt('per_page', 20);
        $page = $request->query->getInt('page', 1);

        $groups = $this->group_service->findPaginated($page, $per_page);
        $status_code = count($groups) === 0 ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $groups_array = array_map(static fn (Group $group) => $group->toArray(), $groups);

        return $this->json(['groups' => $groups_array], $status_code);
    }

    #[Route(path: '/{group_id}', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function getGroupAction(int $group_id): JsonResponse
    {
        $group = $this->group_service->findGroup($group_id);
        return $this->json(['success' => true, 'group' => $group->toArray()], Response::HTTP_OK);
    }

    #[Route(path: '', methods: ['POST'])]
    public function createGroupAction(Request $request): JsonResponse
    {
        try {
            $dto = $this->dtoFactory->createFromRequest($request);
            $group_id = $this->group_management_service->saveGroupWithRelatedEntities($dto);

            return $this->json(['success' => true, 'group_id' => $group_id], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatViolations($e->getViolations()),
            ], Response::HTTP_BAD_REQUEST);
        } catch (SkillNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while creating the group.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{group_id}', requirements: ['group_id' => '\d+'], methods: ['PATCH'])]
    public function updateGroupAction(int $group_id, Request $request): JsonResponse
    {
        try {
            $dto = $this->dtoFactory->createFromRequest($request);
            $result = $this->group_management_service->updateGroupWithRelatedEntities($group_id, $dto);

            return $this->json([
                'success' => $result
            ], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatViolations($e->getViolations()),
            ], Response::HTTP_BAD_REQUEST);
        } catch (GroupNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (SkillNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while updating the group.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{group_id}', requirements: ['group_id' => '\d+'], methods: ['DELETE'])]
    public function deleteGroupAction(int $group_id): JsonResponse
    {
        try {
            $result = $this->group_management_service->deleteGroupWithRelatedEntities($group_id);

            return $this->json([
                'success' => $result
            ], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        } catch (GroupNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while deleting the group.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }
        return $errors;
    }
}
