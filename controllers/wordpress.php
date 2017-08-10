<?php

/**
 * Version controller.
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

class Wordpress extends ClearOS_Controller
{
    /**
    * WordPress default controller.
    *
    * @return view
    */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('wordpress');
        $this->load->library('wordpress/Wordpress');
        $projects = $this->wordpress->get_project_list();
        $versions = $this->wordpress->get_versions();
        $data['projects'] = $projects;
        $data['versions'] = $versions;
        $data['base_path'] = 'https://'.$_SERVER['SERVER_ADDR'].'/wordpress/';

        // Load views
        //-----------
        $this->page->view_form('wordpress', $data, lang('wordpress_app_name'));
    }

    /**
    * Add a new Project
    * 
    * @return load view
    */

    function addproject()
    {
        // Load dependencies
        //------------------

        $this->lang->load('wordpress');
        $this->load->library('wordpress/Wordpress');

        $version_all = $this->wordpress->get_versions();
        $versions = array();
        foreach ($version_all as $key => $value) {
            if ($value['clearos_path'])
                $versions[$value['file_name']] = $value['version'];
        }

        if ($_POST) {

            // Handle Form 
            //------------------

            $use_exisiting_database = $this->input->post('use_exisiting_database');
            $this->form_validation->set_policy('folder_name', 'wordpress/Wordpress', 'validate_folder_name', TRUE);
            if($use_exisiting_database == "Yes")
                $this->form_validation->set_policy('database_name', 'wordpress/Wordpress', 'validate_existing_database', TRUE);
            else
                $this->form_validation->set_policy('database_name', 'wordpress/Wordpress', 'validate_new_database', TRUE);
            $this->form_validation->set_policy('database_user_name', 'wordpress/Wordpress', 'validate_database_username', TRUE);
            $this->form_validation->set_policy('database_user_password', 'wordpress/Wordpress', 'validate_database_password', TRUE);
            $this->form_validation->set_policy('root_username', 'wordpress/Wordpress', 'validate_root_username', TRUE);
            $this->form_validation->set_policy('root_password', 'wordpress/Wordpress', 'validate_root_password', TRUE);
            $this->form_validation->set_policy('wordpress_version', 'wordpress/Wordpress', 'validate_wordpress_version', TRUE);
            $form_ok = $this->form_validation->run();
            if ($form_ok) {
                $folder_name = $this->input->post('folder_name');
                $database_name = $this->input->post('database_name');
                $database_username = $this->input->post('database_user_name');
                $database_user_password = $this->input->post('database_user_password');
                $root_username = $this->input->post('root_username');
                $root_password = $this->input->post('root_password');
                $wordpress_version = $this->input->post('wordpress_version');
                try {
                    $this->wordpress->add_project($folder_name, $database_name, $database_username, $database_user_password, $root_username, $root_password, $use_exisiting_database, $wordpress_version);
                    $this->page->set_message(lang('wordpress_project_add_success'), 'info');
                    redirect('/wordpress');
                } catch (Exception $e) {
                    $this->page->view_exception($e);
                }
            }
        }
        $data['versions'] = $versions;
        $data['default_version'] = 'latest.zip';
        $this->page->view_form('add_project', $data, lang('wordpress_app_name'));
    }

    /**
    * Delete Project
    *
    * @param string $folder_name Folder Name 
    * @return redirect to index after delete
    */

    function delete($folder_name)
    {
        // Load dependencies
        //------------------

        $this->lang->load('wordpress');
        $this->load->library('wordpress/Wordpress');

        if ($_POST) {
            $database_name = '';
            $folder_name = $this->input->post('folder_name');
            $delete_database = $this->input->post('delete_database');

            if ($folder_name)
                $database_name = $this->wordpress->get_database_name($folder_name);
            $_POST['database_name'] = $database_name;
            $_POST['folder_name'] = $folder_name;
            $this->form_validation->set_policy('folder_name', 'wordpress/Wordpress', 'validate_folder_name_exists', TRUE);

            if ($delete_database) {
                $this->form_validation->set_policy('database_name', 'wordpress/Wordpress', 'validate_existing_database', TRUE);
                $this->form_validation->set_policy('root_username', 'wordpress/Wordpress', 'validate_root_username', TRUE);
                $this->form_validation->set_policy('root_password', 'wordpress/Wordpress', 'validate_root_password', TRUE);
            }
            $form_ok = $this->form_validation->run();

            if ($form_ok) {
                $folder_name = $this->input->post('folder_name');
                $database_name = $this->input->post('database_name');
                $root_username = $this->input->post('root_username');
                $root_password = $this->input->post('root_password');

                try {
                    $this->wordpress->delete_folder($folder_name);

                    if ($delete_database) {
                        //$this->wordpress->backup_database($database_name, $root_username, $root_password); /// due to some temp error I commented it
                        $this->wordpress->delete_database($database_name, $root_username, $root_password);
                    }
                    $this->page->set_message(lang('wordpress_project_delete_success'), 'info');
                    redirect('/wordpress');
                } catch (Exception $e) {
                    $this->page->view_exception($e);
                }
            }
        }
    }
}