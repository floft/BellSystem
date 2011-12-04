#include "string_functions.h"

int ustring_to_int(const Glib::ustring& input)
{
	int output = -1;
	stringstream s;
	s << input.raw();
	s >> output;

	return output;
}

int string_to_int(const string& input)
{
	int output = -1;
	stringstream s;
	s << input;
	s >> output;

	return output;
}

string set_digits(const int& digits, const int& number)
{
	string zeros;
	string str;

	stringstream s;
	s << number;
	s >> str;

	const int& len = str.length();

	for (int i = 0; i < (digits - len); ++i)
		zeros += "0";

	return zeros + str;
}
