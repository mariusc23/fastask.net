<?php if ($tasks): ?>
<div class="task-box" id="main">
    <div class="loading"></div>
    <h1 class="title"><a href="#p=1">my tasks</a></h1>
    <div class="task-table" cellspacing="0">
        <?php foreach ($tasks as $task): ?>
        <?php include('task_single.php'); ?>
        <?php endforeach; ?>
    </div>
    <?php print $pager; ?>
</div><!-- /.task-box -->
<?php endif; ?>