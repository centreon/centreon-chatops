%define debug_package %{nil}
%define version 1.0.0
%define stage   beta.1
%define release 0.%{stage}
Name:           centreon-chatops
Version:        %{version}
Release:        %{release}%{?dist}
Summary:        This module provide the communication between a Team chat like Mattermost or Slack and Centreon throught slash command.
Group:          System Environment/Base
License:        Apache-2.0
URL:            https://github.com/centreon/centreon-chatops
Source0:        %{name}-%{version}-%{stage}.tar.gz
BuildRequires:  centreon-devel
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch:      noarch

%description
This module provide the communication between a Team chat like Mattermost or
Slack and Centreon throught slash command.


######################################################
# Prepare the build
######################################################
%prep
%setup -q -n %{name}-%{version}-%{stage}

%install
# Install centreon-open-tickets web files
%{__install} -d $RPM_BUILD_ROOT%{centreon_www}/modules/
%{__install} -d $RPM_BUILD_ROOT%{centreon_www}/modules/%{name}
%{__cp} -rp src/* $RPM_BUILD_ROOT%{centreon_www}/modules/%{name}

%clean
rm -rf $RPM_BUILD_ROOT

######################################################
# Package centreon-open-tickets
######################################################
%files
%defattr(-,apache,apache,-)
%{centreon_www}/modules/%{name}

%changelog
* Tue Aug 23 2018 Centreon Team 1.0.0-beta.2
- Command: [realtime] Fix the list status service command
- Comment: [realtime] Add the filter status in list status service command

* Mon Jul 30 2018 Centreon Team 1.0.0-beta.1
- Impletation of Mattermost connector
- Command: [realtime] Add listing of no ok host
- Command: [realtime] Add listing of no ok service
- Command: [realtime] Add acknownledge host
- Command: [realtime] Add acknownledge service
- Command: [realtime] Add downtime host
- Command: [realtime] Add downtime service
