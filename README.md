# Nagios Failover #
PHP script to help setting up Nagios with failover hosts. The whole idea of this
script is to run in a cronjob on the failover host. When the remote, default,
Nagios host goes down, the script will stop touching a "check file" to update 
it's timestamp. If the remote Nagios is down for more than the predefined number 
of seconds, the script activates the local Nagios notification. See the flow
chart in this directory for more information on how the scripts works.

## Usage ##
Change the variables at the top of the script to suit your environment. Then
place the script in a crontab (root for example, or someone with Nagios
permissions) and let it run for example every fourth minute. If the $maxAge in
the script is 1200 seconds (20 minutes) and the script is being run every fourth
minute, then the check can fail 5 times (5x4=20) before activating the local
Nagios notifications.

## Misc thoughts ##
Nowadays I only disable notifications on the failover hosts. I used to disable
both host checks, service checks and notifications. Over the years this has
turned out to generate a lot of false alarms. The false alarms are generate when
the failover host for example disables it's checks after being active when a
hosts being monitored where experiencing problems. The checks might than have
stopped in a soft state with lets say 3/4. When the failover host is then once
again activating it's checks, it's starts from this state (3/4) and if something
then, for example, a flaky internet connection makes it fail one more time it
has reached a hard state and an alarm is being sent.
Another problem might be if a host or service was flapping the last time the
failover Nagios was active and then had it's host and service checks disabled.
The next time the failover Nagios is activated, the host/service which was
flapping, is now still considered being in a flapping state, even tough this
might have been two years ago, and thus preventing any alarms until the flapping 
state goes away.
For these reasons I now let the failover host run both host and service checks
even when no failover is necessary.
When the main Nagios then goes down, the failover Nagios has an up-to-date
accurate state of all the hosts and services being monitored. 

## Copyright ##
This was script was written by Jack-Benny Persson and is released under GNU GPL
version 2.
