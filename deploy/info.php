<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'wordpress';
$app['version'] = '1.0.0';
$app['release'] = '1';
$app['vendor'] = 'Xtreem Solution'; // e.g. Acme Co
$app['packager'] = 'Xtreem Solution'; // e.g. Gordie Howe
$app['license'] = 'GPL'; // e.g. 'GPLv3';
$app['license_core'] = 'LGPL'; // e.g. 'LGPLv3';
$app['description'] = lang('wordpress_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('wordpress_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_web');


/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////


$app['core_requires'] = array(
    'mod_authnz_external',
    'mod_authz_unixgroup',
    'mod_ssl',
    'phpMyAdmin',
);

$app['requires'] = array(
    'app-web-server',
    'app-mariadb',
    'unzip',
    'zip',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/wordpress' => array(
        'mode' => '0755',
        'owner' => 'webconfig',
        'group' => 'webconfig'
	),
    '/var/clearos/wordpress/backup' => array(
        'mode' => '0755',
        'owner' => 'webconfig',
        'group' => 'webconfig'
	),
    '/var/clearos/wordpress/versions' => array(
        'mode' => '0755',
        'owner' => 'webconfig',
        'group' => 'webconfig'
    ),
    '/var/clearos/wordpress/sites' => array(
        'mode' => '0755',
        'owner' => 'webconfig',
        'group' => 'webconfig'
	)
);

$app['core_file_manifest'] = array(
    'app-wordpress.conf'=> array('target' => '/etc/httpd/conf.d/app-wordpress.conf'),
);
