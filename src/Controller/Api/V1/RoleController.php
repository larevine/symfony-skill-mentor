<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\ManageRoleDTO;
use App\Entity\Role;
use App\Manager\RoleManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: 'api/v1/roles')]
class RoleController extends AbstractController
{
    public function __construct(
        private readonly RoleManager $role_manager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getRolesAction(Request $request): JsonResponse
    {
        $per_page = $request->query->get('per_page');
        $page = $request->query->get('page');
        $roles = $this->role_manager->getRoles($page ?? 0, $per_page ?? 20);
        $code = empty($roles) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(['roles' => array_map(static fn(Role $role) => $role->toArray(), $roles)], $code);
    }

    #[Route(path: '/{role_id}', requirements: ['role_id' => '\d+'], methods: ['GET'])]
    public function getRoleAction(int $role_id): JsonResponse
    {
        $role = $this->role_manager->findRole($role_id);
        [$data, $code] = $role === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'role' => $role->toArray()], Response::HTTP_OK];
        return new JsonResponse($data, $code);
    }

    #[Route(path: '', methods: ['POST'])]
    public function createRoleAction(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), ManageRoleDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['success' => false, 'errors' => $this->serializer->toArray($violations)], Response::HTTP_BAD_REQUEST);
        }
        $role_id = $this->role_manager->saveRoleFromDTO(new Role(), $dto);
        [$data, $code] = $role_id === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'role_id' => $role_id], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    #[Route(path: '/{role_id}', requirements: ['role_id' => '\d+'], methods: ['PATCH'])]
    public function updateRoleAction(int $role_id, Request $request): JsonResponse
    {
        $role = $this->role_manager->findRole($role_id);
        if ($role === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $dto = $this->serializer->deserialize($request->getContent(), ManageRoleDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['success' => false, 'errors' => $this->serializer->toArray($violations)], Response::HTTP_BAD_REQUEST);
        }
        $result = $this->role_manager->saveRoleFromDTO($role, $dto);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    #[Route(path: '{role_id}', requirements: ['role_id' => '\d+'], methods: ['DELETE'])]
    public function deleteRoleAction(int $role_id): JsonResponse
    {
        $role = $this->role_manager->findRole($role_id);
        if ($role === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $result = $this->role_manager->deleteRoleById($role_id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}