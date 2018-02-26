<div class="<?php if (!empty($contactsTop)) { ?>fixed-top<?php } else { ?>fixed-bottom<?php } ?>">
    <div class="contacts visible <?php if (!empty($contactsPermanent)) { ?>permanent<?php } ?>" id="contacts">
        <span class="contact"><a href="mailto:info@drdplus.info">info@drdplus.info</a></span>
        <span class="contact"><a target="_blank"
                                 href="https://rpgforum.cz/forum/viewtopic.php?f=238&t=14870">RPG fórum</a></span>
        <span class="contact"><a target="_blank" href="https://facebook.com/drdplus.info">Facebook</a></span>
    </div>
</div>
<?php if (!empty($contactsTop)) { ?>
    <div class="contacts-placeholder invisible">
        Placeholder for contacts
    </div>
<?php } ?>