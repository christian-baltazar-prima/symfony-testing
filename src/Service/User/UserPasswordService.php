<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserPasswordService
{

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(User $user, string $plainPassword): string
    {
        if (strlen(trim($plainPassword)) < 6) {
            throw new \LogicException('Password must be at least 6 characters long');
        }

        $email = explode('@', $user->getEmail());
        if (str_contains($plainPassword, $email[0])) {
            throw new \LogicException('Password cannot contain the email username');
        }

        if (!preg_match('/[A-Za-z]/', $plainPassword)) {
            throw new \LogicException('Password must contain at least one letter');
        }

        if (!preg_match('/[0-9]/', $plainPassword)) {
            throw new \LogicException('Password must contain at least one number');
        }

        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }
}