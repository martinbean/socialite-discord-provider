# Discord provider for Laravel Socialite
A provider for [Laravel Socialite][1] that allows authentication as a Discord user or bot.

## Installation
```
composer require martinbean/socialite-discord-provider:^1.2
```

## Usage
The package registers a Socialite driver with the name of `discord`.

Before using the driver, create an OAuth application in Discord’s developer portal:
https://discord.com/developers/applications

Set your client ID and client secret as environment variables, and then reference them in your **config/services.php** file. You will also need to add a redirect URL to your application if you intend to authenticate as a user.

```php
<?php

// config/services.php

return [

    // Any other services

    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => '/auth/discord/callback',
    ],

];
```

The `redirect` value will need to match a redirect URL in your Discord application settings. It can be relative as above.

### Authenticating as a user
Create a controller to redirect and handle the access token callback:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * Redirect the user to the Discord authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('discord')->redirect();
    }

    /**
     * Obtain the user information from Discord.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('discord')->user();

        // $user->token;
    }
}
```

#### Scopes
Discord supports various scopes when authenticating as a user. You can find a list here: https://discord.com/developers/docs/topics/oauth2#shared-resources-oauth2-scopes

To request additional scopes when authenticating, you can use the `scopes` method before redirecting:

```php
return Socialite::driver('discord')
    ->scopes(['guilds', 'messages.read'])
    ->redirect();
```

### Authenticating as a bot
Discord allows you to add “bots” to guilds (servers). This is a modified OAuth flow, where you are redirected to Discord to confirm the guild you wish to add a bot to. There is no redirect back to your application when you authorize the request.

You can authenticate as a bot by using the `bot` method before redirecting:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * Redirect the user to the Discord authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('discord')->bot()->redirect();
    }
}
```

If you know the guild ID you wish to add your bot to, you may specify it with the `guild` method:

```php
return Socialite::driver('discord')
    ->bot()
    ->guild($guildId)
    ->redirect();
```

Additionally, you can disable the guild select:

```php
return Socialite::driver('discord')
    ->bot()
    ->guild($guildId)
    ->disableGuildSelect()
    ->redirect();
```

**Note:** if you try and disable guild selection without specifying a guild, the package will throw a `GuildRequiredException` instance.

## Issues
If you have any problems using this package, please open an issue on the [GitHub repository][2].

[1]: https://laravel.com/docs/master/socialite
[2]: https://github.com/martinbean/socialite-discord-provider
