<?php

/**
 * Wordpress beta form view.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('webapp');
$this->lang->load('web_server');
$this->lang->load('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// Form handling
///////////////////////////////////////////////////////////////////////////////


    $read_only = FALSE;
    $form_path = '/wordpress/existing/edit/'.$site;
    $buttons = array(
        form_submit_add('submit'),
        anchor_cancel('/app/wordpress')
    );

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open($form_path);
echo form_header(lang('webapp_site'));

// General information 
//--------------------

echo fieldset_header(lang('web_server_web_site'));
echo field_input('site', $site, lang('web_server_web_site_hostname'), $read_only);
echo field_input('aliases', $aliases, lang('web_server_aliases'));
echo field_dropdown('ssl_certificate', $ssl_certificate_options, $ssl_certificate, lang('certificate_manager_digital_certificate'));
echo fieldset_footer();



// Upload information 
//-------------------

echo fieldset_header(lang('web_server_upload_access'));
echo field_dropdown('group', $groups, $group, lang('base_group'));

if ($ftp_available)
    echo field_toggle_enable_disable('ftp', $ftp_enabled, lang('web_server_ftp_upload'));

if ($file_available)
    echo field_toggle_enable_disable('file', $file_enabled, lang('web_server_file_server_upload'));

echo fieldset_footer();

// Footer
//-------

echo field_button_set($buttons);

echo form_footer();
echo form_close();
