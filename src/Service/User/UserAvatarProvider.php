<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;

interface UserAvatarProvider
{
    public function getAvatarUrl(User $user): ?string;
}