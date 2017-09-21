<?php

/**
 * WordPress Libraray class.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2005-2017 ClearFoundation
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

// Factories
//----------

use \clearos\apps\groups\Group_Manager_Factory as Group_Manager;

clearos_load_library('groups/Group_Manager_Factory');

// Classes
//--------

use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\base\File_Types as File_Types;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Tuning as Tuning;
use \clearos\apps\network\Role as Role;
use \clearos\apps\mariadb\MariaDB as MariaDB;
use \clearos\apps\web_server\Httpd as Httpd;
use \clearos\apps\flexshare\Flexshare as Flexshare;

clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('base/File_Types');
clearos_load_library('base/Folder');
clearos_load_library('base/Tuning');
clearos_load_library('network/Role');
clearos_load_library('mariadb/MariaDB');
clearos_load_library('web_server/Httpd');
clearos_load_library('flexshare/Flexshare');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\flexshare\Flexshare_Not_Found_Exception as Flexshare_Not_Found_Exception;
use \clearos\apps\accounts\Accounts_Driver_Not_Set_Exception as Accounts_Driver_Not_Set_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/Validation_Exception');
clearos_load_library('flexshare/Flexshare_Not_Found_Exception');
clearos_load_library('accounts/Accounts_Driver_Not_Set_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Wordpress class.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2005-2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

class Wordpress extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////

    const PATH_WEBROOT = '/var/www/html'; /// not in use
    const PATH_WORDPRESS = '/var/clearos/wordpress/sites';
    const PATH_VERSIONS = '/var/clearos/wordpress/versions/';
    const PATH_BACKUP = '/var/clearos/wordpress/backup/';
    const COMMAND_MYSQLADMIN = '/usr/bin/mysqladmin';
    const COMMAND_MYSQL = '/usr/bin/mysql';
    const COMMAND_MYSQLDUMP = '/usr/bin/mysqldump';
    const COMMAND_WGET = '/usr/bin/wget';
    const COMMAND_ZIP = '/usr/bin/zip';
    const COMMAND_UNZIP = '/usr/bin/unzip';
    const COMMAND_MV = '/usr/bin/mv';
    const CONFIG_SAMPLE_FILE_NAME = 'wp-config-sample.php';
    const CONFIG_MAIN_FILE_NAME = 'wp-config.php';
    const FOLDER_FLEXSHARE_WORDPRESS = '/var/flexshare/shares/wordpress';

    ///////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////

    var $locales;

    ///////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////

    /**
     * DansGuardian constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('wordpress');
    }
    /**
     * Get Project path
     *
     * @param @string $folder_name Folder Name
     *
     * @return @string path of folder
     */
    function get_project_path($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        return self::PATH_WORDPRESS.'/'.$folder_name.'/';
    }
    /**
     * Get MariaDB Running Status
     *
     * @return @string status
     */
    function get_mariadb_running_status()
    {
        $mariadb = new MariaDB();
        $status = $mariadb->get_status();
        return $status;
    }
    /**
     * Get MariaDB Password Status
     *
     * @return @boolean status
     */
    function get_mariadb_root_password_set_status()
    {
        $mariadb = new MariaDB();
        $status = $mariadb->is_root_password_set();
        return $status;
    }
    /**
     * Get Web Server Running Status
     *
     * @return @string status
     */
    function get_web_server_running_status()
    {
        $web_server = new Httpd();
        $status = $web_server->get_status();
        return $status;
    }
    /**
     * Check Dependencies Before Add A Project
     *
     * @return void
     * @return Exception when somethings goes wrong with Dependencies 
     */
    function check_dependencies()
    {
        $error = '';
        if ($this->get_web_server_running_status() == 'stopped') {
            $error = lang('wordpress_web_server_not_running');
        } else if ($this->get_mariadb_running_status() != 'running') {
            $error = lang('wordpress_mariadb_server_not_running');
        } else if (!$this->get_mariadb_root_password_set_status()) {
            $error = lang('wordpress_mariadb_password_not_set');
        } else if (!$this->get_versions(TRUE)) {
            $error = lang('wordpress_no_wordpress_version_downloaded');
        }
        if ($error) {
            throw new Engine_Exception($error);
        }
    }
    /**
     * Get Wordpress version
     *
     * @return @array Array of available versions
     */
    function get_versions($only_downloaded =FALSE)
    {
        $versions = array(
                array(
                    'version' => 'latest',
                    'download_url' => 'https://wordpress.org/latest.zip',
                    'deletable' => FALSE,
                    'size' => '',
                ),
                array(
                    'version' => '4.8',
                    'download_url' => 'https://wordpress.org/wordpress-4.8.zip',
                    'deletable' => TRUE,
                    'size' => '',
                ),
                array(
                    'version' => '4.7.5',
                    'download_url' => 'https://wordpress.org/wordpress-4.7.5.zip',
                    'deletable' => TRUE,
                    'size' => '',
                ),
                array(
                    'version' => '4.7.4',
                    'download_url' => 'https://wordpress.org/wordpress-4.7.4.zip',
                    'deletable' => TRUE,
                    'size' => '',
                ),
            );
        foreach ($versions as $key => $value) {
            $versions[$key]['file_name'] = basename($versions[$key]['download_url']);
            $versions[$key]['clearos_path'] = $this->get_wordpress_version_downloaded_path(basename($versions[$key]['download_url']));
            if ($only_downloaded) {
                if (!$versions[$key]['clearos_path'])
                    unset($versions[$key]);
            }

        }
        return $versions;
    }
    /**
     * Get local system download Wordpress version path
     * so system can copy from this path to new folder path 
     * 
     * @param @string $version_name zipped version name 
     *
     * @return @string $zip_folder if downloaded & available | FALSE if zip file is not available or not downloaded
     */
    function get_wordpress_version_downloaded_path($version_name)
    {
        $zip_folder = self::PATH_VERSIONS.$version_name;
        $folder = new Folder($zip_folder, TRUE);
        if ($folder->exists())
            return $zip_folder;
        return FALSE;

    }

    /**
    * Add a new project.
    *
    * @param string $folder_name Folder Name               
    * @param string $database_name Database name 
    * @param string $database_username Database user 
    * @param string $database_user_password Database user password 
    * @param string $root_username Root username for root permissions 
    * @param string $root_password Root password 
    * @param string $use_exisiting_database Yes / No if you want to use existing database
    * @param string $wordpress_version_file selected Wordpress version zip file name
    *
    * @return void
    */

    public function add_project(
        $folder_name, $database_name, $database_username, $database_user_password,
        $root_username, $root_password, $use_exisiting_database = "No", $wordpress_version_file = 'latest.zip'
        ) 
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();

        if ($use_exisiting_database == "No")
            $command = "mysql -u $root_username -p$root_password -e \"create database $database_name; GRANT ALL PRIVILEGES ON $database_name.* TO $database_username@localhost IDENTIFIED BY '$database_user_password'\"";
        else
            $command = "mysql -u $root_username -p$root_password -e \"GRANT ALL PRIVILEGES ON $database_name.* TO $database_username@localhost IDENTIFIED BY '$database_user_password'\"";

        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message());
        }
        $output = $shell->get_output();
        $output_message = strtolower($output[0]);
        if (strpos($output_message, 'error') !== FALSE)
            throw new Exception($output_message);

        $this->create_project_folder($folder_name);
        $this->put_wordpress($folder_name, $wordpress_version_file);
        $this->copy_sample_config_file($folder_name);
        $this->set_database_name($folder_name, $database_name);
        $this->set_database_user($folder_name, $database_username);
        $this->set_database_password($folder_name, $database_user_password);

        $folder = new Folder($this->get_project_path($folder_name));
        $folder->chmod(775, TRUE);
        $folder->chown('apache', 'apache', TRUE);

        return $output;
    }
    /**
     * Copy Config File from sample file 
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function copy_sample_config_file($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder_path = $this->get_project_path($folder_name);
        $sample_file = $folder_path.self::CONFIG_SAMPLE_FILE_NAME;
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;

        $sample_file_obj    = new File($sample_file, TRUE);
        $main_file_obj      = new File($main_file, TRUE);

        if (!$main_file_obj->exists())
            $sample_file_obj->copy_to($main_file);
    }
    /**
     * Config database name in config file
     *
     * @param string $folder_name Folder Name
     * @param string $database_name Database Name
     *
     * @return @void
     */
    function set_database_name($folder_name, $database_name)
    {
        $folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;

        $file = new File($main_file, TRUE);

        $replace = "define('DB_NAME', '$database_name');";
        $file->replace_lines("/DB_NAME/", $replace, 1);
    }
    /**
     * Change database user in config file
     *
     * @param string $folder_name Folder Name
     * @param string $database_username Database User
     *
     * @return @void
     */
    function set_database_user($folder_name, $database_username)
    {
        $folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;
        
        $file = new File($main_file, TRUE);

        $replace = "define('DB_USER', '$database_username');";
        $file->replace_lines("/DB_USER/", $replace, 1);
    }
    /**
     * Change database password in config file
     *
     * @param string $folder_name Folder Name
     * @param string $database_user_password Database Password
     *
     * @return @void
     */
    function set_database_password($folder_name, $database_user_password)
    {
        $folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;
        
        $file = new File($main_file, TRUE);

        $replace = "define('DB_PASSWORD', '$database_user_password');";
        $file->replace_lines("/DB_PASSWORD/", $replace, 1);
    }
    /**
     * Validate Folder Name.
     *
     * @param string $folder_name Folder Name
     *
     * @return string error message if Folder name is invalid
     */
    public function validate_folder_name($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $folder_name))
            return lang('wordpress_folder_name_invalid');
        else if($folder_name == 'wordpress')
            return lang('wordpress_folder_name_choose_other');
        else if($this->check_folder_exists($folder_name))
            return lang('wordpress_folder_already_exists');
    }
    /**
     * Validate Folder name must be exists.
     *
     * @param string $folder_name Folder name
     *
     * @return string error message if Folder name is not exists
     */
    public function validate_folder_name_exists($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $folder_name))
            return lang('wordpress_folder_name_invalid');
    }
    /**
     * Validate if database is new.
     *
     * @param string $database_name Database Name
     *
     * @return string error message if Database name is exists
     */
    public function validate_new_database($database_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $root_username = $_POST['root_username'];
        $root_password = $_POST['root_password'];
        $command = "mysql -u $root_username -p$root_password -e \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database_name'\"";
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            return $e->get_message();
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);
        if (strpos($output_message, 'error') !== FALSE)
            return lang('wordpress_unable_connect_via_root_user');
        else if($output)
            return lang('wordpress_database_already_exits');
    }
    /**
     * Validate if database is exisitng.
     *
     * @param string $database_name Database Name
     *
     * @return string error message if database name is not exists
     */
    public function validate_existing_database($database_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $root_username = $_POST['root_username'];
        $root_password = $_POST['root_password'];
        $command = "mysql -u $root_username -p$root_password -e \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database_name'\"";
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            return $e->get_message();
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);
        if (strpos($output_message, 'error') !== FALSE)
            return lang('wordpress_unable_connect_via_root_user');
        else if(!$output)
            return lang('wordpress_database_not_exits');
    }
    /**
     * Validate database username.
     *
     * @param string $username Username
     *
     * @return string error message if exists
     */
    public function validate_database_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $username))
            return lang('wordpress_username_invalid');
    }
    /**
     * Validate database password.
     *
     * @param string $password Password
     *
     * @return string error message if exists
     */
    public function validate_database_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/.*\S.*/', $password))
            return lang('wordpress_password_invalid');
    }
    /**
     * Validate root username.
     *
     * @param string $username Username
     *
     * @return string error message if exists
     */
    public function validate_root_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $username))
            return lang('wordpress_username_invalid');
    }
    /**
     * Validate database root password.
     *
     * @param string $password Password
     *
     * @return string error message if exists
     */
    public function validate_root_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/.*\S.*/', $password))
            return lang('wordpress_password_invalid');
    }
    /**
     * Validate wordpress version.
     *
     * @param string $wordpress_version version file name 
     *
     * @return string error message if exists
     */
    public function validate_wordpress_version($wordpress_version)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/.*\S.*/', $wordpress_version))
            return lang('wordpress_password_invalid');
    }
    /**
     * Check Folder Exists.
     *
     * @param string $folder_name Folder name
     *
     * @return TRUE if exists, FALSE if not exists 
     */
    function check_folder_exists($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $wpfolder = new Folder(self::PATH_WORDPRESS, TRUE);
        $project_path = self::PATH_WORDPRESS.'/'.$folder_name;
        
        if (!$wpfolder->exists()) {
            $wpfolder->create('webconfig', 'webconfig', 755);
            return FALSE;
        }
        $project_folder = new Folder($project_path, TRUE);
        if ($project_folder->exists()) {
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Create Project Folder.
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function create_project_folder($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->check_folder_exists($folder_name)) {
            return FALSE;
        }
        $new_folder = new Folder(self::PATH_WORDPRESS.'/'.$folder_name, TRUE);
        $new_folder->create('webconfig', 'webconfig', 755);
    }
    /**
     * Download and setup wordpress folder.
     *
     * @param string $folder_name Folder name
     * @param string $version_name Version name
     *
     * @return void
     */
    function put_wordpress($folder_name, $version_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $path_wordpress = self::PATH_WORDPRESS;

        $file = new File($this->get_wordpress_version_downloaded_path($version_name));
        if (!$file->exists())
            return FALSE;
        $file->copy_to($path_wordpress);

        $shell = new Shell();
        $options['validate_exit_code'] = FALSE;

        $command = $path_wordpress."/$version_name -d ".$path_wordpress;

        try {
            $retval = $shell->execute(
                self::COMMAND_UNZIP, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e);
        }
        $output = $shell->get_output();

        $command = $path_wordpress."/wordpress/* ".$path_wordpress.'/'.$folder_name;

        try {
            $retval = $shell->execute(
                self::COMMAND_MV, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e);
        }
        $output = $shell->get_output();
        $folder = new Folder($this->get_project_path('wordpress'));
        $folder->delete(TRUE);
        $file = new File($path_wordpress.'/'.$version_name);

        if($file->exists() && (!$file->is_directory()))
            $file->delete();

        // Add Flexshare
        // -------------

        $flexshare = new Flexshare();
        $comment = lang('wordpress_app_name') . ' - ' . $folder_name;
        $group = 'allusers';

        try {

            $flexshare->add_share($folder_name, $comment, $group, $this->get_project_path($folder_name), Flexshare::TYPE_WEB_SITE);
        } catch (Accounts_Driver_Not_Set_Exception $e) {

            $folder = new Folder($this->get_project_path($folder_name));
            $folder->delete(TRUE);

            redirect('/accounts');
        }
    }
    /**
     * Download wordpress version from official website.
     *
     * @param string $version_file_name Zip file name
     *
     * @return TRUE if download completed, FALSE if folder exists, ERROR if something goes wrong
    **/
    function download_version($version_file_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['validate_exit_code'] = FALSE;
        
        $path_versions = self::PATH_VERSIONS;
        $path_file = $path_versions.$version_file_name;

        $file = new File($path_file, TRUE);
        if($file->exists())
           return FALSE;
         
        $shell = new Shell();
        $command = "https://wordpress.org/$version_file_name -P $path_versions";
        try {
            $retval = $shell->execute(
                self::COMMAND_WGET, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e);
        }
        $output = $shell->get_output();
        return TRUE;
    }
    /**
     * Delete downloaded wordpress version.
     *
     * @param string $version_file_name Zip file name
     *
     * @return TRUE if delete completed, FALSE if file not exists, ERROR if something goes wrong 
     */
    function delete_version($version_file_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        
        $path_versions = self::PATH_VERSIONS;
        $path_file = $path_versions.$version_file_name;

        $file = new File($path_file, TRUE);
        if (!$file->exists())
           return FALSE;
        $file->delete();
            return TRUE;
    }
    /**
     * List of project.
     *
     * @return array $list of all projects under wordpress
     */
    function get_project_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();
        $folder = new Folder(self::PATH_WORDPRESS);
        if ($folder->exists()) {
            $list = $folder->get_listing(TRUE, FALSE);
        }
        return $list;
    }
    /**
     * Delete project folder.
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function delete_folder($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        $this->get_database_name($folder_name);
        $this->do_backup_folder($folder_name);
        $folder = new Folder($this->get_project_path($folder_name));
        $folder->delete(TRUE);

        // Flexshre delete
        /////////////////
        
        $flexshare = new Flexshare();
        $flexshare->delete_share($folder_name, TRUE);
    }
    /**
     * Create backup of given project folder.
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function do_backup_folder($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        $folder_path = $this->get_project_path($folder_name);

        $zip_path = self::PATH_WORDPRESS.'/'.$folder_name.'__'.date('Y-m-d-H-i-s').'.zip';
        $command = "-r $zip_path $folder_path";
        
        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_ZIP, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e);
        }
        $output = $shell->get_output();
        $file = new File($zip_path);
        if ($file->exists() && !$file->is_directory()) {
            $file->move_to(self::PATH_BACKUP);
        }
    }
    /**
     * Get database name from config file.
     *
     * @param string $folder_name Project folder name
     *
     * @return string $database_name Database Name
     */
    function get_database_name($folder_name)
    {
        $folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;
        
        $file = new File($main_file, TRUE);
        $line = $file->lookup_line("/DB_NAME/");
        preg_match_all('/".*?"|\'.*?\'/', $line, $matches);
        $database_name = trim($matches[0][1], "'");
        return $database_name;
    }
    /**
     * Delete MYSQL database.
     *
     * @param string $database_name Database Name
     * @param string $root_username Root Username
     * @param string $root_password Root Password
     *
     * @return Exception is somethings goes wrong with MYSQL 
    */
    function delete_database($database_name, $root_username, $root_password)
    {
        $command = "mysql -u $root_username -p$root_password -e \"DROP DATABASE $database_name\"";
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e->get_message());
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);

        if (strpos($output_message, 'error') !== FALSE)
            throw new Exception(lang('wordpress_unable_connect_via_root_user'));
    }
    /**
     * Backup MYSQL database.
     *
     * @param string $database_name Database Name
     * @param string $root_username Root Username
     * @param string $root_password Root Password
     *
     * @return Exception is somethings goes wrong with MYSQL 
    */
    function backup_database($database_name, $root_username, $root_password)
    {
        $sql_file_path = self::PATH_BACKUP.$database_name.'__'.date('Y-m-d-H-i-s').'.sql';
        $command = " -u $root_username -p$root_password $database_name > $sql_file_path";
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQLDUMP, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e->get_message());
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);
        if (strpos($output_message, 'error') !== FALSE)
            throw new Exception(lang('wordpress_unable_connect_via_root_user'));
        
    }
    /**
     * List of avalable Project & SQL backups.
     *
     * @return list of all backups under wordpress including database
    */
    function get_backup_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();
        $folder = new Folder(self::PATH_BACKUP);
        if ($folder->exists()) {
            $list = $folder->get_listing(TRUE, TRUE);
        }
        return $list;
    }
    /**
     * Start force download of backup
     *
     * @param string $file_name Backup file name
     * @return void
    */
    function download_backup($file_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        // Make file full path
        $file_path = self::PATH_BACKUP.$file_name;

        // Check file exists
        if (file_exists($file_path)) {
            // Getting file extension.
            $extension = explode('.', $file_name);
            $extension = $extension[count($extension)-1]; 
            // For Gecko browsers
            header('Content-Transfer-Encoding: binary');  
            // Supports for download resume
            header('Accept-Ranges: bytes');  
            // Calculate File size
            header('Content-Length: ' . filesize($file_path));  
            header('Content-Encoding: none');
            // Change the mime type if the file is not PDF
            header('Content-Type: application/'.$extension);  
            // Make the browser display the Save As dialog
            header('Content-Disposition: attachment; filename=' . $file_name);  
            readfile($file_path); 
            exit;
        }
        else
            throw new File_Not_Found_Exception(lang('wordpress_file_not_found'));
    }
    /**
     * Delete backup from system
     *
     * @param string $file_name Backup file name
     * @return TRUE if deletion successful, Exception if something wrong in deletion
    **/
    function delete_backup($file_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file_path = self::PATH_BACKUP.$file_name;
        $file = new File($file_path);

        if (!$file->is_directory())
            $file->delete(TRUE);
        else
            throw new File_Not_Found_Exception(lang('wordpress_file_not_found'));
        return TRUE;
    }
}