# autoinstall2
Web front to use the autoinstall functionnality of a cisco device.
Create the equipements and the templates, then generate the *-confg.

This repository is the app directory of an CakePHP application.

It requires a tftp server (for now, the generated files are put in /srv/tftp)
and a DHCP server (isc-dhcp-server only).

For the DHCP part, it requiere a template: 
```
ddns-update-style none;
default-lease-time 600;
max-lease-time 7200;
log-facility local7;

option option-150 code 150 = ip-address;

subnet 192.168.1.0 netmask 255.255.255.0 {
  range 192.168.1.101 192.168.1.200;
  option option-150 192.168.1.5;
}



##autoinstall
```
`##autoinstall` is replaced by the generated code.
The web service must be able to write in `/etc/dhcp/dhcpd.conf`
The DHCP template must be `/etc/dhcp/dhcpd.conf.template`

# TODO
* Remove hardcoded variables: paths, reload commands, ...
* Edit the variables of the equipements
