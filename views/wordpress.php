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
    anchor_custom('/app/wordpress/addproject', lang('wordpress_add_project'), 'high', array('target' => '_self'))
);

echo infobox_highlight(
    'Wordpress Website',
    'Description',
    $options
);

?>
<table class="table table-striped theme-summary-table-large my-responsive dataTable dtr-inline">
	<thead>
		<tr><th>Folder Name</th><th>Options</th></tr>
	</thead>
	<tbody>
		<?php foreach ($projects as $key => $value) { ?>
			<tr>
				<td><?php echo $value['name']; ?></td>
				<td>
					<a class="btn btn-info" target="_blank" href="<?php echo $base_path.$value['name']; ?>"><i class="fa fa-info"></i> Access </a>
					<a class="btn btn-info" target="_blank" href="<?php echo $base_path.$value['name'].'/wp-admin'; ?>"><i class="fa fa-info"></i> Access Admin </a>
					<a class="btn btn-danger" data-folder_name="<?php echo $value['name']; ?>" onclick="deleteProject(this)" href="javascript:"><i class="fa fa-remove"></i> Delete</a>
				</td>
			</tr>
		<?php } ?>
		<?php if(!$projects) { ?>
			<tr><td colspan="2"><div class="text-center">Sorry! No project found</div></td></tr>
		<?php } ?>
	</tbody>
</table>

<script type="text/javascript">
	function deleteProject($this)
	{
		var folder_name = $($this).attr('data-folder_name');
		var con = confirm("Are you sure want to delete this project ?");
		if(con)
			window.location.href = '/app/wordpress/delete/'+folder_name;
	}
</script>