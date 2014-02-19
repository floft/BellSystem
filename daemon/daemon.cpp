#include <string>
#include <vector>
#include <sstream>
#include <fstream>
#include <iostream>
#include <stdexcept>
#include <getopt.h>
#include <unistd.h>		//sleep, fork
#include <fcntl.h>		//O_RDWR, O_NDELAY
#include <stdlib.h>		//atoi
#include <sys/ioctl.h>
#include <sys/types.h>		//TIOCMSET
#include <sys/stat.h>

#include "wait.h"
#include "date.h"
#include "config.h"

using namespace std;

//for USB to Serial adapter
const int set_bits_off = 0;
const int set_bits_on  = 6;

void help()
{
	cout << "Usage: bell-daemon [-d] [-f] [-l /log.txt] -c /config.xml" << endl;
}

void error(const string& s)
{
	throw runtime_error(s);
}

void log(const string& s, const string& filename, const bool& background)
{
	DateTime::now n;

	ostringstream ss;
	ss << "[" << n << "] ";
	string timestamp = ss.str();

	if (!background)
		cerr << timestamp << s << endl;

	if (filename != "")
	{
		ofstream ofile(filename.c_str(), ios_base::out|ios_base::app);
		ofile.exceptions(ios_base::badbit|ios_base::failbit);

		if  (!ofile)
		{
			if (!background)
				cerr  << timestamp << "Error: could not write to log" << endl;
		}
		else
		{
			ofile << timestamp << s << endl;
		}

		ofile.close();
	}
}

void turn_on(const string& device, const int& seconds)
{
	int fd;
	
	if ((fd = open(device.c_str(), O_RDWR | O_NDELAY)) < 0)
		error("could not turn device on");

	ioctl(fd, TIOCMSET, &set_bits_on);
	sleep(seconds);
	ioctl(fd, TIOCMSET, &set_bits_off);
	close(fd);
}

void gpio_write(const string& file, const string& contents, const bool& ignore = false)
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
		if (!ignore)
			error("could not write to gpio");
	}
}

vector<int> parse_gpio_list(const string gpio_pins_string)
{
	stringstream gpio_pins_stringstream(gpio_pins_string);
	string item;
	vector<int> pin_int_vector;
	
	while(getline(gpio_pins_stringstream, item, ','))
	{
        pin_int_vector.push_back(atoi(item.c_str()));
	}
	return pin_int_vector;
}

void init_gpio(string gpio_pins_string)
{
	// parse pin list, and init each pin to be used
	vector<int> pins = parse_gpio_list(gpio_pins_string);
	int pin;
	
	for( int i = 0; i < pins.size(); i++ )
	{
		pin = pins[i];
		std::stringstream file_ss, pin_ss;
		pin_ss << pin;
		file_ss << "/sys/class/gpio/gpio" << pin << "/direction";

		gpio_write("/sys/class/gpio/export", pin_ss.str(), true);
		gpio_write(file_ss.str(),	"out");
	}
}

void turn_on_gpio(const string gpio_pins_string, const int& seconds)
{
	// parse pin list, and init each pin to be used
	vector<int> pins = parse_gpio_list(gpio_pins_string);
	int pin;
	stringstream file_ss;
	
	for( int i = 0; i < pins.size(); i++ )
	{
		pin = pins[i];
		file_ss << "/sys/class/gpio/gpio" << pin << "/value";
		gpio_write(file_ss.str(),	"1");
	}
	
	sleep(seconds);
	
	for( int i = 0; i < pins.size(); i++ )
	{
		pin = pins[i];
		file_ss << "/sys/class/gpio/gpio" << pin << "/value";
		gpio_write(file_ss.str(),	"0");
	}
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
		   const Config::Settings& settings, const DateTime::time& now,
		   const bool& debug = false)
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
			if (debug)
			{
				cout << "Ring" << endl;
			}
			else
			{
				if (settings.gpio)
					turn_on_gpio(settings.gpio_pin, settings.length);
				else
					turn_on(settings.device, settings.length);
			}
		}
	}

	return set;
}

void check_ring(const Config& config, const bool& debug = false)
{
	
	DateTime::now n;
	const Config::Settings&         settings  = config.get_settings();
	const vector<string>&           defaults  = config.get_defaults();
	const vector<Config::when>&     quiets    = config.get_quiets();
	const vector<Config::when>&     overrides = config.get_overrides();
	const vector<Config::schedule>& schedules = config.get_schedules();

	//exit if not during school start/stop
	if (settings.start > n.d || settings.end < n.d)
		return;
	
	//exit if in quiet period
	for (unsigned int i = 0; i < quiets.size(); ++i)
		if (within_when(n, quiets[i]))
			return;

	//use override schedule
	bool use_override = false;
	string override_id;

	for (unsigned int i = 0; i < overrides.size(); ++i)
	{
		if (within_when(n, overrides[i]))
		{
			use_override = true;
			override_id = overrides[i].exec;
			break;
		}
	}

	if (use_override)
	{
		if (override_id.length() > 0)
		{
			if (!ring_schedule(override_id, schedules, settings, n.t, debug))
				error("schedule with specified id does not exist");

			return;
		} else error("override exec blank");
	}

	//defaults
	const string& default_id = defaults[n.dow];
	
	if (default_id.length() > 0)
		if (!ring_schedule(default_id, schedules, settings, n.t, debug))
			error("schedule with specified id does not exist");
}

void load_config(Config& config, const string& filename, const string& logfile, const bool& background)
{
	try
	{
		config = Config(filename);
	}
	catch (Config::Error& e)
	{
		log("Config Error: " + e.what(), logfile, background);
	}
	catch (DateTime::time::Invalid& e)
	{
		log("Error: time not in 00:00 format", logfile, background);
	}
	catch (DateTime::date::Invalid& e)
	{
		log("Error: date not in YYYYMMDD format", logfile, background);
	}
	catch (std::exception& e)
	{
		ostringstream ss;
		ss << "Error: " << e.what();

		log(ss.str(), logfile, background);
	}
	catch (...)
	{
		log("Unexpected Exception", logfile, background);
	}
	
	
	//setup gpio
	{
		const Config::Settings& settings  = config.get_settings();
		
		if (settings.gpio)
		{
			// gpio_write("/sys/class/gpio/export",		"4", true);
			init_gpio(settings.gpio_pin);
		}
	}

}

int main(int argc, char *argv[])
{
	int c;
	bool debug = false;
	bool background = true;
	string filename;
	string logfile;

	while ((c = getopt(argc, argv, "c:l:hdf")) != -1)
	{
		switch (c)
		{
			case 'c':
				filename=optarg;
				break;
			case 'l':
				logfile=optarg;
				break;
			case 'f':
				background=false;
				break;
			case 'd':
				debug = true;
				break;
			case 'h':
				help();
				return 0;
			case '?':
				help();
				return 1;
		}
	}
	
	ifstream ifile(filename.c_str());
	if  (!ifile)
	{
		log("Exiting. Error: could not read config", logfile, background);
		return 1;
	}
	ifile.close();

	if (background)
	{
		pid_t pid = fork();

		if (pid<0)		//error occurred
		{
			log("Exiting. Error: could not create child process", logfile, background);
			return 1;
		}

		if (pid>0) return 0;	//exit the parent

		setsid();
	}
	
	struct stat attributes;
	stat(filename.c_str(), &attributes);
	int lastmodified = attributes.st_mtime;

	Config config;
	load_config(config, filename, logfile, background);

	while (true)
	{
		try
		{
			check_ring(config, debug);
		}
		catch (std::exception& e)
		{
			ostringstream ss;
			ss << "Error: " << e.what();

			log(ss.str(), logfile, background);
		}
		catch (...)
		{
			log("Unexpected Exception", logfile, background);
		}
		
		//don't loop until at least one second after the minute
		//wait_till_minute() will terminate immediately if
		//current second = 0, which it would on error
		sleep(1);

		//save time by checking after ring (instead of before)
		stat(filename.c_str(), &attributes);

		if (attributes.st_mtime > lastmodified)
		{
			load_config(config, filename, logfile, background);
			lastmodified = attributes.st_mtime;
		}

		Wait::wait_till_minute();
	}

	return 0;
}
