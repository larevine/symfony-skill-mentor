<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\ManageRoleDTO;
use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RoleManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function findRole(int $id): ?Role
    {
        $repository = $this->em->getRepository(Role::class);
        $role = $repository->find($id);

        return $role instanceof Role ? $role : null;
    }

    public function findRoleByName(string $name): ?Role
    {
        /** @var RoleRepository $role_repository */
        $role_repository = $this->em->getRepository(Role::class);
        /** @var Role|null $role */
        return $role_repository->findOneBy(['name' => $name]);
    }


    /**
     * @return Role[]
     */
    public function getRoles(int $page, int $per_page): array
    {
        /** @var RoleRepository $role_repository */
        $role_repository = $this->em->getRepository(Role::class);

        return $role_repository->getRoles($page, $per_page);
    }

    public function saveRole(Role $role): void
    {
        $this->em->persist($role);
        $this->em->flush();
    }

    public function saveRoleFromDTO(Role $role, ManageRoleDTO $dto): ?int
    {
        $role->setName($dto->name);
        $this->em->persist($role);
        $this->em->flush();

        return $role->getId();
    }

    public function createByName(string $name): Role
    {
        $role = new Role();
        $role->setName($name);
        $this->em->persist($role);
        $this->em->flush();

        return $role;
    }

    public function updateRoleNameById(int $role_id, string $name): ?Role
    {
        $role = $this->findRole($role_id);
        if (!($role instanceof Role)) {
            return null;
        }
        $role->setName($name);
        $this->em->flush();

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        $this->em->remove($role);
        $this->em->flush();

        return true;
    }

    public function deleteRoleById(int $roleId): bool
    {
        /** @var RoleRepository $role_repository */
        $role_repository = $this->em->getRepository(Role::class);
        /** @var Role $role */
        $role = $role_repository->find($roleId);
        if ($role === null) {
            return false;
        }
        return $this->deleteRole($role);
    }
}
