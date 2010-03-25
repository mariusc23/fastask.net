<div class="row <?php if ($task->status) { ?>done<?php } ?>">
<div class="td s">
    <input type="checkbox" name="status" <?php 
        if ($task->status) { ?>checked="checked"<?php }
    ?> class="md sh" />
</div>
<div class="td p pri_<?php print $task->priority ?>"> </div>
<div class="td text"><span class="editable"><?php print $task->text; ?></span></div>
<div class="td due"><span class="editable"><?php print $task->due; ?></span></div>
<div class="td followers"><span><?php //print $task->followers; ?></span></div>
<div class="td del">
    <a class="sh del-link" href="#"> </a>
    <input type="hidden" name="task_id" value="<?php print $task->id ?>" />
    <input type="hidden" name="user_id" value="<?php print $task->user_id ?>" />
</div>
</div>
