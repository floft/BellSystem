Bell System
===========

This is a fairly simple bell system C++ daemon, which reads an XML file,
and PHP web UI, which can create the XML file. The daemon has three options for turning bells on or off:
 * Set one of the pins of a serial connector to high
 * Set a GPIO pin high
 * Execute a command

Features:
 * Create any number of named schedules
 * Set a default schedule for each day of the week (or none, e.g. on weekends)
 * Set quiet periods where no bells will ring (e.g. on holidays or break)
 * Set override schedules (e.g. a half day or a special event on a particular day)
 * Backup and restore

Depends: glibc, libxml++
Website: https://floft.net/code/bells/

[![BellSystem Web UI](https://raw.githubusercontent.com/floft/BellSystem/master/images/website.png)](https://raw.githubusercontent.com/floft/BellSystem/master/images/website.png)

# Raspbian
## Install
First, put Raspbian on an SD card and boot up your Raspberry Pi. See the
[Raspberry Pi website](https://www.raspberrypi.org/downloads/raspbian/).

Second, boot it up and adjust a couple settings. Default user is "pi" and
password is "raspberry".  Run `sudo raspi-config`, then:
 * Enable SSH access if you wish. Interfacing Options --> SSH --> Enable. Make
   sure you change the password before you do this if you want any sort of
   security.
 * Also, you probably want to set the timezone, under Localisation --> Change
   Timezone. Otherwise all your times in the config file and website UI have to
   be in UTC.

Third, you can set up the bell system.

    sudo apt install libglibmm-2.4-dev libxml++2.6-dev apache2 libapache2-mod-php php7.0-xml git
    git clone https://github.com/floft/BellSystem
    cd BellSystem
    make
    sudo make PREFIX=/usr install
    sudo systemctl enable bellsystem
    sudo systemctl start bellsystem

To setup the website with Apache:

    sudo unlink /etc/apache2/sites-enabled/000-default.conf
    sudo ln -s /etc/apache2/sites-{available,enabled}/httpd-bellsystem-root.conf
    sudo systemctl restart apache2

Finally, you need to:

 * Run `sudo bellsystem-password` to change the website user interface password.
 * Manually change the device location, GPIO pin, or command you want to run in
   */usr/share/webapps/bellsystem/config.xml* since those you can't change from
   the website for security.
 * Follow the [user guide](https://floft.net/code/bells/) to set up the bell
   schedule. Make sure to change the school start/end or else it'll never ring.

That's it! Find the IP address of your Raspberry Pi and go to
*http://ipaddress* in a web browser.

If you're interested in debugging serial, you might build the "serial.out" test
which alternates setting the voltage on serial high and low every 3 seconds.
You probably want pins 5 (ground) and 7 (RTS, changed between high/low
voltage).

    make tests
    ./daemon/test/serial.out

## Update
To upgrade (if desired):

    cd Bellsystem
    git pull
    make
    sudo make PREFIX=/usr install
    sudo systemctl daemon-reload
    sudo systemctl restart bellsystem apache2

## Uninstall
To uninstall (note that this deletes your config file and website password
file):

    sudo systemctl disable bellsystem
    sudo systemctl stop bellsystem
    sudo unlink /etc/apache2/sites-enabled/httpd-bellsystem-root.conf
    cd BellSystem
    sudo make PREFIX=/usr uninstall
