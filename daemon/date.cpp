#include <cmath>

#include "date.h"

namespace DateTime
{

	now::now()
		:dow(0)
	{
		time_t current = std::time(0);
		struct tm* tm  = localtime(&current);

		dow = tm->tm_wday;
		t.set(tm->tm_hour, tm->tm_min);
		d.set(tm->tm_year + 1900, tm->tm_mon + 1, tm->tm_mday);
	}

	date::date(const int& yy, const int& mm, const int& dd)
		:y(yy), m(mm), d(dd)
	{
		if (!valid(y,m,d))
			throw Invalid();
	}

	date::date(const string& s)
		:y(0), m(0), d(0)
	{
		set(s);
	}

	void date::set_year(const int& yy)
	{
		y = yy;

		if (!valid(y,m,d))
			throw Invalid();
	}

	void date::set_month(const int& mm)
	{
		m = mm;

		if (!valid(y,m,d))
			throw Invalid();
	}

	void date::set_day(const int& dd)
	{
		d = dd;

		if (!valid(y,m,d))
			throw Invalid();
	}

	void date::set(const int& yy, const int& mm, const int& dd)
	{
		y = yy;
		m = mm;
		d = dd;

		if (!valid(y,m,d))
			throw Invalid();
	}

	void date::set(const string& s)
	{
		if (s.length() != 8) throw Invalid();

		y = string_to_int(s.substr(0,4));
		m = string_to_int(s.substr(4,2));
		d = string_to_int(s.substr(6,2));

		if (!valid(y,m,d))
			throw Invalid();
	}

	bool date::valid(const int& yy, const int& mm, const int& dd) const
	{
		if (mm<0 || mm>12)		return false;
		if (dd<1)			return false;
		if (dd>days_in_month(yy,mm))	return false;

		return true;
	}

	bool date::leapyear(const int& yy) const
	{
		if (yy%4 == 0 && (yy%100 != 0 || yy%400 == 0))
			return true;

		return false;
	}

	int date::days_in_month(const int& yy, const int& mm) const
	{
		int days = 31;

		switch (mm)
		{
			case 2:
				days = (leapyear(yy))?29:28;
				break;
			case 4:
			case 6:
			case 9:
			case 11:
				days = 30;
				break;
		}

		return days;
	}

	time::time(const int& hh, const int& mm)
		:h(hh), m(mm)
	{
		if (!valid(h,m))
			throw Invalid();
	}

	time::time(const string& t)
		:h(0), m(0)
	{
		set(t);
	}

	void time::set_hour(const int& hh)
	{
		h = hh;

		if (!valid(h,m))
			throw Invalid();
	}

	void time::set_minute(const int& mm)
	{
		m = mm;

		if (!valid(h,m))
			throw Invalid();
	}

	void time::set(const int& hh, const int& mm)
	{
		h = hh;
		m = mm;

		if (!valid(h,m))
			throw Invalid();
	}

	void time::set(const string& s)
	{
		vector<string> parts = Split::split(s, ":");

		if (parts.size() != 2)
			throw Invalid();

		h = string_to_int(parts[0]);
		m = string_to_int(parts[1]);

		if (!valid(h,m))
			throw Invalid();
	}

	int time::wrapAround(int v, int delta, int minval, int maxval)
	{
		// To get Python-style -1%50 = 49 rather than -1%50=-1 like in C++
		const int mod = maxval + 1 - minval;
		if (delta >= 0) {return  (v + delta                - minval) % mod + minval;}
		else            {return ((v + delta) - delta * mod - minval) % mod + minval;}
	}

	void time::add(const int& hh, const int& mm)
	{
		// Warning: we carry over overflow minutes into hours but not hours
		// into days since this is just a time object not a datetime object.
		int carry_over_minutes = floor(
			(static_cast<double>(m) + mm) / (max_minutes + 1));
		h = wrapAround(h, hh + carry_over_minutes, 0, max_hours);
		m = wrapAround(m, mm, 0, max_minutes);

		if (!valid(h,m))
			throw Invalid();
	}

	bool time::valid(const int& hh, const int& mm) const
	{
		if (hh < 0 || hh > max_hours)	return false;
		if (mm < 0 || mm > max_minutes)	return false;

		return true;
	}

	bool operator==(const date& a, const date& b)
	{
		if (a.y == b.y &&
		    a.m == b.m &&
		    a.d == b.d)
		    return true;

		return false;
	}

	bool operator!=(const date& a, const date& b)
	{
		return !(a==b);
	}

	bool operator==(const time& a, const time& b)
	{
		if (a.h == b.h &&
		    a.m == b.m)
		    return true;

		return false;
	}

	bool operator!=(const time& a, const time& b)
	{
		return !(a==b);
	}

	bool operator<(const date& a, const date& b)
	{
		if (a.y < b.y) return true;
		if (a.y > b.y) return false;

		if (a.m < b.m) return true;
		if (a.m > b.m) return false;

		if (a.d < b.d) return true;
		if (a.d > b.d) return false;

		return false;
	}

	bool operator>(const date& a, const date& b)
	{
		if (b.y < a.y) return true;
		if (b.y > a.y) return false;

		if (b.m < a.m) return true;
		if (b.m > a.m) return false;

		if (b.d < a.d) return true;
		if (b.d > a.d) return false;

		return false;
	}

	bool operator<=(const date& a, const date& b)
	{
		if (a==b || a<b) return true;

		return false;
	}

	bool operator>=(const date& a, const date& b)
	{
		if (a==b || a>b) return true;

		return false;
	}

	bool operator<(const time& a, const time& b)
	{
		if (a.h < b.h) return true;
		if (a.h > b.h) return false;

		if (a.m < b.m) return true;
		if (a.m > b.m) return false;

		return false;
	}

	bool operator>(const time& a, const time& b)
	{
		if (b.h < a.h) return true;
		if (b.h > a.h) return false;

		if (b.m < a.m) return true;
		if (b.m > a.m) return false;

		return false;
	}

	bool operator<=(const time& a, const time& b)
	{
		if (a==b || a<b) return true;

		return false;
	}

	bool operator>=(const time& a, const time& b)
	{
		if (a==b || a>b) return true;

		return false;
	}

	ostream& operator<<(ostream& os, const date& d)
	{
		os << set_digits(4, d.y)
		   << set_digits(2, d.m)
		   << set_digits(2, d.d);

		return os;
	}

	ostream& operator<<(ostream& os, const time& t)
	{
		os << t.h << ":" << set_digits(2, t.m);

		return os;
	}

	ostream& operator<<(ostream& os, const now& t)
	{
		os << t.d << " " << t.t;

		return os;
	}
}
