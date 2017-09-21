
Name: app-wordpress
Epoch: 1
Version: 2.0.0.1
Release: 1%{dist}
Summary: **wordpress_app_name**
License: GPL
Group: ClearOS/Apps
Packager: Xtreem Solution
Vendor: Xtreem Solution
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-web-server
Requires: app-mariadb
Requires: unzip
Requires: zip

%description
**wordpress_app_description**

%package core
Summary: **wordpress_app_name** - Core
License: LGPL
Group: ClearOS/Libraries
Requires: app-base-core
Requires: mod_authnz_external
Requires: mod_authz_unixgroup
Requires: mod_ssl
Requires: phpMyAdmin
Requires: app-flexshare-core

%description core
**wordpress_app_description**

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/wordpress
cp -r * %{buildroot}/usr/clearos/apps/wordpress/

install -d -m 0775 %{buildroot}/var/clearos/wordpress
install -d -m 0775 %{buildroot}/var/clearos/wordpress/backup
install -d -m 0775 %{buildroot}/var/clearos/wordpress/sites
install -d -m 0775 %{buildroot}/var/clearos/wordpress/versions
install -D -m 0644 packaging/app-wordpress.conf %{buildroot}/etc/httpd/conf.d/app-wordpress.conf

%post
logger -p local6.notice -t installer 'app-wordpress - installing'

%post core
logger -p local6.notice -t installer 'app-wordpress-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/wordpress/deploy/install ] && /usr/clearos/apps/wordpress/deploy/install
fi

[ -x /usr/clearos/apps/wordpress/deploy/upgrade ] && /usr/clearos/apps/wordpress/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-wordpress - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-wordpress-core - uninstalling'
    [ -x /usr/clearos/apps/wordpress/deploy/uninstall ] && /usr/clearos/apps/wordpress/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/wordpress/controllers
/usr/clearos/apps/wordpress/htdocs
/usr/clearos/apps/wordpress/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/wordpress/packaging
%exclude /usr/clearos/apps/wordpress/unify.json
%dir /usr/clearos/apps/wordpress
%dir %attr(0775,webconfig,webconfig) /var/clearos/wordpress
%dir %attr(0775,webconfig,webconfig) /var/clearos/wordpress/backup
%dir %attr(0775,webconfig,webconfig) /var/clearos/wordpress/sites
%dir %attr(0775,webconfig,webconfig) /var/clearos/wordpress/versions
/usr/clearos/apps/wordpress/deploy
/usr/clearos/apps/wordpress/language
/usr/clearos/apps/wordpress/libraries
/etc/httpd/conf.d/app-wordpress.conf
