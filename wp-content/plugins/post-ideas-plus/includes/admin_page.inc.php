<div class='wrap' id="postIdeasContainer">
	<div id="icon-edit" class="icon32">&nbsp;</div>
	<h2>Post Ideas+ v2.1.0.5</h2>
	<?php echo $update_fade; ?>
	
	<div class="postbox-container">
		<div class="metabox-holder">	
			<div class="meta-box-sortables ui-sortable">
				<form name="pip_settings" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=postideas.php&amp;updated=true">
					<?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('pip_postidea_settings'); ?>
					
					<div class="postbox" id="piSettings">
						<div title="Click to toggle" class="handlediv"><br></div>
						<h3 class="hndle">
							<span>Post Idea+ Settings</span>
						</h3>
						<div class="inside">
							<ul>
								<li>
									<strong>Latest Post Ideas Dashboard Widget</strong>
								</li>
								<li>
									<label for="pip_numberRows">Number of rows:<small>(default: 5)</small></label>
									<input name="pip_numberRows" type="text" id="pip_numberRows" value="<?php echo $numberRows; ?>" size="3" />
								</li>
								<li>
									<label for="pip_dashSort">Sort dashboard widget by:</label>
									<select name="pip_dashSort">
										<option value="newest" <?php if($dashSort == "newest"): echo "selected='true'"; endif;?>>Newest</option>
										<option value="oldest" <?php if($dashSort == "oldest"): echo "selected='true'"; endif;?>>Oldest</option>
										<option value="title" <?php if($dashSort == "title"): echo "selected='true'"; endif;?>>Title</option>
										<option value="priority" <?php if($dashSort == "priority"): echo "selected='true'"; endif;?>>Priority</option>
									</select>
								</li>
							</ul>
							<?php if($userLevel >= 8){//Above level 8 it is admin ?>
							<ul>
								<li>
									<strong>User Settings</strong>
								</li>
								<li>
									<label for="pip_adminIdeas">All users can view admin post ideas</label>
									<input name="pip_adminIdeas" type="checkbox" id="pip_adminIdeas" <?php echo $adminIdeasChecked; ?> />
								</li>
								<li>
									<label for="pip_adminIdeasOnly">Users can <strong>only</strong> view admin post ideas</label>
									<input name="pip_adminIdeasOnly" type="checkbox" id="pip_adminIdeasOnly" <?php echo $adminIdeasOnlyChecked; ?> />
								</li>
							</ul>
							<?php } ?>
							<input class='button-primary' type="submit" name="submit_settings" value="Save &raquo;" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<?php 
		//If only view admin PIs is checked and user isn't an admin
		if(in_array($user_id, $adminIdArray) || get_option( "pip_adminIdeasOnly" ) != "on"){ 
	?>
	<div class="postbox-container">
		<div class="metabox-holder">	
			<div class="meta-box-sortables ui-sortable">
				<form name="pip_postideas" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=postideas.php&amp;updated=true">
					<input type="hidden" name="pip_postidea_add" value="1" /> 
					<?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('pip_postidea_add'); ?>
			        <input type="hidden" name="pip_editid" value="<?php echo $edit_id; ?>" />
					<div class="postbox" id="addIdea">
						<div title="Click to toggle" class="handlediv"><br></div>
						<h3 class="hndle">
							<span><?php echo $form_word; ?> Post Idea</span>
						</h3>
						<div class="inside">
							<ul>
								<li>
									<label for="pip_addIdeaTitle">Title:</label>
									<input name="pip_addIdeaTitle" type="text" id="pip_addIdeaTitle" value="<?php echo $edit_title; ?>" size="50" />
								</li>	
								<li>
									<label for="pip_addIdeaDescription">Description:</label>
									<textarea name="pip_addIdeaDescription" type="text" id="pip_addIdeaDescription" rows="4" cols="40"><?php echo $edit_description; ?></textarea>
								</li>
								<li>
									<label for="pip_addIdeaKeywords">Keywords:</label>
									<input name="pip_addIdeaKeywords" type="text" id="pip_addIdeaKeywords" value="<?php echo $edit_keywords; ?>" size="50"/>
								</li>
								<li>
									<label for="pip_addIdeaURLS">Links: <small>(comma seperated)</small></label>
									<textarea name="pip_addIdeaURLS" id="pip_addIdeaURLS" rows="2" cols="45"><?php echo $edit_urls; ?></textarea>
								</li>
								<li>
									<label for="pip_addIdeaPriority">Priority: <small>(1-99)</small></label>
									<input name="pip_addIdeaPriority" type="text" id="pip_addIdeaPriority" value="<?php echo $edit_priority; ?>" size="3" maxlength="2" />
								</li>
							</ul>
							<input <?php if($_GET['edit']): ?> class='button-secondary' <?php else: ?> class='button-primary' <?php endif; ?> type="submit" name="submit_<?php echo $form_word; ?>" value="<?php echo $form_word; ?> Post Idea &raquo;" />
							<?php echo $switch; ?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div><!-- div class="postbox-container" -->
	<?php } ?>
	
    <h3>Post Ideas</h3>
    <?php echo $adminPostStatus; ?>
    <ul id="postIdeaOrder">
    	<li class="first">Order by:</li>
    	<li>
    		<?php if($currentOrder == "newest"): ?>
    			<span>Newest</span>
    		<?php else: ?>
    			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=postideas.php&amp;orderby=newest">Newest</a>
    		<?php endif; ?>
    	</li>
    	<li>
    		<?php if($currentOrder == "oldest"): ?>
    			<span>Oldest</span>
    		<?php else: ?>
    			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=postideas.php&amp;orderby=oldest">Oldest</a>
    		<?php endif; ?>
    	</li>
    	<li>
    		<?php if($currentOrder == "title"): ?>
    			<span>Title</span>
    		<?php else: ?>
    			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=postideas.php&amp;orderby=title">Title</a>
    		<?php endif; ?>
    	</li>
    	<li class="last">
    		<?php if($currentOrder == "priority"): ?>
    			<span>Priority</span>
    		<?php else: ?>
    			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=postideas.php&amp;orderby=priority">Priority</a>
    		<?php endif; ?>
    	</li>
    </ul>
    
    <table class="widefat" cellspacing="0" id="postResults">
		<thead>
			<tr>
				<th>Post Title</th>
				<th>Description</th>
				<th>Keywords</th>
				<th>Links</th>
				<th>Priority</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tfoot>
		    <tr>
				<th>Post Title</th>
				<th>Description</th>
				<th>Keywords</th>
				<th>Links</th>
				<th>Priority</th>
				<th>Actions</th>
			</tr>
		</tfoot>
		<tbody>
			<?php echo $output; ?>
		</tbody>
    </table>
</div><!-- div class='wrap' -->