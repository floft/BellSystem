#include "split.h"

namespace Split
{
	string peek_next(istream& is, const int& number)
	{
		char c;
		string s;

		for (int i = 0; i < number; ++i)
		{
			is.get(c);
			s+=c;
		}

		for (int i = s.length() - 1; i >= 0; --i)
			is.putback(s[i]);

		return s;
	}

	vector<string> split(const string& input, const string& split)
	{
		char c;
		int pos = 0;
		vector<string> items(1);
		istringstream is(input);

		while (is.get(c))
		{
			if (c == split[0] && split.substr(1) == peek_next(is, split.length() - 1))
			{
				++pos;
				items.push_back("");
				is.seekg(split.length() - 1, ios_base::cur);
				continue;
			}

			items[pos] += c;
		}
		
		return items;
	}
}
