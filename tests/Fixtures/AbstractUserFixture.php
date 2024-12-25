<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Domain\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractUserFixture extends Fixture
{
    protected static ?UserPasswordHasherInterface $password_hasher = null;

    public static function setPasswordHasher(UserPasswordHasherInterface $password_hasher): void
    {
        self::$password_hasher = $password_hasher;
    }

    protected function hashPassword(User $user, string $plainPassword): string
    {
        if (self::$password_hasher === null) {
            throw new \RuntimeException('Password hasher is not set. Call setPasswordHasher() first.');
        }

        return self::$password_hasher->hashPassword($user, $plainPassword);
    }
}
