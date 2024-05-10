<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\ManageGroupDTO;
use App\Manager\GroupManager;
use App\Service\Builder\GroupBuilderService;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: 'api/v1/groups')]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly GroupManager $group_manager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getGroupsAction(Request $request): JsonResponse
    {
        $per_page = $request->query->get('per_page');
        $page = $request->query->get('page');
        $groups = $this->group_manager->getGroups($page ?? 0, $per_page ?? 20);
        $code = empty($groups) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(['groups' => array_map(static fn(\App\Entity\Group $group) => $group->toArray(), $groups)], $code);
    }

    #[Route(path: '/{group_id}', requirements: ['group_id' => '\d+'], methods: ['GET'])]
    public function getGroupAction(int $group_id): JsonResponse
    {
        $group = $this->group_manager->findGroup($group_id);
        [$data, $code] = $group === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'group' => $group->toArray()], Response::HTTP_OK];
        return new JsonResponse($data, $code);
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '', methods: ['POST'])]
    public function createGroupAction(Request $request, GroupBuilderService $service): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), ManageGroupDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['success' => false, 'errors' => $this->serializer->toArray($violations)], Response::HTTP_BAD_REQUEST);
        }
        $group_id = $service->saveGroupWithRelatedEntities($dto);
        [$data, $code] = $group_id === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'group_id' => $group_id], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/{group_id}', requirements: ['group_id' => '\d+'], methods: ['PATCH'])]
    public function updateGroupAction(int $group_id, Request $request, GroupBuilderService $service): JsonResponse
    {
        $group = $this->group_manager->findGroup($group_id);
        if ($group === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $dto = $this->serializer->deserialize($request->getContent(), ManageGroupDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['success' => false, 'errors' => $this->serializer->toArray($violations)], Response::HTTP_BAD_REQUEST);
        }
        $result = $service->updateGroupWithRelatedEntities($group, $dto);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    #[Route(path: '/{group_id}', requirements: ['group_id' => '\d+'], methods: ['DELETE'])]
    public function deleteGroupAction(int $group_id, GroupBuilderService $service): JsonResponse
    {
        $group = $this->group_manager->findGroup($group_id);
        if ($group === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $service->deleteGroupWithRelatedEntities($group);

        return new JsonResponse(['success' => $this->group_manager->deleteGroupById($group_id)], Response::HTTP_OK);
    }
}
