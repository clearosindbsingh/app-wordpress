<?php

/**
 * WordPress Add project View.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage Views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link    http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */


///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('wordpress');


///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////
$headers = array(
    lang('wordpress_backup_name'),
);


///////////////////////////////////////////////////////////////////////////////
// Buttons
///////////////////////////////////////////////////////////////////////////////

$buttons  = array(
    anchor_custom('/app/wordpress','Back','low')
);



///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////
foreach ($backups as $value) {
    $item['title'] = $value['name'];
    $download_action = "/app/wordpress/backup/download/".$value['name'];
    $delete_action = "/app/wordpress/backup/delete/".$value['name'];
    $item['anchors'] = button_set(
        array(
            anchor_custom($download_action, lang('wordpress_download'), 'high'),
            anchor_delete($delete_action, 'low'),
        )
    );
    $item['details'] = array(
        $value['name']
    );
    $items[] = $item;
}


///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('wordpress_my_backups'),
    $buttons,
    $headers,
    $items
);