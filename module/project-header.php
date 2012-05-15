<h2 id="project-title">
<a href="./?module=project-top&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">
<?php echo $cicadaBtsProject->getProjectName(); ?>
</a></h2>

<div class="main-header-links">
<a href="./?module=new-ticket&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">新規チケット</a>
<a href="./?module=ticket-list&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">全チケットリスト</a>
<a href="./?module=project-bbs&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">プロジェクト掲示板</a>
</div>
