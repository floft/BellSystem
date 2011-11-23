#ifndef H_STRING_FUNCTIONS
#define H_STRING_FUNCTIONS

#include <string>
#include <sstream>
#include <glibmm/ustring.h>

using namespace std;

int string_to_int(const string& input);
int ustring_to_int(const Glib::ustring& input);

#endif
