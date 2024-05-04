<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\ManageUserDTO;
use App\Entity\User;
use App\Manager\UserManager;
use App\Service\Builder\UserBuilderService;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: 'api/v1/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManager $user_manager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getUsersAction(Request $request): JsonResponse
    {
        $per_page = $request->query->get('per_page');
        $page = $request->query->get('page');
        $users = $this->user_manager->getUsers($page ?? 0, $per_page ?? 20);
        $code = empty($users) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return $this->json(['users' => array_map(static fn(User $user) => $user->toArray(), $users)], $code,
            context: ['json_encode_options' => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
        );
    }

    #[Route(path: '/{user_id}', requirements: ['user_id' => '\d+'], methods: ['GET'])]
    public function getUserAction(int $user_id): JsonResponse
    {
        $user = $this->user_manager->findUser($user_id);
        [$data, $code] = $user === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'user' => $user->toArray()], Response::HTTP_OK];
        return new JsonResponse($data, $code);
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '', methods: ['POST'])]
    public function createUserAction(Request $request, UserBuilderService $service): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), ManageUserDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['success' => false, 'errors' => $this->serializer->toArray($violations)], Response::HTTP_BAD_REQUEST);
        }
        $user_id = $service->saveUserWithRelatedEntities($dto);
        [$data, $code] = $user_id === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'user_id' => $user_id], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/{user_id}', requirements: ['user_id' => '\d+'], methods: ['PATCH'])]
    public function updateUserAction(int $user_id, Request $request, UserBuilderService $service): JsonResponse
    {
        $user = $this->user_manager->findUser($user_id);
        if ($user === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $dto = $this->serializer->deserialize($request->getContent(), ManageUserDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(['success' => false, 'errors' => $this->serializer->toArray($violations)], Response::HTTP_BAD_REQUEST);
        }
        $result = $service->updateUserWithRelatedEntities($user, $dto);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    #[Route(path: '/{user_id}', requirements: ['user_id' => '\d+'], methods: ['DELETE'])]
    public function deleteUserAction(int $user_id, UserBuilderService $service): JsonResponse
    {
        $user = $this->user_manager->findUser($user_id);
        if ($user === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $result = $service->deleteUserWithRelatedEntities($user);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}