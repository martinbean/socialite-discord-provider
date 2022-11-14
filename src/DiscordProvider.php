<?php

namespace MartinBean\Laravel\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class DiscordProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    protected $scopes = [
        'email',
        'identify',
    ];

    /**
     * {@inheritDoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * Create a new bot redirect.
     *
     * @return \MartinBean\Laravel\Socialite\BotRedirectBuilder
     */
    public function bot()
    {
        return new BotRedirectBuilder($this->clientId);
    }

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://discord.com/api/oauth2/authorize', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl()
    {
        return 'https://discord.com/api/oauth2/token';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'https://discord.com/api/users/@me';

        $response = $this->getHttpClient()->get($userUrl, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    private function getGuildsByToken(array $scopes, $token)
    {
        if (in_array('guilds', $scopes)) {
            $guildsUrl = 'https://discord.com/api/users/@me/guilds';

            $response = $this->getHttpClient()->get($guildsUrl, [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]);

            return ['guilds' => json_decode($response->getBody(), true)];
        } else {
            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['username'],
            'email' => $user['email'],
            'avatar' => sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $user['id'], $user['avatar']),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        $token = Arr::get($response, 'access_token');

        $scopes = explode($this->scopeSeparator, Arr::get($response, 'scope', ''));

        $this->user = $this->mapUserToObject(array_merge($this->getUserByToken($token), $this->getGuildsByToken($scopes, $token)));

        return $this->user->setToken($token)
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'))
            ->setApprovedScopes($scopes);
    }
}
