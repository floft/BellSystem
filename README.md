Bell System
===========

This is a fairly simple bell system C++ daemon, which reads an XML file,
and PHP web UI, which can create the XML file. The daemon sets one of the
pins of a serial connector to high to ring the bell.

Depends: glibc, libxml++
Website: http://floft.net/wiki/Bells.html

# Arch Linux ARM Installation
If you're using Arch Linux ARM, then I provided a
[PKGBUILD](https://github.com/floft/PKGBUILDs/tree/master/bellsystem-git) for
this. Note this was before Systemd was around though. This should be a starting
point though. You can also see the Raspbian instructions below.

# Raspbian Installation
Since it it more likely you'll be using Raspbian than Arch Linux ARM, I'll
provide instructions for Raspbian.

First, put Raspbian on an SD card and boot up your Raspberry Pi. See the
[Raspberry Pi website](https://www.raspberrypi.org/downloads/raspbian/).

Second, boot it up and enable SSH access if you wish. Default user is "pi" and
password is "raspberry". After running the following command, it's under
Interfacing Options --> SSH --> Enable. Make sure you change the password
before you do this if you want any sort of security.

    sudo raspi-config

Third, you can set up the bell system.

    sudo apt install libglibmm-2.4-dev libxml++2.6-dev apache2 libapache2-mod-php git
    git clone https://github.com/floft/BellSystem
    cd BellSystem
    make
    sudo make PREFIX=/usr install
    sudo systemctl enable bellsystem
    sudo systemctl start bellsystem

Finally, you need to:

 * Run `sudo bellsystem-password` to change the website user interface password.
 * Manually change the device location in */usr/share/webapps/bellsystem/config.xml*.
 * Add one of the following two lines to */etc/apache2/apache2.conf*
    - To put the website at /: *sudo ln -s /etc/apache2/conf-{available,enabled}/httpd-bellsystem-root.conf*
    - To put the website at /bellsystem: *sudo ln -s /etc/apache2/conf-{available,enabled}/httpd-bellsystem.conf*
    - Restart Apache: `sudo systemctl restart apache2`

To uninstall:

    sudo systemctl disable bellsystem
    sudo systemctl stop bellsystem
    sudo unlink /etc/apache2/conf-enabled/httpd-bellsystem.conf
    sudo unlink /etc/apache2/conf-enabled/httpd-bellsystem-root.conf
    cd BellSystem
    sudo make PREFIX=/usr uninstall
