<?php

/**
 * WordPress Add project View.
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

echo form_open('wordpress/addproject');
echo form_header("Add a new Wordpress Project");
echo field_input('folder_name', '', "Folder Name");
echo field_dropdown('use_exisiting_database', array('Yes' => 'Yes', 'No' => 'No'), 'No', 'Use Existing Database');
echo field_input('database_name', '', "Database Name");
echo field_input('database_user_name', 'testuser', "Database User");
echo field_password('database_user_password', '', "User Password");
echo field_input('root_username', 'root', "MYSQL Root Username");
echo field_password('root_password', '', "MYSQL Root Password");
echo field_dropdown('wordpress_version', $versions, $default_version, 'Wordpress Version');
echo field_button_set(
    array(form_submit_custom('submit', "Save", 'high'))
);
echo form_footer();
echo form_close();

?>
<script type="text/javascript">
	$(function(e){
		selectDatabase();
		$("#use_exisiting_database").change(function(){
			selectDatabase();
		})
	})
	function selectDatabase()
	{
		var option = $("#use_exisiting_database").val();
		if(option == "Yes")
		{
			$("#database_name_label").text("Existing DB Name");
		}
		else
		{
			$("#database_name_label").text("New DB Name");
		}
	}
</script>