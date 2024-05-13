<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\ManageSkillDTO;
use App\Entity\Skill;
use App\Manager\SkillManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: 'api/v1/skills')]
class SkillController extends AbstractController
{
    public function __construct(
        private readonly SkillManager $skill_manager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getSkillsAction(Request $request): JsonResponse
    {
        $per_page = $request->query->get('per_page');
        $page = $request->query->get('page');
        $skills = $this->skill_manager->getSkills($page ?? 0, $per_page ?? 20);
        $code = count($skills) === 0 ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            data: ['skills' => array_map(static fn (Skill $skill) => $skill->toArray(), $skills)],
            status: $code
        );
    }

    #[Route(path: '/{skill_id}', requirements: ['skill_id' => '\d+'], methods: ['GET'])]
    public function getSkillAction(int $skill_id): JsonResponse
    {
        $skill = $this->skill_manager->findSkill($skill_id);
        [$data, $code] = $skill === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'skill' => $skill->toArray()], Response::HTTP_OK];
        return new JsonResponse($data, $code);
    }

    #[Route(path: '', methods: ['POST'])]
    public function createSkillAction(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), ManageSkillDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse([
                'success' => false,
                'errors' => $this->serializer->toArray($violations)
            ], Response::HTTP_BAD_REQUEST);
        }
        $skill_id = $this->skill_manager->saveSkillFromDTO(new Skill(), $dto);
        [$data, $code] = $skill_id === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'skill_id' => $skill_id], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    #[Route(path: '/{skill_id}', requirements: ['skill_id' => '\d+'], methods: ['PATCH'])]
    public function updateSkillAction(int $skill_id, Request $request): JsonResponse
    {
        $skill = $this->skill_manager->findSkill($skill_id);
        if ($skill === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $dto = $this->serializer->deserialize($request->getContent(), ManageSkillDTO::class, 'json');
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return new JsonResponse(
                data: [
                'success' => false,
                'errors' => $this->serializer->toArray($violations)
                ],
                status: Response::HTTP_BAD_REQUEST
            );
        }
        $result = $this->skill_manager->saveSkillFromDTO($skill, $dto);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    #[Route(path: '/{skill_id}', requirements: ['skill_id' => '\d+'], methods: ['DELETE'])]
    public function deleteSkillAction(int $skill_id): JsonResponse
    {
        $skill = $this->skill_manager->findSkill($skill_id);
        if ($skill === null) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }
        $result = $this->skill_manager->deleteSkill($skill);

        return new JsonResponse(
            data: ['success' => $result],
            status: $result
                ? Response::HTTP_OK
                : Response::HTTP_BAD_REQUEST
        );
    }
}
