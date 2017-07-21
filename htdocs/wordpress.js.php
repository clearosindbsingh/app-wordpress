<?php
/**
 * Wordpress Javascript Helper.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */
///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////
$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';
///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////
clearos_load_language('base');
clearos_load_language('wordpress');
///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////
header('Content-Type: application/x-javascript');
?>

///////////////////////////////////////////////////////////////////////////////
// For main page
///////////////////////////////////////////////////////////////////////////////
$(function(){
	$(".delete_project_anchor").click(function(e){
		e.preventDefault();
		openProjectDeleteDialog(this);
		return false;
	});
	$(".delete_version_anchor").click(function(e){
		e.preventDefault();
		deleteWordpressVersion(this);
		return false;
	});
	$(".hide_btn").click(function(){
		$("#delete_modal").modal('hide');
	})
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


///////////////////////////////////////////////////////////////////////////////
// For backup page
///////////////////////////////////////////////////////////////////////////////

$(function(){
	$(".hide_btn").click(function(){
		$("#delete_backup_modal").modal('hide');
	})
	$(".delete_anchor").click(function(e){
		e.preventDefault();
		openBackupDeleteDialog($(this).attr('href'));
		return false;
	})
});
function openBackupDeleteDialog(url)
{
	$("#delete_backup_modal").modal('show');
	$("#delete_backup_modal").find('.delete_btn').attr('href',url);
}


///////////////////////////////////////////////////////////////////////////////
// For add project page
///////////////////////////////////////////////////////////////////////////////

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