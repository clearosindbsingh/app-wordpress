<?php

/**
 * Version controller.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage Views
 * @author     Your name <your@e-mail>
 * @copyright  2017 Your name / Company
 * @license    Your license
 */

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * WordPress controller.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage Controllers
 * @author     Your name <your@e-mail>
 * @copyright  2017 Your name / Company
 * @license    Your license
 */

class Version extends ClearOS_Controller
{
    /**
     * WordPress Version controller.
     *
     * @return view
     */

    function download($file_name)
	{
		$this->lang->load('wordpress');
		$this->load->library('wordpress/Wordpress');

		$this->wordpress->download_version($file_name);
		$this->page->set_message(lang('wordpress_version_download_success'), 'info');
		redirect('/wordpress');
	}
	function delete($file_name)
	{
		$this->lang->load('wordpress');
		$this->load->library('wordpress/Wordpress');

		$this->wordpress->delete_version($file_name);
		$this->page->set_message(lang('wordpress_version_delete_success'), 'info');
		redirect('/wordpress');
	}
}
