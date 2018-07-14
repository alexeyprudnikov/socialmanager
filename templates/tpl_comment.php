<?php if(!$IsSubcomment): ?>

<?php else: ?>
- <br>
<?php endif; ?>
<img src="<?php echo $CommentData['userImage']; ?>" width="28" height="28"><br>
<a href="<?php echo $CommentData['userUrl']; ?>" target="_blank"><?php echo $CommentData['userName']; ?></a> <?php echo $CommentData['publishedAt'] ; ?><br>
<?php echo $CommentData['message'];?>
<?php
// subcomments
    if(isset($CommentData['replies']) && count($CommentData['replies']) > 0):
        foreach($CommentData['replies'] as $reply):
            // subcomments youtube
            if($Post->ChannelAccountType == SOCIALMEDIA_CHANNEL_TYPE_YOUTUBE):
                $snippet = $reply['snippet'];
                $commentData = array(
                    'userName' => $snippet['authorDisplayName'],
                    'userImage' => $snippet['authorProfileImageUrl'],
                    'userUrl' => $snippet['authorChannelUrl'],
                    'publishedAt' => $snippet['publishedAt'],
                    'message' => $snippet['textDisplay'],
                    'replies' => null,
                );
            // facebook
            elseif($Post->ChannelAccountType == SOCIALMEDIA_CHANNEL_TYPE_FACEBOOK):
                $snippet = $reply;
                $commentData = array(
                    'userName' => $snippet['from']['name'],
                    'userImage' => $snippet['from']['picture']['url'],
                    'userUrl' => 'https://www.facebook.com/'.$snippet['from']['id'],
                    'publishedAt' => $snippet['created_time']->getTimestamp(),
                    'message' => $snippet['message'],
                    'replies' => null,
                );
            endif;
            echo $this->rendern("tpl_comment.php", array('Post'=>$Post,'CommentData'=>$commentData,'IsSubcomment'=>true));
        endforeach;
    endif;
?>