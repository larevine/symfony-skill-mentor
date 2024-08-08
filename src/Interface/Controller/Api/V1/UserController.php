<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1;

use App\Application\Factory\ManageUserDTOFactory;
use App\Application\Interface\Service\IUserService;
use App\Application\Service\Management\UserManagementService;
use App\Application\Exception\ValidationException;
use App\Application\Exception\UserNotFoundException;
use App\Domain\Entity\User;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;

#[Route(path: '/api/v1/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly IUserService $user_service,
        private readonly UserManagementService $user_management_service,
        private readonly ManageUserDTOFactory $dto_factory,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getUsersAction(Request $request): JsonResponse
    {
        $per_page = $request->query->getInt('per_page', 20);
        $page = $request->query->getInt('page', 1);

        $users = $this->user_service->getUsers($page, $per_page);
        $status_code = count($users) === 0 ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $users_array = array_map(static fn (User $user): array => $user->toArray(), $users);

        return $this->json(['users' => $users_array], $status_code);
    }

    #[Route(path: '/{user_id}', requirements: ['user_id' => '\d+'], methods: ['GET'])]
    public function getUserAction(int $user_id): JsonResponse
    {
        $user = $this->user_service->findUser($user_id);
        return $this->json(['success' => true, 'user' => $user->toArray()], Response::HTTP_OK);
    }

    #[Route(path: '', methods: ['POST'])]
    public function createUserAction(Request $request): JsonResponse
    {
        try {
            $dto = $this->dto_factory->createFromRequest($request);
            $user_id = $this->user_management_service->saveUserWithRelatedEntities($dto);

            return $this->json(['success' => true, 'user_id' => $user_id], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatViolations($e->getViolations())
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while creating the user.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{user_id}', requirements: ['user_id' => '\d+'], methods: ['PATCH'])]
    public function updateUserAction(int $user_id, Request $request): JsonResponse
    {
        try {
            $dto = $this->dto_factory->createFromRequest($request);
            $result = $this->user_management_service->updateUserWithRelatedEntities($user_id, $dto);

            return $this->json(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $this->formatViolations($e->getViolations())
            ], Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while updating the user.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/{user_id}', requirements: ['user_id' => '\d+'], methods: ['DELETE'])]
    public function deleteUserAction(int $user_id): JsonResponse
    {
        try {
            $result = $this->user_management_service->deleteUserWithRelatedEntities($user_id);
            return $this->json(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
        } catch (UserNotFoundException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while deleting the user.',
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
