<?php

/**
 * Backup controller.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage Controller
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link    http://www.clearfoundation.com/docs/developer/apps/wordpress/
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * WordPress controller.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link    http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

class Backup extends ClearOS_Controller
{
    /**
     * WordPress Backup controller.
     *
     * @return view
     */
    function index()
    {
		// Load libraries
    	//---------------

		$this->lang->load('wordpress');
		$this->load->library('wordpress/Wordpress');

		$data['backups'] = $this->wordpress->get_backup_list();
		$this->page->view_form('backups', $data, lang('wordpress_available_backup'));
	}
	/**
     * Download Backup file
     *
     * @param string $file_name File Name
     * @return Start dorce download 
     */ 
    function download($file_name)
	{
		// Load libraries
        //---------------

		$this->lang->load('wordpress');

		$this->load->library('wordpress/Wordpress');
		$this->wordpress->download_backup($file_name);
	}
	/**
     * Delete wordpress version
     *
     * @param @string $file_name File name
     *
     * @return @rediret load backup index page
     */
	function delete($file_name)
	{
		// Load libraries
        //---------------

		$this->lang->load('wordpress');
		$this->load->library('wordpress/Wordpress');

		$this->wordpress->delete_backup($file_name);
		$this->page->set_message(lang('wordpress_backup_delete_success'), 'info');
		redirect('/wordpress/backup');
	}
}
