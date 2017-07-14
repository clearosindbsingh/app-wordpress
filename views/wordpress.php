<?php

/**
 * WordPress Main View.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage View
 * @author     Your name <your@e-mail>
 * @copyright  2013 Your name / Company
 * @license    Your license
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('wordpress');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////



$options['buttons']  = array(
    anchor_custom('/app/wordpress/backups', "Backups", 'high', array('target' => '_blank', 'disabled' => 'disabled')),
    anchor_custom('/app/mariadb', "MariaDB Server", 'high', array('target' => '_blank')),
    anchor_custom('/app/web_server', "Web Server", 'high', array('target' => '_blank')),
);

echo infobox_highlight(
    'Wordpress Website',
    'Description',
    $options
);

?>
<div class="box">
	<div class="box-header">
	    <div class="theme-box-tools pull-right">&nbsp; <?php echo anchor_custom('/app/wordpress/addproject', lang('wordpress_add_project'), 'high', array('target' => '_self')) ?> </div>
	    <h3 class="box-title">My Projects</h3>
	</div>
	<div class="box-body">
		<table class="table table-striped theme-summary-table-large my-responsive dataTable dtr-inline">
			<thead>
				<tr><th width="54%">Folder Name</th><th>Options</th></tr>
			</thead>
			<tbody>
				<?php foreach ($projects as $key => $value) { ?>
					<tr>
						<td><?php echo $value['name']; ?></td>
						<td>
							<a class="btn btn-info" target="_blank" href="<?php echo $base_path.$value['name']; ?>"><i class="fa fa-info"></i> Access </a>
							<a class="btn btn-info" target="_blank" href="<?php echo $base_path.$value['name'].'/wp-admin'; ?>"><i class="fa fa-info"></i> Access Admin </a>
							<a class="btn btn-danger" data-folder_name="<?php echo $value['name']; ?>" onclick="openProjectDeleteDialog(this)" href="javascript:"><i class="fa fa-remove"></i> Delete</a>
						</td>
					</tr>
				<?php } ?>
				
			</tbody>
		</table>
	</div>
</div>

<div class="box">
	<div class="box-header">
	    <div class="theme-box-tools pull-right">&nbsp; </div>
	    <h3 class="box-title">Wordpress Version</h3>
	</div>
	<div class="box-body">
		
		<table class="table table-striped theme-summary-table-large my-responsive dataTable dtr-inline">
			<thead>
				<tr><th width="60%">Available Version</th><th>Options</th></tr>
			</thead>
			<tbody>
				<?php foreach ($versions as $key => $value) { ?>
					<tr>
						<td>Wordpress : <?php echo $value['version']; ?></td>
						<td>
							<?php if($value['clearos_path']) { ?>
								<a class="btn btn-info disabled" href="javascript:"><i class="fa fa-download"></i> Downloaded </a>
							<?php } else { ?>
								<a class="btn btn-info" href="<?php echo '/app/wordpress/version/download/'.$value['file_name']; ?>"><i class="fa fa-download"></i> Download </a>
							<?php } ?>
							<?php if($value['clearos_path']) { ?>
								<a class="btn btn-danger" href="javascript:" data-file_name="<?php echo $value['file_name']; ?>" onclick="deleteWordpressVersion(this)" ><i class="fa fa-remove"></i> Delete</a>
							<?php } else { ?>
								<a class="btn btn-danger disabled" href="javascript:"><i class="fa fa-remove"></i> Delete</a>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
			
	</div>
</div>

<!-- Modal -->
<div id="delete_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
    <?php echo form_open('wordpress/delete'); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Are you sure want to delete this project ?</h4>
      </div>
      <div class="modal-body">
      	<?php 
      		
      		//echo form_header("Delete");
      		echo field_checkbox("delete_sure","1","Yes delete this project");
      		echo field_checkbox("delete_database","1","Delete the assigned database");
      		echo field_input('root_username', 'root', "MYSQL Root Username");
			echo field_password('root_password', '', "MYSQL Root Password");
      	?>
      </div>
      <div class="modal-footer">
      	<?php echo field_button_set(
    		array(form_submit_custom('submit', "Save", 'high',array('id' => 'delete_submit_btn')))
		); ?>
		<input type="hidden" name="folder_name" id="deleting_folder_name">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      <?php echo form_close(); ?>
    </div>

  </div>
</div>


<script type="text/javascript">
	$(function(){
		$('.dataTable').dataTable();
		$("#delete_database").change(function(){
			if($(this).is(":checked"))
			{
				$("#root_username_field").show();
				$("#root_password_field").show();
			}
			else
			{
				$("#root_username_field").hide();
				$("#root_password_field").hide();
			}
		});
		$("#delete_sure").change(function(){
			if($(this).is(":checked"))
			{
				$("#delete_submit_btn").removeClass('disabled').removeAttr('disabled');
			}
			else
			{
				$("#delete_submit_btn").addClass('disabled').attr('disabled','disabled');
			}
		});
	});
	function openProjectDeleteDialog($this)
	{
		$("#deleting_folder_name").val($($this).attr('data-folder_name'));
		$("#delete_sure").attr('checked',false);
		$("#delete_database").attr('checked',false);
		$("#root_username_field").hide();
		$("#root_password_field").hide();
		$("#delete_submit_btn").addClass('disabled').attr('disabled','disabled');
		$("#delete_modal").modal('show');
	}
	function deleteProject($this)
	{
		var folder_name = $($this).attr('data-folder_name');
		var con = confirm("Are you sure want to delete this project ?");
		if(con)
			window.location.href = '/app/wordpress/delete/'+folder_name;
	}
	function deleteWordpressVersion($this)
	{
		var file_name = $($this).attr('data-file_name');
		var con = confirm("Are you sure want to delete this version ?");
		if(con)
			window.location.href = '/app/wordpress/version/delete/'+file_name;
	}
</script>

