<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'wordpress';
$app['version'] = '2.1.0';
$app['release'] = '1';
$app['vendor'] = 'Xtreem Solution';
$app['packager'] = 'Xtreem Solution';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('wordpress_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('wordpress_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_web');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['wordpress']['title'] = $app['name'];
$app['controllers']['site']['title'] = lang('base_settings');
$app['controllers']['backup']['title'] = lang('base_backup');
$app['controllers']['version']['title'] = lang('base_version');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-certificate-manager-core',
    'app-flexshare-core',
    'app-mariadb-core',
    'app-php-engines-core',
    'app-web-server-core >= 1:2.4.5',
    'app-webapp >= 1:2.4.0',
);

$app['requires'] = array(
    'app-certificate-manager',
    'app-mariadb',
    'app-php-engines',
    'app-web-server >= 1:2.4.0',
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
        'mode' => '0775',
        'owner' => 'webconfig',
        'group' => 'webconfig'
	),
    '/var/clearos/wordpress/versions' => array(
        'mode' => '0775',
        'owner' => 'webconfig',
        'group' => 'webconfig'
    ),
);
