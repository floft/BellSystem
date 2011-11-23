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

bool within_when(const date& today, const ptime& now, const Config::when& w)
{
	if (
		(today == w.start || (w.end != not_a_date_time && today >= w.start && today <= w.end)) &&	//today is the day or within the range of days
		(now >= w.start_time || date != w.start) &&							//if first day, after start time
		(now <= w.end_time   || w.end == not_a_date_time || date != w.end) &&				//if last day,  before end time
		(now >= w.period_start && now <= w.period_end)							//now is between period start and end
	)
		return true;
	else
		return false;
}

bool in_times(const ptime& now, const vector<Config::time>& times)
{
	for (unsigned int i = 0; i < times.size(); ++i)
	{
		const Config::time& t = times[i];

		if (now.hours() == t.h && now.minutes() == t.m)
			return true;
	}

	return false;
}

void daemon(string filename)
{
	using namespace boost::gregorian;
	using namespace boost::posix_time;

	ifstream ifile(filename.c_str());
	if  (!ifile) error("could not read config");
	ifile.close();
	
	Config config(filename);
	const Config::Settings&         settings  = config.get_settings();
	const vector<string>&           defaults  = config.get_defaults();
	const vector<Config::when>&     quiets    = config.get_quiets();
	const vector<Config::when>&     overrides = config.get_overrides();
	const vector<Config::schedule>& schedules = config.get_schedules();

	date  today = day_clock::local_day();
	ptime now   = second_clock::local_time();
	
	//exit if in quiet period
	for (unsigned int i = 0; i < quiets.size(); ++i)
		if (within_when(today, now, &quiets[i]))
			return;

	//use override schedule
	string id;

	for (unsigned int i = 0; i < overrides.size(); ++i)
	{
		if (within_when(today, now, &overrides[i]))
		{
			id = overrides[i].exec;
			break;
		}
	}
	

	if (schedule.length() > 0)
	{
		bool set = false;
		Config::schedule schedule;

		for (unsigned int i = 0; i < schedules.size(); ++i)
		{
			if (schedules[i].id = id)
			{
				set = true;
				schedule = schedules[i];
				break;
			}
		}

		if (set)
		{
			if (in_times(now, &schedules[i].times))
				turn_on(settings.device, settings.length);
		}
	}

	//defaults
	
	//find default schedule for today
	//ring if in_times
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
