<?php

/**
 * Class PostCollection
 * @author: alexeyprudnikov
 */
class PostCollection extends Collection {
	protected $ObjectClassName = 'Post';

	protected $LoadMoreIdentifier;

    public function setLoadMoreIdentifier($value) {
        $this->LoadMoreIdentifier = $value;
    }

    public function getLoadMoreIdentifier() {
        return $this->LoadMoreIdentifier;
    }

    public function prepareCollection(Channel $channel, $lastId = 0) {
        foreach ($this->Elements as $element) {
            $this->increaseElementId($element, $lastId);
            $this->addChannelData($element, $channel);
        }
    }

    protected function increaseElementId($element, $lastId) {
        $element->Id += $lastId;
    }

    protected function addChannelData($element, $channel) {
        $element->ChannelAccountId = $channel->Id;
        $element->ChannelAccountType = $channel->Type;
        #if(!empty($channel->Name)) {
            $element->ChannelAccountName = $channel->Name;
        #}
        $languages = $channel->getLanguages();
        $element->ChannelAccountLanguages = is_array($languages) ? $languages : array($languages);
    }
}