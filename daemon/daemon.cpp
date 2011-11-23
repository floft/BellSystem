#include <string>
#include <vector>
#include <fstream>
#include <iostream>
#include <stdexcept>
#include <getopt.h>
#include <unistd.h>		//sleep
#include <fcntl.h>		//O_RDWR, O_NDELAY
#include <sys/ioctl.h>		//ioctl
#include <sys/types.h>		//TIOCMSET

#include "date.h"
#include "config.h"

using namespace std;

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

void turn_on(const string& device, const int& seconds)
{
	int fd;
	
	if ((fd = open(device.c_str(), O_RDWR | O_NDELAY)) < 0)
		error("could not turn device on");

	ioctl(fd, TIOCMSET, &set_bits_on);
	sleep(seconds);
	close(fd);
}

bool within_when(const DateTime::now& n, const Config::when& w)
{
	if (
		//today is the day or within the range of days
		(n.d == w.start || (w.end != DateTime::date() && n.d >= w.start && n.d <= w.end)) &&
		//if first day, after start time
		(n.t >= w.start_time || n.d != w.start) &&
		//if last day,  before end time
		(n.t <= w.end_time || w.end == DateTime::date() || n.d != w.end) &&
		//now is between period start and end
		(n.t >= w.period_start && n.t <= w.period_end)
	)
		return true;
	else
		return false;
}

bool in_times(const DateTime::time& t, const vector<DateTime::time>& times)
{
	for (unsigned int i = 0; i < times.size(); ++i)
		if (t == times[i])
			return true;

	return false;
}

bool ring_schedule(const string& id, const vector<Config::schedule>& schedules,
		   const Config::Settings& settings, const DateTime::time& now)
{
	bool set = false;
	Config::schedule schedule;

	for (unsigned int i = 0; i < schedules.size(); ++i)
	{
		if (schedules[i].id == id)
		{
			set = true;
			schedule = schedules[i];
			break;
		}
	}

	if (set)
	{
		if (in_times(now, schedule.times))
		{
			turn_on(settings.device, settings.length);
		}
	}

	return set;
}

void daemon(const string& filename)
{
	ifstream ifile(filename.c_str());
	if  (!ifile) error("could not read config");
	ifile.close();
	
	DateTime::now n;
	const Config&			config(filename);
	const Config::Settings&         settings  = config.get_settings();
	const vector<string>&           defaults  = config.get_defaults();
	const vector<Config::when>&     quiets    = config.get_quiets();
	const vector<Config::when>&     overrides = config.get_overrides();
	const vector<Config::schedule>& schedules = config.get_schedules();
	
	//exit if in quiet period
	for (unsigned int i = 0; i < quiets.size(); ++i)
		if (within_when(n, quiets[i]))
			return;

	//use override schedule
	string override_id;

	for (unsigned int i = 0; i < overrides.size(); ++i)
	{
		if (within_when(n, overrides[i]))
		{
			override_id = overrides[i].exec;
			break;
		}
	}

	if (override_id.length() > 0)
	{
		if (!ring_schedule(override_id, schedules, settings, n.t))
			error("schedule with specified id does not exist");

		return;
	} else error("override exec blank");

	//defaults
	const string& default_id = defaults[n.dow];
	
	if (default_id.length() > 0)
		if (!ring_schedule(default_id, schedules, settings, n.t))
			error("schedule with specified id does not exist");
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
	catch (DateTime::time::Invalid& e)
	{
		cerr << "Error: time not in 00:00 format" << endl;
		return 1;
	}
	catch (DateTime::date::Invalid& e)
	{
		cerr << "Error: date not in YYYYMMDD format" << endl;
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
