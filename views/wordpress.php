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


///////////////////////////////////////////////////////////////////////////////
// Infobox
///////////////////////////////////////////////////////////////////////////////

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
        	anchor_custom($access_action, lang('wordpress_access_website'), 'high', array('target' => '_blank')),
        	anchor_custom($access_admin_action, lang('wordpress_access_admin'), 'high', array('target' => '_blank')),
        	anchor_delete($delete_action, 'low', array('class' => 'delete_project_anchor', 'data' => array('folder_name' => $value['name']))),
        )
    );
    $item['details'] = array(
      $value['name']
    );
    $items[] = $item;
}


///////////////////////////////////////////////////////////////////////////////
// List table: Table for wordpress versions
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('wordpress_my_projects'),
    $buttons,
    $headers,
    $items
);


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
    lang('wordpress_wordpress_versions'),
    $buttons,
    $headers,
    $items
);


///////////////////////////////////////////////////////////////////////////////
// Make project delete confirm popup
///////////////////////////////////////////////////////////////////////////////

$title = lang('wordpress_confirm_delete_project');
$message = form_open('wordpress/delete');
$message = $message. field_checkbox("delete_sure","1", lang('wordpress_yes_delete_this_project'));
$message = $message. field_checkbox("delete_database","1", lang('wordpress_yes_delete_assigned_database'));
$message = $message. field_input('root_username', 'root', lang('wordpress_mysql_root_username'));
$message = $message. field_password('root_password', '', lang('wordpress_mysql_root_password'));
$message = $message. field_input('folder_name', '', 'Folder Name', FALSE, array('id' => 'deleting_folder_name'));
$message = $message. form_close();
$confirm = '#';
$trigger = '';
$form_id = 'delete_form';
$modal_id = 'delete_modal';

echo modal_confirm($title, $message, 'javascript:', $trigger, $form_id, $modal_id);