<h3 class=""><?php echo Core::getTranslation('alle_posts'); ?></h3>
<div>
    <?php foreach ($ChannelCollection as $channel):
        $active = ($channel->Id == $ActiveChannel->Id) ? ' class="active"' : '';
        ?>
        <span<?php echo $active; ?>>
            <a class="Link" href="<?php echo $Base.'?cid='.$channel->Id; ?>"><?php echo $channel->Name; ?></a> |
        </span>
    <?php endforeach; ?>
    <hr>
</div>
<div>
    <?php if($PostCollection->getCount() > 0) {
        foreach($PostCollection as $Post) {
            echo $this->rendern('tpl_post.php', array('Post' => $Post, 'User' => $User));
        }
    } ?>
</div>
<div<?php if($PostCollection->getCount() > 0) echo ' style="display: none;"'; ?>>
    <?php echo Core::getTranslation('es_wurden_keine_posts_gefunden'); ?>
</div>

<script type="text/javascript">
    $( function() {

    });
</script>