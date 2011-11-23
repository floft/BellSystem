#include <string>
#include <fstream>
#include <iostream>
#include <stdexcept>
#include <getopt.h>
#include <unistd.h>		//sleep
#include <fcntl.h>		//O_RDWR, O_NDELAY
#include <sys/ioctl.h>		//ioctl
#include <sys/types.h>		//TIOCMSET

#include "config.h"

using namespace std;
using namespace xmlpp;

//for USB to Serial adapter
const int set_bits_off = 0;
const int set_bits_on  = 6;

void help()
{
	cout << "Usage: bell-daemon -c /path/to/config.xml" << endl;
}

void error(const string& s)
{
	throw runtime_error(s);
}

void turn_on(string device, int seconds)
{
	int fd;
	
	if ((fd = open(device.c_str(), O_RDWR | O_NDELAY)) < 0)
	{
		error("could not turn device on");
	}

	ioctl(fd, TIOCMSET, &set_bits_on);
	sleep(seconds);
	close(fd);
}

void daemon(string filename)
{
	ifstream ifile(filename.c_str());
	if  (!ifile) error("could not read config");
	ifile.close();
	
	Config config(filename);
	const Config::Settings&         settings  = config.get_settings();
	const vector<string>&           defaults  = config.get_defaults();
	const vector<Config::when>&     quiets    = config.get_quiets();
	const vector<Config::when>&     overrides = config.get_overrides();
	const vector<Config::schedule>& schedules = config.get_schedules();

	//quiets
	//overrides
	//defaults

	turn_on(settings.device, settings.length);
}

int main(int argc, char *argv[])
{
	int c;
	string config;

	while ((c = getopt(argc, argv, "c:h")) != -1)
	{
		switch (c)
		{
			case 'c':
				config=optarg;
				break;
			case 'h':
				help();
				return 0;
			case '?':
				help();
				return 1;
		}
	}

	try
	{
		daemon(config);
	}
	catch (Config::Error& e)
	{
		cerr << "Config Error: "  << e.what() << endl;
		return 1;
	}
	catch (std::exception& e)
	{
		cerr << "Error: " << e.what() << endl;
		return 1;
	}
	catch (...)
	{
		cerr << "Unexpected Exception" << endl;
		return 1;
	}

	return 0;
}
