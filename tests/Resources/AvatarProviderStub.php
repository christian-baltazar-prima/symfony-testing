<?php

declare(strict_types=1);

namespace App\Tests\Resources;

use App\Entity\User;
use App\Service\User\UserAvatarProvider;

class AvatarProviderStub implements UserAvatarProvider
{
    public function getAvatarUrl(User $user): ?string
    {
        return null;
    }
}