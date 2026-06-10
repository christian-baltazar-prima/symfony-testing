<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

readonly class CreateUserAction
{
    public function __construct(
        private UserPasswordService $userPassword,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(FormInterface $form): void
    {
        $user = $form->getData();
        if (!$user instanceof User) {
            throw new \LogicException('Form data must be an instance of User');
        }

        $user->setPassword(($this->userPassword)($user, $user->getPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}