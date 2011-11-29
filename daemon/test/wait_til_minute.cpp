#include <ctime>
#include <iostream>
#include "../wait.h"

using namespace std;

int main()
{
	Wait::wait_till_minute();
	
	time_t current = std::time(0);
	struct tm* tm  = localtime(&current);
	cout << tm->tm_hour << ":"
	     << tm->tm_min  << ":"
	     << tm->tm_sec  << endl;
}
