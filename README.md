Bell System
===========

This is a fairly simple bell system C++ daemon, which reads an XML file,
and PHP web UI, which can create the XML file. The daemon sets one of the
pins of a serial connector to high to ring the bell.

Depends: glibc, libxml++  
Website: http://floft.net/code/bells/

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
 * Follow the [user guide](http://www.floft.net/code/bells/) to set up the bell
   schedule. Make sure to change the school start/end or else it'll never ring.

To uninstall:

    sudo systemctl disable bellsystem
    sudo systemctl stop bellsystem
    sudo unlink /etc/apache2/sites-enabled/httpd-bellsystem-root.conf
    cd BellSystem
    sudo make PREFIX=/usr uninstall

# Arch Linux ARM Installation
If you're using Arch Linux ARM, then I provided a
[PKGBUILD](https://github.com/floft/PKGBUILDs/tree/master/bellsystem-git) for
this. Note this was before Systemd was around though. This should be a starting
point though. I'd recommend using Raspbian at the moment unless you want to
modify the PKGBUILD.
