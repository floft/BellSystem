/*
 * Simple way to split a string when the separator is another string.
 */

#ifndef H_STRING_SPLIT
#define H_STRING_SPLIT

#include <string>
#include <vector>
#include <sstream>

namespace Split
{
	using namespace std;

	string peek_next(istream& is, const int& number);
	vector<string> split(const string& input, const string& split);
}

#endif
