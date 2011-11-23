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
	Config(string filename);

	class Error
	{
	public:
		Error(string s) :s(s) { }
		string what() { return s; }
	private:
		string s;
	};

	struct Settings
	{
		Settings() :length(0) { }
		int length;
		string device;
		date start;
		date end;
	};
	
	struct when
	{
		string exec;
		date start;
		date end;
		time start_time;	//starts at certain time on a day
		time end_time;
		time period_start;	//certian times during these days
		time period_end;
	};

	struct schedule
	{
		string id;
		string name;
		vector<time> times;
	};
	
	Settings         get_settings()  { return settings;  }
	vector<string>   get_defaults()  { return defaults;  }
	vector<when>     get_quiets()    { return quiets;    }
	vector<when>     get_overrides() { return overrides; }
	vector<schedule> get_schedules() { return schedules; }
	
private:
	void recursive(const Node* node);
	void add_whens(NodeSet& nodeset, vector<when>& whens);

	Settings         settings;
	vector<string>   defaults;
	vector<when>     quiets;
	vector<when>     overrides;
	vector<schedule> schedules;

	friend ostream& operator<<(ostream& os, const Config& c);
};

ostream& operator<<(ostream& os, const Config::Settings& s);
ostream& operator<<(ostream& os, const Config::time& t);
ostream& operator<<(ostream& os, const Config::when& w);
ostream& operator<<(ostream& os, const Config::schedule& s);
ostream& operator<<(ostream& os, const Config& c);
#endif
