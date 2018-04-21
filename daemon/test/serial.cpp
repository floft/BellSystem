/*
 * Helps debugging whether the serial port is connected correctly without
 * having to wait an entire minute between each test by using the bellsystem
 * daemon
 */
#include <string>
#include <iostream>
#include <unistd.h>     // sleep
#include <fcntl.h>      // O_RDWR, O_NDELAY
#include <sys/ioctl.h>
#include <sys/types.h>  //TIOCMSET
#include <sys/stat.h>

//for USB to Serial adapter
const int set_bits_off = 0;
const int set_bits_on  = 6;

void turn_on_serial(const std::string& device, const int& seconds)
{
    int fd;

    if ((fd = open(device.c_str(), O_RDWR | O_NDELAY)) < 0)
        std::cerr << "could not turn device on" << std::endl;

    ioctl(fd, TIOCMSET, &set_bits_on);
    sleep(seconds);
    ioctl(fd, TIOCMSET, &set_bits_off);
    close(fd);
}

int main()
{
    int seconds = 3;
    std::string device = "/dev/ttyUSB0";

    // Turn on for X seconds, then off for X seconds, then on, ...
    while (true)
    {
        std::cout << "Bell on" << std::endl;
        turn_on_serial(device, seconds);
        std::cout << "Bell off" << std::endl;
        sleep(seconds);
    }

    return 0;
}
