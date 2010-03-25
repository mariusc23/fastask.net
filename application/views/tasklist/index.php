<div class="task-box" id="main">
    <h1 class="title">my tasks</h1>
    <div class="task-table" cellspacing="0">
        <?php foreach ($tasks as $task): ?>
        <?php include('task_single.php'); ?>
        <?php endforeach; ?>
    </div>
    <?php print $pager; ?>
</div><!-- /.task-box -->
