<?php
echo 'Channel: '.ucfirst($Post->ChannelAccountName).'<br>';
echo 'Id: '.$Post->Id.'<br>';
echo 'SocialMediaId: '.$Post->SocialMediaId.'<br><br>';

echo $Post->getCreatorAvatar().' <a href="'.$Post->getCreatorProfileLink().'" target="_blank">'.$Post->getCreatorName().'</a><br>';
echo 'Created: '.$Post->CreateDate.'<br><br>';

echo $Post->getImage().'<br>';
echo '<a href="'.$Post->getUrl().'" target="_blank">'.$Post->Title.'</a><br>';
echo $Post->Description.'<br>';
echo 'Likes: '.$Post->LikeCount.', Views: '.$Post->ViewCount.'<br>';
echo 'Privacy: '.$Post->Privacy.'<br><br>';

echo 'Tags: '.(is_array($Post->Tags) ? implode(', ', $Post->Tags) : $Post->Tags).'<br><br>';

// Localizations
$channelAccountLanguages = $Post->ChannelAccountLanguages;
$hasLocalisations = ($Post->DefaultLanguage && is_array($Post->Localizations) && count($Post->Localizations) > 1);
if($hasLocalisations) {
    foreach($Post->Localizations as $lang=>$data) {
        if($lang == $Post->DefaultLanguage) continue;
        echo (isset($channelAccountLanguages[$lang]) ? $channelAccountLanguages[$lang] : $lang).':<br>';
        if (!empty($data["title"])) echo $data["title"].'<br>';
        if (!empty($data["description"])) echo $data["description"].'<br>';
    }
    echo '<br>';
}

// Comments
$hasComments = (is_array($Post->Comments) && count($Post->Comments) > 0);
if($hasComments) {
    foreach($Post->Comments as $cid=>$comment) {
        if($Post->ChannelAccountType == CHANNEL_TYPE_YOUTUBE):
            $snippet = $comment['snippet']['topLevelComment']['snippet'];
            $commentData = array(
                'userName' => $snippet['authorDisplayName'],
                'userImage' => $snippet['authorProfileImageUrl'],
                'userUrl' => $snippet['authorChannelUrl'],
                'publishedAt' => $snippet['publishedAt'],
                'message' => $snippet['textDisplay'],
                'replies' => isset($comment['replies']['comments']) ? $comment['replies']['comments'] : null,
            );
        // facebook
        elseif($Post->ChannelAccountType == CHANNEL_TYPE_FACEBOOK):
            $snippet = $comment;
            $commentData = array(
                'userName' => $snippet['from']['name'],
                'userImage' => $snippet['from']['picture']['url'],
                'userUrl' => 'https://www.facebook.com/'.$snippet['from']['id'],
                'publishedAt' => $snippet['created_time']->getTimestamp(),
                'message' => $snippet['message'],
                'replies' => isset($snippet['comments']) ? $snippet['comments'] : null,
            );
        endif;
        echo $this->rendern("tpl_comment.php", array('Post'=>$Post,'CommentData'=>$commentData,'IsSubcomment'=>false));
    }
}
?>
<hr>
