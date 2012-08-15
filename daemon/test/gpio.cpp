#include <fstream>
#include <iostream>
#include <unistd.h>

using namespace std;

void gpio_write(const string& file, const string& contents)
{
	try
	{
		ofstream ofile(file.c_str(), ios_base::out|ios_base::trunc);
		ofile.exceptions(ios_base::badbit|ios_base::failbit);
		ofile << contents << endl;
		ofile.close();
	}
	catch (const ios_base::failure& e)
	{
		cerr << "could not write to gpio" << endl;
	}
}

void turn_on_gpio(const int& seconds)
{
	gpio_write("/sys/class/gpio/export",            "4");
	gpio_write("/sys/class/gpio/gpio4/direction",   "out");
	gpio_write("/sys/class/gpio/gpio4/value",       "1");
	sleep(seconds);
	gpio_write("/sys/class/gpio/gpio4/value",       "0");
}

int main()
{
	turn_on_gpio(3);
}
