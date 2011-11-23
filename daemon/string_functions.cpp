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
