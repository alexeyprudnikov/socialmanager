<?php

/**
 * Class SocialMediaConnector
 * @author: alexeyprudnikov
 */
class SocialMediaConnector {

    /**
     * @param $ClientId
     * @param $ClientSecret
     * @param string $redirect_uri
     * @param string $scope
     * @return Google_Client
     */
    public static function getGoogleClient($ClientId, $ClientSecret) {
        require_once 'core/lib/ext/google-api/vendor/autoload.php';

        $googleClient = new Google_Client();

        /* check proxy */
        if(Core::getProxy() !== false) {
            $httpClient = new GuzzleHttp\Client([
                'proxy' => Core::getProxy(),
                'verify' => false,
            ]);
            $googleClient->setHttpClient($httpClient);
        }
        /**/

        $googleClient->setClientId($ClientId);
        $googleClient->setClientSecret($ClientSecret);

        return $googleClient;
    }

    /**
     * @param $ClientId
     * @param $ClientSecret
     * @return \Facebook\Facebook
     */
    public static function getFacebookClient($ClientId, $ClientSecret) {
        require_once 'core/lib/ext/facebook-sdk/autoload.php';

        $fbClient = new \Facebook\Facebook([
            'app_id' => $ClientId,
            'app_secret' => $ClientSecret,
            'default_graph_version' => 'v2.9'
        ]);

        return $fbClient;
    }

    /**
     * @param $ClientId
     * @param $ClientSecret
     * @param null $OauthToken
     * @param null $OauthTokenSecret
     * @return \Abraham\TwitterOAuth\TwitterOAuth
     */
    public static function getTwitterClient($ClientId, $ClientSecret, $OauthToken = null, $OauthTokenSecret = null) {
        require_once 'core/lib/ext/twitteroauth/autoload.php';

        $twitterClient = new \Abraham\TwitterOAuth\TwitterOAuth(
            $ClientId,
            $ClientSecret,
            $OauthToken,
            $OauthTokenSecret
        );

        return $twitterClient;
    }

    /**
     * @param $ClientId
     * @param $ClientSecret
     * @return \Vimeo\Vimeo
     */
    public static function getVimeoClient($ClientId, $ClientSecret) {
        require_once 'core/lib/ext/vimeo-sdk/autoload.php';

        $vimeoClient = new \Vimeo\Vimeo(
            $ClientId,
            $ClientSecret
        );

        return $vimeoClient;
    }

}
