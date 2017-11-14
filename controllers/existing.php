<?php

/**
 * Wordpress beta controller.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

//require clearos_app_base('webapp') . '/controllers/webapp_version.php';

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Wordpress version controller.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

class Existing extends ClearOS_Controller
{
    /**
     * Wordpress Existing constructor.
     */

    function __construct()
    {
        $this->lang->load('wordpress');

        parent::__construct('wordpress', lang('wordpress_app_name'));
    }
    /**
     * Beta index listing.
     *
     * @return load view
     */ 
    public function index()
    {
    	$this->load->library('wordpress/Webapp_site_driver');
    	$sites = $this->webapp_site_driver->get_old_projects();
    	$data['sites'] = $sites;
    	$this->page->view_form('existing_list', $data, lang('wordpress'));
    }
    /**
     * Edit form to move.
     *
     * @param string $site site name
     * @return load view 
     */ 
    function edit($site)
    {
    	$app_basename = 'wordpress';
    	$this->app_basename = $app_basename;
        $this->app_description = lang('wordpress_app_name');
        $this->version_driver = $app_basename . '/Webapp_Version_Driver';
        $this->site_driver = $app_basename . '/Webapp_Site_Driver';
        $this->driver = $app_basename . '/Webapp_Driver';
    	        // Load libraries
        //---------------
        $this->lang->load('webapp');
        $this->load->library($this->driver);
        $this->load->library($this->site_driver);
        $this->load->library($this->version_driver);
        // Set validation rules
        //---------------------
       
        $check_exists = TRUE;
        $this->form_validation->set_policy('site', $this->site_driver, 'validate_site', TRUE, $check_exists);
        $this->form_validation->set_policy('aliases', $this->site_driver, 'validate_aliases');
        $this->form_validation->set_policy('ssl_certificate', $this->site_driver, 'validate_ssl_certificate', TRUE);
        $this->form_validation->set_policy('group', $this->site_driver, 'validate_group', TRUE);
        if (clearos_app_installed('ftp'))
            $this->form_validation->set_policy('ftp', $this->site_driver, 'validate_ftp_state', TRUE);
        if (clearos_app_installed('samba'))
            $this->form_validation->set_policy('file', $this->site_driver, 'validate_file_state', TRUE);
        $form_ok = $this->form_validation->run();
        
        // Extra validation
        //-----------------
        if ($this->input->post('submit') && $this->input->post('site')) {
            // Make sure site name resolves via DNS
            $resolvable = dns_get_record($this->input->post('site'));
            if (!$resolvable) {
                $this->form_validation->set_error('site', lang('webapp_hostname_does_not_resolve_warning'));
                $form_ok = FALSE;
            }
        }

        // Handle form submit
        //-------------------
        if ($this->input->post('submit') && ($form_ok === TRUE)) {
            $group = ($this->input->post('group')) ? $this->input->post('group') : '';
            $ftp_state = ($this->input->post('ftp')) ? $this->input->post('ftp') : FALSE;
            $file_state = ($this->input->post('file')) ? $this->input->post('file') : FALSE;
            $use_existing_db = ($this->input->post('use_existing_database') == 'Yes') ? TRUE : FALSE;
            try {
               
                $this->webapp_site_driver->add_from_beta(
                    $this->input->post('site'),
                    $this->input->post('aliases'),
                    $this->input->post('ssl_certificate'),
                    $group,
                    $ftp_state,
                    $file_state,
                    $site
                );
                $this->page->set_status_added();
                redirect('/' . $this->app_basename);
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load the view data 
        //------------------- 
        try {
           
            $form_type = 'add';
            $data['form_type'] = $form_type;
            $data['webapp'] = $this->app_basename;
            $data['site'] = $site;
            $data['ftp_available'] = clearos_app_installed('ftp');
            $data['file_available'] = clearos_app_installed('samba');
            $data['groups'] = $this->webapp_site_driver->get_group_options($site);
            $data['ssl_certificate_options'] = $this->webapp_site_driver->get_ssl_certificate_options();

            if ($form_type == 'add') {
                $data['ftp_enabled'] = TRUE;
                $data['file_enabled'] = TRUE;

            } else {
                $data['aliases'] = $this->webapp_site_driver->get_aliases($site);
                $data['ftp_enabled'] = $this->webapp_site_driver->get_ftp_state($site);
                $data['file_enabled'] = $this->webapp_site_driver->get_file_state($site);
                $data['ssl_certificate'] = $this->webapp_site_driver->get_ssl_certificate($site);
                $data['group'] = $this->webapp_site_driver->get_group($site);
            }
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load the view
        //--------------
        $options['javascript'] = array(clearos_app_htdocs('webapp') . '/webapp.js.php');
        $this->page->view_form('move_form', $data, lang('wordpress_beta_migration'), $options);
    }
}
