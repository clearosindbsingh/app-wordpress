<?php

/**
 * WordPress Add project View.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage Views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link    http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('wordpress');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////



$options['buttons']  = array(
    anchor_custom('/app/wordpress/backup', "Backups", 'high', array('target' => '_self')),
    anchor_custom('/app/mariadb', "MariaDB Server", 'high', array('target' => '_blank')),
    anchor_custom('/app/web_server', "Web Server", 'high', array('target' => '_blank')),
);

echo infobox_highlight(
    lang('wordpress_app_name'),
    lang('wordpress_app_dependencies_description'),
    $options
);



///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////
$headers = array(
    lang('wordpress_project_folder_name'),
);

///////////////////////////////////////////////////////////////////////////////
// Buttons
///////////////////////////////////////////////////////////////////////////////

$buttons  = array(anchor_custom('/app/wordpress/addproject', lang('wordpress_add_project'), 'high', array('target' => '_self')));



///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////
foreach ($projects as $value) {
    $item['title'] = $value['name'];
    $access_action = $base_path.$value['name'];
    $access_admin_action = $base_path.$value['name'].'/wp-admin';
    $delete_action = "javascript:";
    $item['anchors'] = button_set(
        array(
        	anchor_custom($access_action, lang('wordpress_access_website'), 'high'),
        	anchor_custom($access_admin_action, lang('wordpress_access_admin'), 'high'),
        	anchor_delete($delete_action, 'low', array('class' => 'delete_project_anchor', 'data' => array('folder_name' => $value['name']))),
        )
    );
    $item['details'] = array(
        $value['name']
    );
    $items[] = $item;
}


///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('wordpress_my_projects'),
    $buttons,
    $headers,
    $items
);



///////////////////////////////////////////////////////////////////////////////
// Table for wordpress versions
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////
$headers = array(
    lang('wordpress_wordpress_versions'),
);

///////////////////////////////////////////////////////////////////////////////
// Buttons
///////////////////////////////////////////////////////////////////////////////

$buttons  = array();



///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////
$items = array();
foreach ($versions as $value) {
    if ($value['clearos_path']) {
    	$download_btn = anchor_custom('javascript:', lang('wordpress_version_download_btn'), 'high', array('class' => 'disabled', 'disabled' => 'disabled'));
    	$delete_btn = anchor_custom('/app/wordpress/version/delete/'.$value['file_name'], lang('wordpress_version_delete_btn'), 'low', array('class' => 'delete_version_anchor', 'data' => array('file_name'=> $value['file_name'])));
    }
    else {
    	$download_btn = anchor_custom('/app/wordpress/version/download/'.$value['file_name'], lang('wordpress_version_download_btn'), 'high');
    	$delete_btn = anchor_custom('javascript:', lang('wordpress_version_delete_btn'), 'low', array('class' => 'disabled', 'disabled' => 'disabled'));
    	
    }
    $item['anchors'] = button_set(
        array(
        	$download_btn,
        	$delete_btn
        )
    );
    $item['details'] = array(
        "Wordpress: ".$value['version'],
    );
    $items[] = $item;
}


///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('wordpress_my_projects'),
    $buttons,
    $headers,
    $items
);

?>

<!-- Modal -->
<div id="delete_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
    <?php echo form_open('wordpress/delete'); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?php echo lang('wordpress_confirm_delete_project'); ?></h4>
      </div>
      <div class="modal-body">
      	<?php 
      		
      		//echo form_header("Delete");
      		echo field_checkbox("delete_sure","1", lang('wordpress_yes_delete_this_project'));
      		echo field_checkbox("delete_database","1", lang('wordpress_yes_delete_assigned_database'));
      		echo field_input('root_username', 'root', lang('wordpress_mysql_root_username'));
			echo field_password('root_password', '', lang('wordpress_mysql_root_password'));
      	?>
      </div>
      <div class="modal-footer">
      	<?php echo field_button_set(
    		array(form_submit_custom('submit', "Save", 'high',array('id' => 'delete_submit_btn')))
		); ?>
		<input type="hidden" name="folder_name" id="deleting_folder_name">
        <?php echo anchor_cancel('javascript:','low', array('class' => 'hide_btn')); ?>
      </div>
      <?php echo form_close(); ?>
    </div>

  </div>
</div>

