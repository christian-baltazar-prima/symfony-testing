<?php

declare(strict_types=1);

namespace App\Service\Gravatar;

use App\Entity\User;
use App\Service\User\UserAvatarProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class GravatarProvider implements UserAvatarProvider
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%gravatar.base_url%')]
        private string $gravatarBaseUrl,
        #[Autowire('%gravatar.token%')]
        private string $gravatarApiToken,
    ) {}

    public function getAvatarUrl(User $user): ?string
    {
        $hashEmail = hash('sha256', $user->getEmail());
        $uri = $this->gravatarBaseUrl . '/profiles/' . $hashEmail;
        $response = $this->httpClient->request('GET', $uri, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->gravatarApiToken,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $body = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $body['avatar_url'] ?? null;
    }
}