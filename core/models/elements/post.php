<?php

/**
 * Class Post
 * @author: alexeyprudnikov
 */
class Post extends Element {
    public function getCreatorName() {return 'System';}
    public function getCreatorProfileLink() {return '#';}
    public function getCreatorAvatar() {
        return ($this->CreatorAvatar) ? '<img src="'.$this->CreatorAvatar.'" width="20" height="20">' : '';
    }
    public function getImage() {
        return ($this->Thumbnail) ? '<img src="'.$this->Thumbnail.'" width="100" height="100">' : '';
    }
    public function getUrl() { return Core::getBaseUrl().'#'.$this->SocialMediaId; }
}

class YoutubePost extends Post {
    public $ChannelAccountName = 'youtube';	// fallback
    public function getCreatorName() {return $this->ChannelTitle;}
    public function getCreatorProfileLink() {return 'https://www.youtube.com/channel/'.$this->ChannelId;}
    public function getUrl() { return 'https://youtu.be/'.$this->SocialMediaId; }
    public function getImage() {
        $thumbnail = ($this->Thumbnail) ? '<img src="'.$this->Thumbnail['url'].'" width="'.$this->Thumbnail['width'].'" height="'.$this->Thumbnail['height'].'">' : '';
        if ($thumbnail) {
            return '<a href="'.$this->getUrl().'">'.$thumbnail.'</a>';
        }
        return '';
    }
}

class FacebookPost extends Post {

}

class TwitterPost extends Post {

}

class VimeoPost extends Post {

}