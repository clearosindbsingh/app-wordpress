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

echo form_open('wordpress/addproject');
echo form_header(lang('wordpress_add_project'));
echo field_input('folder_name', '', lang('wordpress_folder_name'));
echo field_dropdown('use_exisiting_database', array('Yes' => lang('wordpress_select_yes'), 'No' => lang('wordpress_select_no')), 'No', lang('wordpress_use_existing_database'));
echo field_input('database_name', '', lang('wordpress_database_name'));
echo field_input('database_user_name', 'testuser', lang('wordpress_database_username'));
echo field_password('database_user_password', '', lang('wordpress_database_password'));
echo field_input('root_username', 'root', lang('wordpress_mysql_root_username'));
echo field_password('root_password', '', lang('wordpress_mysql_root_password'));
echo field_dropdown('wordpress_version', $versions, $default_version, lang('wordpress_wordpress_version'));
echo field_button_set(
    array(
    	anchor_cancel('/app/wordpress'),
    	form_submit_add('submit', 'high')
    )
);
echo form_footer();
echo form_close();

?>