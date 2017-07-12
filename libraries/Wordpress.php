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

clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('base/File_Types');
clearos_load_library('base/Folder');
clearos_load_library('base/Tuning');
clearos_load_library('network/Role');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/Validation_Exception');

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
 * @link       http://www.clearfoundation.com/docs/developer/apps/content_filter/
 */

class Wordpress extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////

    const PATH_WEBROOT = '/var/www/html';
    const PATH_WORDPRESS = '/var/www/html/wordpress';
    const COMMAND_MYSQLADMIN = '/usr/bin/mysqladmin';
    const COMMAND_MYSQL = '/usr/bin/mysql';
    const COMMAND_WGET = '/bin/wget';
    const COMMAND_UNZIP = '/bin/unzip';
    const COMMAND_MV = '/bin/mv';
    const CONFIG_SAMPLE_FILE_NAME = 'wp-config-sample.php';
    const CONFIG_MAIN_FILE_NAME = 'wp-config.php';

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
     * @param $folder_name Folder Name
     *
     * @return @string path of folder
     */
    function get_project_path($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        return self::PATH_WORDPRESS.'/'.$folder_name.'/';
    }

    /**
     * Adds A new project.
     *
     * @param $folder_name Folder name
     * @param $database_name Database name
     * @param $database_user Database user
     * @param $ Project name
     *
     * @return vois
     */

    public function add_project($folder_name, $database_name, $database_username, $database_user_password, $root_username, $root_password, $use_exisiting_database = "No")
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();

        if($use_exisiting_database == "No")
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
        if (strpos($output_message, 'error') !== false)
            throw new Exception($output_message);

        $this->create_project_folder($folder_name);
        $this->put_wordpress($folder_name);
        $this->copy_sample_config_file($folder_name);
        $this->set_database_name($folder_name, $database_name);
        $this->set_database_user($folder_name, $database_username);
        $this->set_database_password($folder_name, $database_user_password);
        return $output;
    }
    /**
     * Copy Config File from sample file 
     *
     * @param $folder_name Folder Name
     *
     * @return void
     */
    function copy_sample_config_file($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder_path = $this->get_project_path($folder_name);
        $sample_file = $folder_path.self::CONFIG_SAMPLE_FILE_NAME;
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;

        $sample_file_obj = new File($sample_file,TRUE);
        $main_file_obj = new File($main_file,TRUE);
        if(!$main_file_obj->exists())
            $sample_file_obj->copy_to($main_file);
    }
    /**
     * Config database name in config file
     *
     * @param $folder_name Folder Name
     * @param $database_name Database Name
     *
     * @return @void
     */
    function set_database_name($folder_name,$database_name)
    {
        $folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;

        $file = new File($main_file,TRUE);

        $replace = "define('DB_NAME', '$database_name');";
        $file->replace_lines("/DB_NAME/",$replace,1);
    }
    /**
     * Change database user in config file
     *
     * @param $folder_name Folder Name
     * @param $database_user Database User
     *
     * @return @void
     */
    function set_database_user($folder_name,$database_username)
    {
        $folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;
        
        $file = new File($main_file,TRUE);

        $replace = "define('DB_USER', '$database_username');";
        $file->replace_lines("/DB_USER/",$replace,1);
    }
    /**
     * Change database password in config file
     *
     * @param $folder_name Folder Name
     * @param $database_password Database Password
     *
     * @return @void
     */
    function set_database_password($folder_name,$database_user_password)
    {
        $folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_NAME;
        
        $file = new File($main_file,TRUE);

        $replace = "define('DB_PASSWORD', '$database_user_password');";
        $file->replace_lines("/DB_PASSWORD/",$replace,1);
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
        if (strpos($output_message, 'error') !== false)
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
        if (strpos($output_message, 'error') !== false)
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
     * Check Folder Exists.
     *
     * @param string $folder_name Folder Name
     *
     * @return True if exists
     */
    function check_folder_exists($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $wpfolder = new Folder(self::PATH_WORDPRESS,TRUE);
        $project_path = self::PATH_WORDPRESS.'/'.$folder_name;
        if(!$wpfolder->exists())
        {
            $wpfolder->create('root', 'root', 0777);
            return FALSE;
        }
        $project_folder = new Folder($project_path,TRUE);
        if($project_folder->exists())
        {
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

        if($this->check_folder_exists($folder_name))
        {
            return FALSE;
        }
        $new_folder = new Folder(self::PATH_WORDPRESS.'/'.$folder_name, TRUE);
        $new_folder->create('root', 'root', 0777);

    }
    /**
     * Download and setup wordpress folder.
     *
     * @param string $folder_name Folder Name
     *
     * @return
     */
    function put_wordpress($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();

        //echo "<pre>";
        $path_wordpress = self::PATH_WORDPRESS;


        $command = "https://wordpress.org/latest.zip -P $path_wordpress";
        try {
            $retval = $shell->execute(
                self::COMMAND_WGET, $command, TRUE, $options
            );
        } catch (Engine_Exception $e) {
           // print_r($e);
        }
        $output = $shell->get_output();

        $command = $path_wordpress."/latest.zip -d ".$path_wordpress;
        try {
            $retval = $shell->execute(
                self::COMMAND_UNZIP, $command, TRUE, $options
            );
        } catch (Engine_Exception $e) {
           print_r($e);
        }
        $output = $shell->get_output();

        $command = $path_wordpress."/wordpress/* ".$path_wordpress.'/'.$folder_name;
        try {
            $retval = $shell->execute(
                self::COMMAND_MV, $command, TRUE, $options
            );
        } catch (Engine_Exception $e) {
           print_r($e);
        }
        $output = $shell->get_output();

        $folder = new Folder($this->get_project_path('wordpress'));
        $folder->delete(true);
        
        return $output;
    }
    /**
     * List of project.
     *
     *
     * @return list of all projects under wordpress
     */
    function get_project_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();
        $folder = new Folder(self::PATH_WORDPRESS);
        if($folder->exists())
        {
           $list = $folder->get_listing(true,false);
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

        $folder = new Folder($this->get_project_path($folder_name));
        $folder->delete(true);
    }   
}