# Discord provider for Laravel Socialite
A provider for [Laravel Socialite][1] that allows authentication as a Discord user.
_Authenticating as a bot is currently not supported._

## Installation
```
composer require martinbean/socialite-discord-provider
```

## Usage
The package registers a Socialite driver with the name of `discord`.

Before using the driver, create an OAuth application in Discord’s developer portal:
https://discord.com/developers/applications

Set your client ID and client secret as environment variables, and then reference them in your **config/services.php** file.
You will also need to add a redirect URL to your application.

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

Then, create a controller to redirect and handle the token callback:

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

### Scopes
Discord supports various scopes. You can find a list here: https://discord.com/developers/docs/topics/oauth2#shared-resources-oauth2-scopes
To request additional scopes when authenticating, you can use the `scopes` method before redirecting:

```php
return Socialite::driver('discord')
    ->scopes(['guilds', 'messages.read'])
    ->redirect();
```

## Issues
If you have any problems using this package, please open an issue on the [GitHub repository][2].

[1]: https://laravel.com/docs/master/socialite
[2]: https://github.com/martinbean/socialite-discord-provider
