/*
 * This will read the config.xml file and provide easy access to the data.
 *
 * Useful:
 *  http://www.linuxquestions.org/questions/programming-9/retrieving-element-content-
 *    in-libxml-1-0-a-257672/
 *  http://git.gnome.org/browse/libxml++/tree/examples/dom_parser/main.cc
 */

#ifndef H_BELLCONFIG
#define H_BELLCONFIG

#include <string>
#include <vector>
#include <iostream>
#include <libxml++/libxml++.h>
#include "date.h"
#include "split.h"
#include "string_functions.h"

using namespace std;
using namespace xmlpp;

class Config
{
public:
	Config(const string& filename);

	static const int min_length = 1;
	static const int max_length = 10;

	class Error
	{
	public:
		Error(const string& ss) :s(ss) { }
		string what() const { return s; }
	private:
		string s;
	};

	struct Settings
	{
		Settings() :length(0) { }
		int length;
		string device;
		DateTime::date start;
		DateTime::date end;
	};
	
	struct when
	{
		string exec;
		DateTime::date start;
		DateTime::date end;
		DateTime::time start_time;	//starts at certain time on a day
		DateTime::time end_time;
		DateTime::time period_start;	//certian times during these days
		DateTime::time period_end;
	};

	struct schedule
	{
		string id;
		string name;
		vector<DateTime::time> times;
	};
	
	Settings         get_settings()  const { return settings;  }
	vector<string>   get_defaults()  const { return defaults;  }
	vector<when>     get_quiets()    const { return quiets;    }
	vector<when>     get_overrides() const { return overrides; }
	vector<schedule> get_schedules() const { return schedules; }
	
private:
	void add_whens(const NodeSet& nodeset, vector<when>& whens);

	Settings         settings;
	vector<string>   defaults;
	vector<when>     quiets;
	vector<when>     overrides;
	vector<schedule> schedules;

	friend ostream& operator<<(ostream& os, const Config& c);
};

ostream& operator<<(ostream& os, const Config::Settings& s);
ostream& operator<<(ostream& os, const Config::when& w);
ostream& operator<<(ostream& os, const Config::schedule& s);
ostream& operator<<(ostream& os, const Config& c);
#endif
