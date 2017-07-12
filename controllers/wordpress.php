<?php

/**
 * WordPress controller.
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
        $data['projects'] = $projects;
        $data['base_path'] = 'https://'.$_SERVER['SERVER_ADDR'].'/wordpress/';

        // Load views
        //-----------
        $this->page->view_form('wordpress', $data, lang('wordpress_app_name'));
    }

    /*
    Add Project
    */
	function addproject()
	{
		$this->lang->load('wordpress');
		$this->load->library('wordpress/Wordpress');
		if($_POST)
		{
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
			$form_ok = $this->form_validation->run();
			if($form_ok)
			{
				$folder_name = $this->input->post('folder_name');
				$database_name = $this->input->post('database_name');
				$database_username = $this->input->post('database_user_name');
				$database_user_password = $this->input->post('database_user_password');
				$root_username = $this->input->post('root_username');
				$root_password = $this->input->post('root_password');
				try {
					$this->wordpress->add_project($folder_name, $database_name, $database_username, $database_user_password, $root_username, $root_password, $use_exisiting_database);
					//$this->wordpress->create_project_folder($folder_name);
					$this->wordpress->put_wordpress($folder_name);
					redirect('/wordpress');
				} catch (Exception $e) {
					$this->page->view_exception($e);
				}
			}
		}
		$this->page->view_form('add_project', NULL, lang('wordpress_app_name'));
	}
	/*
    Delete Project
    */
	function delete($folder_name)
	{
		$this->lang->load('wordpress');
		$this->load->library('wordpress/Wordpress');

		$this->wordpress->delete_folder($folder_name);
		redirect('/wordpress');
	}
}
