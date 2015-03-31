<?php

namespace Lorry\Override;

class Facebook extends \League\OAuth2\Client\Provider\Facebook
{

    public function urlAuthorize()
    {
        return 'https://www.facebook.com/v2.2/dialog/oauth';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://graph.facebook.com/v2.2/me?access_token='.$token;
    }
}
