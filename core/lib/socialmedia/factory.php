<?php

/**
 * Class SocialMediaFactory
 * @author: alexeyprudnikov
 */
class SocialMediaFactory {
    private static $typeStr;
    private static $channelAuthData;

    /**
     * @param $channelAuthData
     *
     * @return mixed
     */
    public static function createYoutube($channelAuthData) {
        self::$typeStr = Core::getChannelTypeString(CHANNEL_TYPE_YOUTUBE);
        self::$channelAuthData = $channelAuthData;
        return self::loadChannelHandler();
    }

    /**
     * @param $channelAuthData
     *
     * @return mixed
     */
    public static function createFacebook($channelAuthData) {
        self::$typeStr = Core::getChannelTypeString(CHANNEL_TYPE_FACEBOOK);
        self::$channelAuthData = $channelAuthData;
        return self::loadChannelHandler();
    }

    /**
     * @param $channelAuthData
     *
     * @return mixed
     */
    public static function createTwitter($channelAuthData) {
        self::$typeStr = Core::getChannelTypeString(CHANNEL_TYPE_TWITTER);
        self::$channelAuthData = $channelAuthData;
        return self::loadChannelHandler();
    }

    /**
     * @param $channelAuthData
     *
     * @return mixed
     */
    public static function createVimeo($channelAuthData) {
        self::$typeStr = Core::getChannelTypeString(CHANNEL_TYPE_VIMEO);
        self::$channelAuthData = $channelAuthData;
        return self::loadChannelHandler();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private static function loadChannelHandler () {

        if(self::$typeStr == '') {
            throw new Exception('Invalid SocialMedia Type.');
        } else {

            $className = self::$typeStr.'Handler';

            if(class_exists($className)) {
                return new $className(self::$channelAuthData);
            } else {
                throw new Exception('SocialMedia type not found.');
            }
        }
    }
}