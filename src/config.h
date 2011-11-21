/*
 * This will read the config.xml file and provide easy access to the data.
 */

#ifndef H_BELLCONFIG
#define H_BELLCONFIG

#include <iostream>		//for testing
#include <string>
#include <vector>
#include <sstream>
#include <libxml++/libxml++.h>
#include <boost/date_time/gregorian/gregorian.hpp>
#include <boost/date_time/posix_time/posix_time.hpp>
#include "split.h"

using namespace std;
using namespace xmlpp;
using namespace boost::gregorian;
using namespace boost::posix_time;

class Config
{
public:
	class Error
	{
	public:
		Error(string s) :s(s) { }
		string what() { return s; }
	private:
		string s;
	};

	Config(string filename);

	int string_to_int(const string& input) const;
	int ustring_to_int(const Glib::ustring& input) const;
	
private:
	void recursive(const Node* node);

	struct Settings
	{
		Settings() :length(0) { }
		int length;
		string device;
		date start;
		date end;
	};

	struct time
	{
		time() :h(0), m(0) { }
		int h;
		int m;
	};
	
	struct when
	{
		string exec;
		time start_time;
		time end_time;
		date start;
		date end;
	};

	struct schedule
	{
		string id;
		string name;
		vector<time> times;
	};

	Settings settings;
	vector<string> defaults;
	vector<when> quiets;
	vector<when> overrides;
	vector<schedule> schedules;
};

#endif
