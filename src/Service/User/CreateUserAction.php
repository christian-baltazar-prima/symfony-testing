<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreateUserAction
{
    public function __construct(
        private readonly UserPasswordService $userPassword,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserAvatarProvider $avatarProvider,
    ) {}

    public function __invoke(User $user): void
    {
        $user->setPassword(($this->userPassword)($user, $user->getPassword()));
        $user->setAvatar($this->avatarProvider->getAvatarUrl($user));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}