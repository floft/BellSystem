/*
 * A very simple date class since nothing fancy is needed,
 * only comparisons.
 */

#ifndef H_DATE
#define H_DATE

#include <ctime>
#include <string>
#include <iomanip>	//setprecision
#include <iostream>
#include "split.h"
#include "string_functions.h"

namespace DateTime
{
	using namespace std;

	static const int max_hours   = 23;
	static const int max_minutes = 59;

	class date
	{
	public:
		class Invalid { };

		date() :y(0), m(1), d(1) { }
		date(const int& yy, const int& mm, const int& dd);
		date(const string& s);

		bool valid(const int& yy, const int& mm, const int& dd) const;
		bool leapyear(const int& yy) const;
		int days_in_month(const int& yy, const int& mm) const;
	
		int year()  const { return y; }
		int month() const { return m; }
		int day()   const { return d; }

		void set_year(const int& yy);
		void set_month(const int& mm);
		void set_day(const int& dd);
		void set(const string& s);
		void set(const int& yy, const int& mm, const int& dd);
		
	private:
		int y;
		int m;
		int d;
	
		friend bool operator==(const date& a, const date& b);
		friend bool operator<(const date& a,  const date& b);
		friend bool operator>(const date& a,  const date& b);
		friend ostream& operator<<(ostream& os, const date& d);
	};

	class time
	{
	public:
		class Invalid { };

		time() :h(0), m(0) { }
		time(const string& t);
		time(const int& hh, const int& mm);

		bool valid(const int& hh, const int& mm) const;
	
		int hour()   const { return h; }
		int minute() const { return m; }

		void set_hour(const int& hh);
		void set_minute(const int& mm);
		void set(const string& s);
		void set(const int& hh, const int& mm);
	private:
		int h;
		int m;
	
		friend bool operator==(const time& a, const time& b);
		friend bool operator<(const time& a,  const time& b);
		friend bool operator>(const time& a,  const time& b);
		friend ostream& operator<<(ostream& os, const time& t);
	};

	struct now
	{
		now();
		date d;
		time t;
	};

	bool operator==(const date& a, const date& b);
	bool operator!=(const date& a, const date& b);
	bool operator<(const date& a, const date& b);
	bool operator>(const date& a, const date& b);
	bool operator<=(const date& a, const date& b);
	bool operator>=(const date& a, const date& b);

	bool operator==(const time& a, const time& b);
	bool operator!=(const time& a, const time& b);
	bool operator<(const time& a, const time& b);
	bool operator>(const time& a, const time& b);
	bool operator<=(const time& a, const time& b);
	bool operator>=(const time& a, const time& b);

	ostream& operator<<(ostream& os, const date& d);
	ostream& operator<<(ostream& os, const time& t);
	ostream& operator<<(ostream& os, const now& n);
}

#endif
