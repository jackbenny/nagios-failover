#!/usr/bin/php
<?php
/*
    Copyright (C) 2014 Jack-Benny Persson <jack-benny@cyberinfo.se>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
    Simple script to turn on/off Nagios notification/checks etc for use with
    Nagios failover hosts.
    Default is to turn on/off notifications.
    Version 0.2
*/

// Variables to set for your environment
$remoteHost = "testlab.lab";
$checkCmd = "/usr/lib/nagios/plugins/check_nrpe -H $remoteHost -c check_nagios";
$checkTmpDir = "/tank/home/jake/failover";
$checkTmpFile = "check_file";
$maxCheckFileAge = "1200";
$enableCmd = "/usr/share/nagios3/plugins/eventhandlers/enable_notifications";
$disableCmd = "/usr/share/nagios3/plugins/eventhandlers/disable_notifications";

// Run the command to check if the remote Nagios is up
if ((runCmd($checkCmd)) == 0)
{
    // If it's up, then update the modify time of the temp file
    updateFile($checkTmpDir . "/" . $checkTmpFile);
    // Then run the "disable" command(s) to stop the local Nagios notifications
    if ((runCmd($disableCmd)) != 0)
    {
        print "Couldn't execute $disableCmd\n";
        exit(1);
    }
    else
    {
        // If the above command executed successfully, then just quit
        exit(0);
    }
}

// If the remote Nagios was NOT up and running, execute the code here
else
{
    // Start with if the age of the temp file has exceeded $maxCheckFileAge
    if ((checkAge($checkTmpDir . "/" . $checkTmpFile, $maxCheckFileAge)) == 1)
    {
        // If it has, enable the local Nagios and check if that command did run
        if ((runCmd($enableCmd)) == 0)
        {
            // If we were successful in enabling the local Nagios, then print
            // a message about it and quit
            print "Failover Nagios is now active!\n";
            exit(0);
        }
        else
        {
            // If we couln't execute the local Nagios, issue a message about it
            print "Couldn't execute $enableCmd\n";
            exit(1);
        }
    }
}

// All of the functions below

// Generic function to execute commands on the system
function runCmd($cmd)
{
    exec($cmd . " 2> /dev/null", $cmdOutput, $cmdReturn);
    return $cmdReturn;
}

// Function to touch (update file modify date) of the temp check file
function updateFile($file)
{
    if ((touch ($file)) === false)
    {
        print "Couldn't write file $file\n";
        exit(1);
    }
}

// Function to check the age of the temp check file.
// Return 0 if the file age is within acceptable age (<$maxAge) and 1 otherwise
function checkAge($file, $maxAge)
{
    if (file_exists($file))
    {
        if (time() - filemtime($file) > $maxAge)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
    else
    {
        // Print an error message if the file does not exist
        print "File $file does not exists\n";
        exit(1);
    }
}

?>
