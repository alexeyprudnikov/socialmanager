<?php
/**
 * Class Autoloader
 * @author: alexeyprudnikov
 */
spl_autoload_register('Autoloader::loader');

class Autoloader {

    // className => filePath from core/
    protected static $files = array(
        'Core' => 'controllers/core.php',
        'Output' => 'controllers/output.php',

        'Instance' => 'lib/instance.php',
        'Database' => 'lib/database.php',
        'Request' => 'lib/request.php',
        'Session' => 'lib/session.php',
        'Template' => 'lib/template.php',
        'Utility' => 'lib/utility.php',

        'SocialMediaConnector' => 'lib/socialmedia/connector.php',
        'SocialMediaFactory' => 'lib/socialmedia/factory.php',
        'YoutubeHandler' => 'lib/socialmedia/handler.php',
        'FacebookHandler' => 'lib/socialmedia/handler.php',
        'TwitterHandler' => 'lib/socialmedia/handler.php',
        'VimeoHandler' => 'lib/socialmedia/handler.php',

        'Collection' => 'models/collection.php',
        'ChannelCollection' => 'models/collections/channels.php',
        'PostCollection' => 'models/collections/posts.php',
        'UserCollection' => 'models/collections/users.php',

        'Element' => 'models/element.php',
        'Channel' => 'models/elements/channel.php',
        'YoutubeChannel' => 'models/elements/channel.php',
        'Post' => 'models/elements/post.php',
        'YoutubePost' => 'models/elements/post.php',
        'FacebookPost' => 'models/elements/post.php',
        'TwitterPost' => 'models/elements/post.php',
        'VimeoPost' => 'models/elements/post.php',
        'User' => 'models/elements/user.php',

        'Finder' => 'models/finder.php',
        'ChannelFinder' => 'models/orm/finders/channel.php',
        'UserFinder' => 'models/orm/finders/user.php',
    );

    public static function loader($className) {
        // todo: autoload classNames with Namespaces
        if(!array_key_exists($className, self::$files)) return false;
        $filePath = "core/" . self::$files[$className];
        if (file_exists($filePath)) {
            require_once($filePath);
            if (class_exists($className)) {
                return true;
            }
        }
        return false;
    }
}