<?php include("project-header.php"); ?>

<h3>カテゴリリストを編集</h2>
<div class="table-form">
<form name="editCategory" id="category-edit" method="post"
 action="./?mode=writeCategoryList&module=edit-category-list&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>"
>
<table><tbody>
<tr><td colspan="2">
<textarea name="categoryList" class="large" ><?php echo htmlspecialchars($cicadaBtsProject->getCategoryListText()); ?></textarea>
</td></tr>
<tr><td colspan="2" class="submitarea">
<input type="submit" value="登録" />
</td></tr>
</tbody></table>
<input type="hidden" name="projectId" value="<?php echo $cicadaBtsProject->getProjectId(); ?>" />
</form>
</div>

<?php include("project-footer.php"); ?>
