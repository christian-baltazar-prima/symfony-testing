<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreateUserAction
{
    const string DEFAULT_AVATAR_URL = 'https://placehold.net/avatar-5.png';

    public function __construct(
        private readonly UserPasswordService $userPassword,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserAvatarProvider $avatarProvider,
    ) {}

    public function __invoke(User $user): void
    {
        $user->setPassword(($this->userPassword)($user, $user->getPassword()));
        $user->setAvatar($this->avatarProvider->getAvatarUrl($user) ?? self::DEFAULT_AVATAR_URL);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}