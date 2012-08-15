#include "../string_functions.h"
#include <iostream>
#include <glibmm/ustring.h>

using namespace std;

void test(const Glib::ustring str)
{
	cout << str << "\t" << ustring_to_bool(str) << endl;
}

int main()
{
	test("true");
	test("false");
	test("True");
	test("False");
	test("1");
	test("0");
}
