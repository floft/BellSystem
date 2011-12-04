#include <ctime>
#include <iostream>
#include "../wait.h"
#include "../string_functions.h"

using namespace std;

int main()
{
	Wait::wait_till_minute();
	
	time_t current = std::time(0);
	struct tm* tm  = localtime(&current);
	cout << set_digits(2, tm->tm_hour) << ":"
	     << set_digits(2, tm->tm_min)  << ":"
	     << set_digits(2, tm->tm_sec)  << endl;
}
