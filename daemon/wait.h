#ifndef H_WAITFOR
#define H_WAITFOR

#include <poll.h>
#include <ctime>
#include <unistd.h>
#include <iostream>

namespace Wait
{
	using namespace std;

	const int max_seconds  = 60;
	const int nano_in_mili = 1000000;
	const int mili_wait    = 10;      //check time every 10 miliseconds
					  //during last 1 second before minute
	const int inotify_timeout = 500;  //arbitrary number of miliseconds

	void wait_for_minute();
	bool wait_for_change(int& fd);
}

#endif
