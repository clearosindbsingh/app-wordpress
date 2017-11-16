<?php

/**
 * Wordpress webapp site driver.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\wordpress;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('wordpress');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\wordpress\Webapp_Version_Driver as Webapp_Version_Driver;
use \clearos\apps\web_server\Httpd as Httpd;
use \clearos\apps\webapp\Webapp_Site_Engine as Webapp_Site_Engine;

clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('wordpress/Webapp_Version_Driver');
clearos_load_library('web_server/Httpd');
clearos_load_library('webapp/Webapp_Site_Engine');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Wordpress webapp site driver.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

class Webapp_Site_Driver extends Webapp_Site_Engine
{
    ///////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////

    const WEBAPP_BASENAME = 'wordpress';
    const COMMAND_MYSQL = '/usr/bin/mysql';
    const COMMAND_UNZIP = '/usr/bin/unzip';
    const FILE_CONFIG = 'wp-config.php';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Wordpress webapp site constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct(self::WEBAPP_BASENAME);
    }

    /**
     * Adds a new Wordpress site.
     *
     * @param string  $site                    site hostname
     * @param string  $aliases                 site aliases
     * @param string  $database_name           database name
     * @param string  $database_username       database user
     * @param string  $database_password       database user password
     * @param string  $database_admin_username database admin username
     * @param string  $database_admin_password database admin password
     * @param boolean $use_existing_database   use existing database flag
     * @param string  $version                 selected version zip file name
     * @param string  $ssl_certificate         SSL certificate name
     * @param string  $group                   group for upload access
     * @param boolean $ftp_enabled             flag for FTP upload state
     * @param boolean $file_enabled            flag for file/Samba upload state
     *
     * @return void
     */

    public function add($site, $aliases, $database_name, $database_username, $database_password,
        $database_admin_username, $database_admin_password, $use_existing_database, $version,
        $ssl_certificate, $group, $ftp_enabled, $file_enabled
    )
    {
        clearos_profile(__METHOD__, __LINE__);

        $webapp_version = new Webapp_Version_Driver(self::WEBAPP_BASENAME);

        // Validation
        //-----------

        Validation_Exception::is_valid($this->validate_site($site));
        Validation_Exception::is_valid($this->validate_aliases($aliases));
        Validation_Exception::is_valid($this->validate_database_name($database_name));
        Validation_Exception::is_valid($this->validate_database_username($database_username));
        Validation_Exception::is_valid($this->validate_database_password($database_password));
        Validation_Exception::is_valid($this->validate_database_username($database_admin_username));
        Validation_Exception::is_valid($this->validate_database_password($database_admin_password));
        Validation_Exception::is_valid($this->validate_ssl_certificate($ssl_certificate));
        Validation_Exception::is_valid($this->validate_group($group));
        Validation_Exception::is_valid($this->validate_ftp_state($ftp_enabled));
        Validation_Exception::is_valid($this->validate_file_state($file_enabled));
        Validation_Exception::is_valid($webapp_version->validate_version($version));

        // Database handling
        //------------------

        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();

        if ($use_existing_database)
            $params = "mysql -u $database_admin_username -p$database_admin_password -e \"GRANT ALL PRIVILEGES ON $database_name.* TO $database_username@localhost IDENTIFIED BY '$database_password'\"";
        else
            $params = "mysql -u $database_admin_username -p$database_admin_password -e \"create database $database_name; GRANT ALL PRIVILEGES ON $database_name.* TO $database_username@localhost IDENTIFIED BY '$database_password'\"";

        $retval = $shell->execute(self::COMMAND_MYSQL, $params, FALSE, $options);
        $output = $shell->get_output();
        $output_message = strtolower($output[0]);
        if (strpos($output_message, 'error') !== FALSE)
            throw new Engine_Exception($output_message);

        // Add web site via Httpd API
        // --------------------------

        $httpd = new Httpd();

        $options['require_authentication'] = FALSE;
        $options['show_index'] = TRUE;
        $options['follow_symlinks'] = TRUE;
        $options['ssi'] = TRUE;
        $options['htaccess'] = TRUE;
        $options['cgi'] = FALSE;
        $options['require_ssl'] = FALSE;
        $options['custom_configuration'] = FALSE;
        $options['php'] = TRUE;
        $options['php_engine'] = Httpd::PHP_70;
        $options['web_access'] = Httpd::ACCESS_ALL;
        $options['folder_layout'] = Httpd::FOLDER_LAYOUT_SANDBOX;
        $options['system_permissions'] = Httpd::PERMISSIONS_THIRD_PARTY;
        $options['ssl_certificate'] = $ssl_certificate;
        $options['webapp'] = self::WEBAPP_BASENAME;
        $options['comment'] = lang('wordpress_app_name') . ' - ' . $site;

        $httpd->add_site(
            $site,
            $aliases,
            $group,
            $ftp_enabled,
            $file_enabled,
            Httpd::TYPE_WEB_APP,
            $options
        );

        $this->_put_wordpress($site, $version);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Download and setup Wordpress folder.
     *
     * @param string $site    site name
     * @param string $version version
     *
     * @return void
     */

    protected function _put_wordpress($site, $version)
    {
        clearos_profile(__METHOD__, __LINE__);

        $webapp_version = new Webapp_Version_Driver('wordpress');

        // Validation
        //-----------

        Validation_Exception::is_valid($webapp_version->validate_version($version));

        // Grab zip file name
        //-------------------

        $versions = $webapp_version->listing();

        $zip_file = $versions[$version]['local_path'];

        $doc_root = $this->get_document_root($site);

        if (empty($doc_root) || empty($zip_file))
            throw new Engine_Exception(lang('base_ooops'));

        // Unpack
        //-------

        // Update file permissons
        $folder = new Folder($doc_root, TRUE);
        $folder->chmod('2775'); // Set the sticky bit to preserve group ownership in folder

        // Unpack
        $shell = new Shell();
        $options['stdin'] = ' ';
        $shell->execute(self::COMMAND_UNZIP, "$zip_file -d $doc_root", TRUE, $options);

        // Security policy
        //----------------

        // Insecure: set all files/folders to be owned by apache
        // This needs to be improved, but product team vetoed it.
        $folder = new Folder($doc_root, TRUE);
        $folder->chown(Httpd::SERVER_USERNAME, '', TRUE);

        // move all folder and files to docroot
        $list = $folder->get_listing(TRUE);
        $setup_folder = $list[0]['name'];
        $setup_path = $doc_root.'/'.$setup_folder.'/';
        $folder = new Folder($setup_path);
        $list = $folder->get_listing(TRUE, TRUE);

        foreach ($list as $key => $value) {
            if ($value['is_dir']) {
                $folder = new Folder($setup_path.$value['name']);
                $folder->move_to($doc_root);

            } else {
                $file = new File($setup_path.$value['name']);
                $file->move_to($doc_root);
            }
        }

        // delete setup folder
        $folder = new Folder($setup_path);
        $folder->delete(TRUE);
    }


    ////////////////////////////////////////////////////////////
    /////      Methods for Beta Version Recovery        ////////
    ////////////////////////////////////////////////////////////

    /**
     * Move Beta Wordpress site.
     *
     * @param string  $site                    site hostname
     * @param string  $aliases                 site aliases
     * @param string  $ssl_certificate         SSL certificate name
     * @param string  $group                   group for upload access
     * @param boolean $ftp_enabled             flag for FTP upload state
     * @param boolean $file_enabled            flag for file/Samba upload state
     * @param string $folder_existing          existing folder name
     *
     * @return void
     */

    /**
     * List of wordpress beta folders.
     *
     *
     * @return array folder list 
     */
    public function get_old_projects()
    {
        $folder = new Folder("/var/clearos/wordpress/sites", TRUE);
        $listings = $folder->get_listing(TRUE, FALSE);
        return $listings;
    }

    public function add_from_beta($site, $aliases,
        $ssl_certificate, $group, $ftp_enabled, $file_enabled, $folder_existing
    )
    {
        clearos_profile(__METHOD__, __LINE__);

        $webapp_version = new Webapp_Version_Driver(self::WEBAPP_BASENAME);

        // Validation
        //-----------

        Validation_Exception::is_valid($this->validate_site($site));
        Validation_Exception::is_valid($this->validate_aliases($aliases));
        Validation_Exception::is_valid($this->validate_ssl_certificate($ssl_certificate));
        Validation_Exception::is_valid($this->validate_group($group));
        Validation_Exception::is_valid($this->validate_ftp_state($ftp_enabled));
        Validation_Exception::is_valid($this->validate_file_state($file_enabled));


        // Database handling
        //------------------

        $database_name = $this->get_database_name($folder_existing);
        $database_username = $this->get_database_user($folder_existing);
        $database_password = $this->get_database_password($folder_existing);

        if ($database_name && $database_username) {

            $con = mysqli_connect("localhost", "$database_username", "$database_password", "$database_name");
            
            if (mysqli_connect_errno()) {
               throw new Engine_Exception(mysqli_connect_error());
            }

            $website_url = $site; // Base Path or HTTP(S) URL of website 
            mysqli_query($con, "UPDATE `$database_name`.`wp_options` SET `option_value` = '$website_url' WHERE `wp_options`.`option_name` = 'siteurl' OR `wp_options`.`option_name` = 'home';");

        }   

        // Add web site via Httpd API
        // --------------------------

        $httpd = new Httpd();

        $options['require_authentication'] = FALSE;
        $options['show_index'] = TRUE;
        $options['follow_symlinks'] = TRUE;
        $options['ssi'] = TRUE;
        $options['htaccess'] = TRUE;
        $options['cgi'] = FALSE;
        $options['require_ssl'] = FALSE;
        $options['custom_configuration'] = FALSE;
        $options['php'] = TRUE;
        $options['php_engine'] = Httpd::PHP_70;
        $options['web_access'] = Httpd::ACCESS_ALL;
        $options['folder_layout'] = Httpd::FOLDER_LAYOUT_SANDBOX;
        $options['system_permissions'] = Httpd::PERMISSIONS_THIRD_PARTY;
        $options['ssl_certificate'] = $ssl_certificate;
        $options['webapp'] = self::WEBAPP_BASENAME;
        $options['comment'] = lang('wordpress_app_name') . ' - ' . $site;

        $httpd->add_site(
            $site,
            $aliases,
            $group,
            $ftp_enabled,
            $file_enabled,
            Httpd::TYPE_WEB_APP,
            $options
        );

        $this->_move_wordpress_beta($site, $folder_existing, $group);
    }

    /**
     * Move & setup Wordpress beta folder with webapp.
     *
     * @param string $site    site name
     * @param string $folder_existing existing folder name
     * @param string $group group
     *
     * @return void
     */
    protected function _move_wordpress_beta($site, $folder_existing, $group)
    {

        clearos_profile(__METHOD__, __LINE__);

        $doc_root = $this->get_document_root($site);

        if (empty($doc_root))
            throw new Engine_Exception(lang('base_ooops'));

        // Update file permissons
        $folder = new Folder($doc_root, TRUE);
        $folder->chmod('2775'); // Set the sticky bit to preserve group ownership in folder

        

        // Security policy
        //----------------

        // Insecure: set all files/folders to be owned by apache
        // This needs to be improved, but product team vetoed it.
        $folder = new Folder($doc_root, TRUE);
        $folder->chown(Httpd::SERVER_USERNAME, '', TRUE);

        // move all folder and files to docroot
        $folder_existing_path = '/var/clearos/wordpress/sites/'.$folder_existing;
        $folder_existing_object = new Folder($folder_existing_path);
        
        $list = $folder_existing_object->get_listing(TRUE, TRUE);

        foreach ($list as $key => $value) {
            if ($value['is_dir']) {
                $folder = new Folder($folder_existing_path.'/'.$value['name']);
                $folder->move_to($doc_root);

            } else {
                $file = new File($folder_existing_path.'/'.$value['name']);
                $file->move_to($doc_root);
            }
        }

        // change permission to apache
        $folder = new Folder($doc_root, TRUE);
        $folder->chown(Httpd::SERVER_USERNAME, $group, TRUE);

        // delete existing folder
        $folder_existing_object->delete(TRUE);
    }

    /**
     * Get database name from config file.
     *
     * @param string $site Project folder name
     *
     * @return string $database_name Database Name
     */
    function get_database_name($site)
    {
        $value = $this->_get_config_value("DB_NAME", $site);
        if($value)
            return $value;
    }
    /**
     * Get database user from config file.
     *
     * @param string $site Project folder name
     *
     * @return string $database_user Database User
     */
    function get_database_user($site)
    {
        $value = $this->_get_config_value("DB_USER", $site);
        if($value)
            return $value;
    }
    /**
     * Get database password from config file.
     *
     * @param string $site Project folder name
     *
     * @return string $password password
     */
    function get_database_password($site)
    {
        $value = $this->_get_config_value("DB_PASSWORD", $site);
        if($value)
            return $value;
    }

    /**
     * Get config value from config file.
     *
     * @param string $key key
     * @param string $site Project folder name
     *
     * @return string value
     */
    protected function _get_config_value($key, $site)
    {
        $folder_path = '/var/clearos/wordpress/sites/'.$site;
        //$folder_path =  $this->get_document_root($site);
        $main_file = $folder_path.'/'.self::FILE_CONFIG;
        
        $file = new File($main_file, TRUE);
        if(!$file->exists())
            return FALSE;
        $line = $file->lookup_line("/$key/");
        preg_match_all('/".*?"|\'.*?\'/', $line, $matches);
        $value = trim($matches[0][1], "'");
        return $value;
    }
}
