<?php

/**
 * Class HandlerInterface
 * @author: alexeyprudnikov
 */
interface HandlerInterface {
    public function init();
    public function listPosts($loadMoreIdentifier, $limit);
    public function insertPost($data);
    public function updatePost($id, $data);
    public function deletePost($id);
    public function getLanguages();
}

class YoutubeHandler implements HandlerInterface {

    protected $channelAuthData;

    protected $googleClient;
    protected $serviceYoutube;
    protected $lastError;

    protected static $defaultLanguage = "de";

    public function getLastError() {
        return $this->lastError;
    }

    public function __construct($channelAuthData) {
        $this->channelAuthData = $channelAuthData;
    }

    /**
     * @return bool
     */
    public function init() {

        $ChannelId = $this->channelAuthData['Id'];
        $ClientId = $this->channelAuthData['ClientId'];
        $ClientSecret = $this->channelAuthData['ClientSecret'];
        $AccessToken = $this->channelAuthData['AccessToken'];

        if(empty($ChannelId) || empty($ClientId) || empty($ClientSecret) || empty($AccessToken)) {
            $this->lastError = 'Youtube Auth-Daten nicht vollständig';
            return false;
        }

        $this->googleClient = SocialMediaConnector::getGoogleClient($ClientId, $ClientSecret);

        // set access token
        $this->googleClient->setAccessToken($AccessToken);

        // access token expired? refresh!
        if($this->googleClient->isAccessTokenExpired()) {
            // get new token
            $this->googleClient->refreshToken($this->googleClient->getRefreshToken());
            $newAccessToken = $this->googleClient->getAccessToken();
            $SocialMediaChannel = ChannelFinder::findById($ChannelId);
            if($SocialMediaChannel instanceof YoutubeChannel) {
                $SocialMediaChannel->setAccessToken($newAccessToken);
                // write new acces token to db
                #SocialMediaChannelManager::update($SocialMediaChannel);
            }
        }

        $this->serviceYoutube = new Google_Service_YouTube($this->googleClient);
        return true;
    }

    /**
     * @param $loadMoreIdentifier
     * @param $limit
     * @return array
     */
    public function listPosts($loadMoreIdentifier, $limit) {

        $items = array();
        $nextPageIdentifier = '';

        if ($this->googleClient->getAccessToken()) {
            try {
                // Call the channels.list method to retrieve information about the
                // currently authenticated user's channel.
                $channelsResponse = $this->serviceYoutube->channels->listChannels('contentDetails,snippet', array(
                    'mine' => 'true',
                ));

                if (empty($channelsResponse['items'])) {
                    return array($items, $nextPageIdentifier);
                }
                $index = 1;
                foreach ($channelsResponse['items'] as $channel) {

                    $channelId = $channel['id'];
                    $channelTitle = $channel['snippet']['title'];

                    // Extract the unique playlist ID that identifies the list of videos
                    // uploaded to the channel, and then call the playlistItems.list method
                    // to retrieve that list.
                    $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];
                    $requestData = array(
                        'playlistId' => $uploadsListId,
                        'maxResults' => $limit
                    );
                    if(!empty($loadMoreIdentifier)) {
                        $requestData['pageToken'] = $loadMoreIdentifier;
                    }
                    $playlistItemsResponse = $this->serviceYoutube->playlistItems->listPlaylistItems('snippet', $requestData);
                    #print_r($playlistItemsResponse);
                    if (empty($playlistItemsResponse['items'])) {
                        continue;
                    }

                    if(array_key_exists('nextPageToken', $playlistItemsResponse))
                        $nextPageIdentifier = $playlistItemsResponse['nextPageToken'];

                    $avatar = null;
                    if (isset($channel['snippet']['thumbnails']['default'])) {
                        $avatar =$channel['snippet']['thumbnails']['default']['url'];
                    }

                    # Merge video ids und get Statistics (Likes, Views...)
                    $videoResults = array();
                    foreach ($playlistItemsResponse['items'] as $item) {
                        array_push($videoResults, $item['snippet']['resourceId']['videoId']);
                    }
                    $videoIds = join(',', $videoResults);
                    $videoData = $this->serviceYoutube->videos->listVideos('snippet,statistics,status,localizations', array(
                        'id' => $videoIds,
                    ));
                    #print_r($videoData);
                    foreach ($playlistItemsResponse['items'] as $playlistItem) {
                        $videoId = $playlistItem['snippet']['resourceId']['videoId'];
                        // statistics - likes, views...
                        foreach($videoData['items'] as $vd) {
                            if($vd['id'] == $videoId) {
                                $itemStat = $vd['statistics'];
                                $tags = $vd['snippet']['tags'];
                                $localizations = $vd['localizations'];
                                $defaultLanguage = $vd['snippet']['defaultLanguage'];
                                $status = $vd['status'];
                                try {
                                    $comments = $this->serviceYoutube->commentThreads->listCommentThreads('snippet,replies', array(
                                        'videoId' => $videoId,
                                        'textFormat' => 'plainText'
                                    ));
                                } catch (Google_Service_Exception $e) {
                                    $comments = false;
                                    $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                                        htmlspecialchars($e->getMessage()));
                                }
                                break;
                            }
                        }
                        $t = array(
                            'id'=>$index,
                            'socialMediaId'   => $videoId,
                            'title' => $playlistItem['snippet']['title'],
                            'description'  => $playlistItem['snippet']['description'],
                            'localizations' => isset($localizations) ? $localizations : array(),
                            'defaultLanguage' => isset($defaultLanguage) ? $defaultLanguage : null,
                            'likeCount' => isset($itemStat) ? $itemStat['likeCount'] : 0,
                            'viewCount' => isset($itemStat) ? $itemStat['viewCount'] : 0,
                            'tags' => isset($tags) ? $tags : array(),
                            'privacy' => isset($status) ? $status['privacyStatus'] : null,
                            'thumbnail' => isset($playlistItem['snippet']['thumbnails']['default']) ? $playlistItem['snippet']['thumbnails']['default'] : null,
                            'createDate' => $playlistItem['snippet']['publishedAt'],
                            'channelTitle' => $channelTitle,
                            'channelId' => $channelId,
                            'creatorAvatar' => $avatar,
                            'comments' => isset($comments) ? $comments['items'] : array(),
                        );

                        $items[] = $t;
                        $index++;
                    }
                }
            } catch (Google_Service_Exception $e) {
                $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                $this->lastError = sprintf('<p>An client error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            }
        }

        return array($items, $nextPageIdentifier);
    }

    /**
     * @param $data
     * @return array
     */
    public function insertPost($data) {
        $item = array();
        if ($this->googleClient->getAccessToken()) {
            try {
                $item = $this->setVideoDataAndUpload($data);
            } catch (Google_Service_Exception $e) {
                $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                $this->lastError = sprintf('<p>An client error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            }
        }
        #echo $this->lastError;
        return $item;
    }

    /**
     * @param $data
     * @return array
     */
    protected function setVideoDataAndUpload($data) {

        if(empty($data['path']) || empty($data['title'])) return array();

        $videoPath = $data['path'];

        // Create a snippet with title, description
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($data['title']);
        $snippet->setDescription($data['description']);
        $snippet->setTags($data['tags']);

        // Set the video's status to "public". Valid statuses are "public",
        // "private" and "unlisted".
        $status = new Google_Service_YouTube_VideoStatus();
        $status->privacyStatus = (!empty($data['privacy'])) ? $data['privacy'] : 'private';

        // Associate the snippet and status objects with a new video resource.
        $video = new Google_Service_YouTube_Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        // Specify the size of each chunk of data, in bytes. Set a higher value for
        // reliable connection as fewer chunks lead to faster uploads. Set a lower
        // value for better recovery on less reliable connections.
        $chunkSizeBytes = 1 * 1024 * 1024;

        // Setting the defer flag to true tells the client to return a request which can be called
        // with ->execute(); instead of making the API call immediately.
        $this->googleClient->setDefer(true);

        // Create a request for the API's videos.insert method to create and upload the video.
        $insertRequest = $this->serviceYoutube->videos->insert("status,snippet", $video);

        // Create a MediaFileUpload object for resumable uploads.
        $media = new Google_Http_MediaFileUpload(
            $this->googleClient,
            $insertRequest,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($videoPath));

        // Read the media file and upload it chunk by chunk.
        $uploadStatus = false;
        $handle = fopen($videoPath, "rb");
        while (!$uploadStatus && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $uploadStatus = $media->nextChunk($chunk);
        }

        fclose($handle);

        // If you want to make other calls after the file upload, set setDefer back to false
        $this->googleClient->setDefer(false);

        //
        $videoId = $uploadStatus['id'];

        // upload thumbnail
        try {
            $this->uploadSetThumbnail($videoId, $data['thumbnail']);
        } catch (Google_Service_Exception $e) {
            $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        } catch (Google_Exception $e) {
            $this->lastError = sprintf('<p>An client error occurred: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        }

        $item = array(
            'socialMediaId'   => $videoId
        );
        return $item;
    }

    /**
     * @param string $videoId
     * @param string $imagePath
     * @return null
     */
    protected function uploadSetThumbnail($videoId = '', $imagePath = '') {

        if(empty($videoId) || empty($imagePath)) return;

        $chunkSizeBytes = 1 * 1024 * 1024;
        $this->googleClient->setDefer(true);
        $setRequest = $this->serviceYoutube->thumbnails->set($videoId);

        // Create a MediaFileUpload object for resumable uploads.
        $media = new Google_Http_MediaFileUpload(
            $this->googleClient,
            $setRequest,
            'application/octet-stream',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($imagePath));

        // Read the media file and upload it chunk by chunk.
        $status = false;
        $handle = fopen($imagePath, "rb");
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        fclose($handle);

        // If you want to make other calls after the file upload, set setDefer back to false
        $this->googleClient->setDefer(false);

        //not needed by output
        //$thumbnail = $status['items'][0]['default'];
        return;
    }

    /**
     * @param $id
     * @param $data
     * @return array
     */
    public function updatePost($id, $data) {
        $item = array();
        if ($this->googleClient->getAccessToken()) {
            try {
                $item = $this->setVideoData($id, $data);
            } catch (Google_Service_Exception $e) {
                $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                $this->lastError = sprintf('<p>An client error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            }
        }
        return $item;
    }

    /**
     * @param $videoId
     * @param $data
     * @return array
     */
    protected function setVideoData($videoId, $data) {
        // siehe https://developers.google.com/youtube/v3/docs/videos/update
        $videos = $this->serviceYoutube->videos->listVideos("status,snippet,localizations", array(
            'id' => $videoId
        ));
        if (empty($videos)) {
            $this->lastError = "Video mit der ID: ".$videoId." kann nicht gefunden werden";
        } else {
            // Since the request specified a video ID, the response only
            // contains one video resource.
            $originalVideo = $videos[0];
            // set new data
            if (!empty($data['title'])) {
                $originalVideo['snippet']['title'] = $data['title'];
            }
            if (!empty($data['description'])) {
                $originalVideo['snippet']['description'] = $data['description'];
            }
            if (!empty($data['tags'])) {
                $originalVideo['snippet']['tags'] = $data['tags'];
            }
            if (!empty($data['privacy'])) {
                $originalVideo['status']['privacyStatus'] = $data['privacy'];
            }
            if (!empty($data['localization'])) {
                // set default localization, need if other should be removed
                if(empty($originalVideo['snippet']['defaultLanguage'])) {
                    $originalVideo['snippet']['defaultLanguage'] = self::$defaultLanguage;
                }
                $checkedLocalization = array(
                    $originalVideo['snippet']['defaultLanguage'] => array(
                        'title' => $data['title'],
                        'description' => $data['description']
                    )
                );
                // check setted localizations
                foreach($data['localization'] as $lang=>$loc) {
                    if(!empty($loc['title']) OR !empty($loc['description'])) {
                        $checkedLocalization[$lang] = $loc;
                    }
                }
                $originalVideo['localizations'] = $checkedLocalization;
            }

            // Call the YouTube Data API's videos.update method to update an existing video.
            $videoUpdateResponse = $this->serviceYoutube->videos->update("status,snippet,localizations", $originalVideo);

            // read updated data
            $title = $videoUpdateResponse['snippet']['title'];
            $description = $videoUpdateResponse['snippet']['description'];
            $tags = $videoUpdateResponse['snippet']['tags'];
            $privacy = $videoUpdateResponse['status']['privacyStatus'];
            $localizations = $videoUpdateResponse['localizations'];
            $defaultLanguage = $videoUpdateResponse['snippet']['defaultLanguage'];

            // comments
            try {
                $comments = $this->serviceYoutube->commentThreads->listCommentThreads('snippet,replies', array(
                    'videoId' => $videoId,
                    'textFormat' => 'plainText'
                ));
            } catch (Google_Service_Exception $e) {
                $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            }

            $item = array(
                'socialMediaId'   => $videoId,
                'title' => $title,
                'description'  => $description,
                'localizations' => $localizations,
                'defaultLanguage' => $defaultLanguage,
                'tags'  => $tags,
                'privacy' => $privacy,
                'thumbnail' => isset($videoUpdateResponse['snippet']['thumbnails']['default']) ? $videoUpdateResponse['snippet']['thumbnails']['default'] : null,
                'comments' => isset($comments) ? $comments['items'] : null,
            );
            return $item;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function deletePost($id) {
        $response = false;
        if ($this->googleClient->getAccessToken()) {
            try {
                $response = $this->serviceYoutube->videos->delete($id);
            } catch (Google_Service_Exception $e) {
                $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                $this->lastError = sprintf('<p>An client error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            }
        }
        return $response;
    }

    /**
     * @return array
     */
    public function getLanguages() {
        $languages = array();
        if ($this->googleClient->getAccessToken()) {
            try {
                $response = $this->serviceYoutube->i18nLanguages->listI18nLanguages(
                    'snippet',
                    array('hl'=>Core::getInstance()->getSystemLanguage())
                );
            } catch (Google_Service_Exception $e) {
                $this->lastError = sprintf('<p>A service error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                $this->lastError = sprintf('<p>An client error occurred: <code>%s</code></p>',
                    htmlspecialchars($e->getMessage()));
            }
        }
        foreach($response as $r) {
            $languages[$r['snippet']['hl']] = $r['snippet']['name'];
        }
        // sorting by name
        asort($languages);
        return $languages;
    }
}
class FacebookHandler implements HandlerInterface {
    protected $channelAuthData;

    protected $fbClient;
    protected $lastError;

    public function getLastError() {
        return $this->lastError;
    }
    public function __construct($channelAuthData) {
        $this->channelAuthData = $channelAuthData;
    }

    /**
     * @return bool
     */
    public function init() {

        $ChannelId = $this->channelAuthData['Id'];
        $ClientId = $this->channelAuthData['ClientId'];
        $ClientSecret = $this->channelAuthData['ClientSecret'];
        $AccessToken = $this->channelAuthData['AccessToken'];

        if(empty($ChannelId) || empty($ClientId) || empty($ClientSecret) || empty($AccessToken)) {
            $this->lastError = 'Facebook Auth-Daten nicht vollständig';
            return false;
        }

        $this->fbClient = SocialMediaConnector::getFacebookClient($ClientId, $ClientSecret);

        $this->fbClient->setDefaultAccessToken($AccessToken);

        return true;
    }

    /**
     * @param $loadMoreIdentifier
     * @param $limit
     * @return array
     */
    public function listPosts($loadMoreIdentifier, $limit) {

        $offset = !empty($loadMoreIdentifier) ? $loadMoreIdentifier : 0;

        $items = array();
        $nextPageIdentifier = '';

        // get data
        try {
            // User Data
            $response = $this->fbClient->get('/me?fields=id,name,picture.width(100).height(100)');
            $graphObject = $response->getGraphObject();
            $userData = $graphObject->asArray();
            $channelTitle = $userData['name'];
            $channelId = $userData['id']; // NOTICE! this is app_scoped_id, besser to find real ID or username
            $avatar = isset($userData['picture']['url']) ? $userData['picture']['url'] : null;
            // Requires the "user_posts" permission
            $response = $this->fbClient->get('/me/posts?fields=id,message,story,created_time,object_id,permalink_url,privacy&limit='.$limit.'&offset='.$offset);
            $feedEdge = $response->getGraphEdge();

            // paginierung
            $nextFeed = $this->fbClient->next($feedEdge);
            if(!is_null($nextFeed)) {
                $nextPageIdentifier = $offset+$limit;
            }

            $index = 1;
            foreach ($feedEdge as $item) {
                $itemData = $item->asArray();
                #print_r($itemData);
                // get photo to post
                $photo = null;
                if(isset($itemData['object_id'])) {
                    // picture, comments + subcomments (format as comments)
                    $response = $this->fbClient->get('/'.$itemData['object_id'].'?fields=picture,comments.order(reverse_chronological){created_time,from{name,id,picture.width(32).height(32)},message,id,comments{created_time,from{name,id,picture.width(32).height(32)},message,id}}');
                    $graphObject = $response->getGraphObject();
                    $objectData = $graphObject->asArray();
                    #print_r($objectData);
                    $photo = $objectData['picture'];
                    $comments = $objectData['comments'];
                }
                $t = array(
                    'id'=>$index,
                    'socialMediaId'   => $itemData['id'],
                    'createDate' => $itemData['created_time']->getTimestamp(),	// php DateTime Object
                    'title' => '',
                    'description'  => isset($itemData['message']) ? $itemData['message'] : (isset($itemData['story']) ? $itemData['story'] : ''),
                    'thumbnail' => $photo,
                    'channelTitle' => $channelTitle,
                    'channelId' => $channelId,
                    'creatorAvatar' => $avatar,
                    'permalinkUrl' => $itemData['permalink_url'],
                    'privacy' => isset($itemData['privacy']['value']) ? $itemData['privacy']['value'] : null,
                    'comments' => isset($comments) ? $comments : array(),
                );

                $items[] = $t;
                $index++;
            }

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        return array($items, $nextPageIdentifier);
    }
    public function insertPost($data) {
        $item = $this->setDataAndPublish($data);
        return $item;
    }
    /**
     * @param $data
     * @return array
     */
    protected function setDataAndPublish($data) {

        if (empty($data['description'])) return array();

        $privacy = !empty($data['privacy']) ? $data['privacy'] : 'EVERYONE';

        $post = array(
            'message' => $data['description'],
            'privacy' => array('value'=>$privacy)
        );

        //add media upload, lock at attachment_url, media_ids by POST statuses/update

        try {
            $response = $this->fbClient->post('/me/feed', $post);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $itemData = $response->getGraphNode()->asArray();

        $item = array(
            'socialMediaId'   => $itemData['id']
        );

        return $item;
    }
    public function updatePost($id, $data) {}
    public function deletePost($id) {}
    public function getLanguages() {return array();}
}

class TwitterHandler implements HandlerInterface {
    protected $channelAuthData;

    protected $twitterClient;
    protected $lastError;

    public function getLastError() {
        return $this->lastError;
    }
    public function __construct($channelAuthData) {
        $this->channelAuthData = $channelAuthData;
    }

    /**
     * @return bool
     */
    public function init() {

        $ChannelId = $this->channelAuthData['Id'];
        $ClientId = $this->channelAuthData['ClientId'];
        $ClientSecret = $this->channelAuthData['ClientSecret'];
        $AccessToken = $this->channelAuthData['AccessToken'];

        if(empty($ChannelId) || empty($ClientId) || empty($ClientSecret) || empty($AccessToken)) {
            $this->lastError = 'Twitter Auth-Daten nicht vollständig';
            return false;
        }

        $this->twitterClient = SocialMediaConnector::getTwitterClient($ClientId, $ClientSecret, $AccessToken['oauth_token'], $AccessToken['oauth_token_secret']);

        return true;
    }

    /**
     * @param $loadMoreIdentifier
     * @param $limit
     * @return array
     */
    public function listPosts($loadMoreIdentifier, $limit) {

        $items = array();
        $nextPageIdentifier = '';

        // get data
        try {
            // User Data
            $userData = $this->twitterClient->get('account/verify_credentials', array());

            $userId = $userData->id;
            $channelTitle = $userData->name;
            $channelId = $userData->screen_name;
            $avatar = isset($userData->profile_image_url) ? $userData->profile_image_url : null;

            $filter = array("user_id" => $userId, "count" => $limit, "exclude_replies" => true, "trim_user" => true);
            if(!empty($loadMoreIdentifier)) {
                $filter['max_id'] = (int)$loadMoreIdentifier - 1;
            }
            $feed = $this->twitterClient->get('statuses/user_timeline', $filter);

            $index = 1;
            foreach ($feed as $itemData) {
                #print_r($itemData);
                $t = array(
                    'id'=>$index,
                    'socialMediaId'   => $itemData->id,
                    'createDate' => $itemData->created_at,
                    'description'  => $itemData->text,
                    'likeCount' => $itemData->favorite_count,
                    'shareCount' => $itemData->retweet_count,
                    'tags' => $itemData->entities->hashtags,
                    'thumbnail' => null,	// todo: get image from post
                    'channelTitle' => $channelTitle,
                    'channelId' => $channelId,
                    'creatorAvatar' => $avatar,
                    'comments' => array(), // todo: read comments
                );

                $items[] = $t;
                $index++;

                // paginierung
                $nextPageIdentifier = $itemData->id; // oldest id in set, cause order from new to old
            }

        } catch(Exception $e) {
            echo 'Twitter API error: ' . $e->getMessage();
            exit;
        }

        return array($items, $nextPageIdentifier);
    }

    /**
     * @param $data
     * @return array
     */
    public function insertPost($data) {
        $item = $this->setDataAndPublish($data);
        return $item;
    }

    /**
     * @param $data
     * @return array
     */
    protected function setDataAndPublish($data) {

        if (empty($data['description'])) return array();

        $tweet = array(
            'status' => $data['description']
        );

        //add media upload, lock at attachment_url, media_ids by POST statuses/update

        $response = $this->twitterClient->post('statuses/update', $tweet);

        if(isset($response->error)) {
            echo 'Twitter error: ' . $response->error;
            exit;
        }

        if(isset($response->errors)) {
            $errors = array_map(function($e){return $e->message;}, $response->errors);
            echo 'Twitter errors: '.implode(', ', $errors);
            exit;
        }

        $item = array(
            'socialMediaId'   => $response->id
        );

        return $item;
    }

    public function updatePost($id, $data) {}
    public function deletePost($id) {}
    public function getLanguages() {return array();}
}

class VimeoHandler implements HandlerInterface {
    protected $channelAuthData;

    protected $vimeoClient;
    protected $lastError;

    public function getLastError() {
        return $this->lastError;
    }
    public function __construct($channelAuthData) {
        $this->channelAuthData = $channelAuthData;
    }

    /**
     * @return bool
     */
    public function init() {

        $ChannelId = $this->channelAuthData['Id'];
        $ClientId = $this->channelAuthData['ClientId'];
        $ClientSecret = $this->channelAuthData['ClientSecret'];
        $AccessToken = $this->channelAuthData['AccessToken'];

        if(empty($ChannelId) || empty($ClientId) || empty($ClientSecret) || empty($AccessToken)) {
            $this->lastError = 'Vimeo Auth-Daten nicht vollständig';
            return false;
        }

        $this->vimeoClient = SocialMediaConnector::getVimeoClient($ClientId, $ClientSecret);

        // set access token
        $this->vimeoClient->setToken($AccessToken);

        return true;
    }

    /**
     * @param $loadMoreIdentifier
     * @param $limit
     * @return array
     */
    public function listPosts($loadMoreIdentifier, $limit) {

        $page = !empty($loadMoreIdentifier) ? $loadMoreIdentifier : 1;

        $items = array();
        $nextPageIdentifier = '';

        // get data
        try {
            $response = $this->vimeoClient->request('/me/videos', array('page' => $page, 'per_page' => $limit));
            $feed = $response['body'];

            if(!empty($feed['error']))
                throw new Exception($feed['error'].'<br>'.$feed['developer_message']);

            if (empty($feed['data'])) {
                return array($items, $nextPageIdentifier);
            }
            $index = 1;
            foreach ($feed['data'] as $itemData) {

                $userData = $itemData['user'];
                $channelTitle = $userData['name'];
                $channelId = $userData['uri'];
                $channelUrl = $userData['link'];
                // 100x100px
                $avatar = isset($userData['pictures']['sizes'][2]['link']) ? $userData['pictures']['sizes'][2]['link'] : null;

                $tags = array_map(function($t){return $t['name'];}, $itemData['tags']);
                $t = array(
                    'id'=>$index,
                    'socialMediaId'   => $itemData['uri'],
                    'createDate' => $itemData['created_time'],
                    'title'  => $itemData['name'],
                    'description'  => $itemData['description'],
                    'tags' => $tags,
                    'thumbnail' => isset($itemData['pictures']['sizes'][0]) ? $itemData['pictures']['sizes'][0] : null,
                    'channelTitle' => $channelTitle,
                    'channelId' => $channelId,
                    'channelUrl' => $channelUrl,
                    'creatorAvatar' => $avatar,
                    'permalinkUrl' => $itemData['link'],
                    'privacy' => isset($itemData['privacy']['view']) ? $itemData['privacy']['view'] : null,
                    'comments' => array(), // todo: read comments
                );

                $items[] = $t;
                $index++;
            }

        } catch(Exception $e) {
            echo 'Vimeo API error: ' . $e->getMessage();
            exit;
        }

        if($index == $limit) $nextPageIdentifier = $page + 1;

        return array($items, $nextPageIdentifier);
    }

    public function insertPost($data) {}
    public function updatePost($id, $data) {}
    public function deletePost($id) {}
    public function getLanguages() {return array();}
}
