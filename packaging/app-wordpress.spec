
Name: app-wordpress
Epoch: 1
Version: 2.1.0
Release: 1%{dist}
Summary: WordPress
License: GPLv3
Group: ClearOS/Apps
Packager: Xtreem Solution
Vendor: Xtreem Solution
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-certificate-manager
Requires: app-mariadb
Requires: app-php-engines
Requires: app-web-server >= 1:2.4.0
Requires: unzip
Requires: zip

%description
WordPress website content management system (or CMS).

%package core
Summary: WordPress - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-certificate-manager-core
Requires: app-flexshare-core
Requires: app-mariadb-core
Requires: app-php-engines-core
Requires: app-web-server-core >= 1:2.4.5
Requires: app-webapp >= 1:2.4.0

%description core
WordPress website content management system (or CMS).

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/wordpress
cp -r * %{buildroot}/usr/clearos/apps/wordpress/
rm -f %{buildroot}/usr/clearos/apps/wordpress/README.md
install -d -m 0755 %{buildroot}/var/clearos/wordpress
install -d -m 0775 %{buildroot}/var/clearos/wordpress/backup
install -d -m 0775 %{buildroot}/var/clearos/wordpress/versions

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
%doc README.md
%exclude /usr/clearos/apps/wordpress/packaging
%doc README.md
%exclude /usr/clearos/apps/wordpress/unify.json
%dir /usr/clearos/apps/wordpress
%dir %attr(0755,webconfig,webconfig) /var/clearos/wordpress
%dir %attr(0775,webconfig,webconfig) /var/clearos/wordpress/backup
%dir %attr(0775,webconfig,webconfig) /var/clearos/wordpress/versions
/usr/clearos/apps/wordpress/deploy
/usr/clearos/apps/wordpress/language
/usr/clearos/apps/wordpress/libraries
