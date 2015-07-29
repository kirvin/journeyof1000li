<form name="pip_postideas" method="post" action="<?php echo $url; ?>">
	<input type="hidden" name="pip_postidea_add" value="1" />
	<input type="hidden" name="pip_editid" value="<?php echo $edit_id; ?>" />
	<?php 
		if (function_exists('wp_nonce_field')){ 
			$content .= wp_nonce_field('pip_postidea_add');
		} 
	?>
	<table cellspacing="0">
		<tr>
			<td class="lbl"><label for="pip_addIdeaTitle">Title:</label></td>
			<td><div class="input-text-wrap"><input name="pip_addIdeaTitle" type="text" id="pip_addIdeaTitle" /></div></td>
		</tr>
		<tr>
			<td class="lbl"><label for="pip_addIdeaDescription">Description:</label></td>
			<td><div class="textarea-wrap"><textarea name="pip_addIdeaDescription" type="text" id="pip_addIdeaDescription"></textarea></div></td>
		</tr>
		<tr>
			<td class="lbl"><label for="pip_addIdeaKeywords">Tags:</label></td>
			<td><div class="input-text-wrap"><input name="pip_addIdeaKeywords" type="text" id="pip_addIdeaKeywords" value="" /></div></td>
		</tr>
		<tr>
			<td class="lbl"><label for="pip_addIdeaURLS">Links: <small>(comma seperated)</small></label></td>
			<td><div class="textarea-wrap"><textarea name="pip_addIdeaURLS" id="pip_addIdeaURLS"></textarea></div></td>
		</tr>
		<tr>
			<td class="lbl"><label for="pip_addIdeaPriority">Priority: <small>(1-99)</small></label></td>
			<td><div class="input-text-wrap priority-wrap"><input name="pip_addIdeaPriority" type="text" id="pip_addIdeaPriority" size="3" maxlength="2" /></div></td>
		</tr>
	</table>
	<a class="button-secondary" id="pAll" href="<?php echo $url; ?>" title="View all post ideas">View all</a><input class="button-primary" id="addIdeaSubmit" type="submit" name="submit_Add" value="Add Post Idea &raquo;" />
</form>