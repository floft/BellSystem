#include "wait.h"

namespace Wait
{
	void wait_till_minute()
	{
		while (true)
		{
			time_t current = std::time(0);
			struct tm* tm  = localtime(&current);

			if (tm->tm_sec == 0)
				break;

			int seconds = max_seconds - tm->tm_sec;

			//sleep in seconds till just before the minute,
			//then go in increments of mili_wait
			if (seconds > 1)
				usleep((seconds-1)*1000);
			else
				usleep(mili_wait);
		}
	}

	/*bool wait_for_change(int& fd)
	{
		while (true)
		{
			struct pollfd pfd = { fd, POLLIN, 0 };
			int           ret = poll(&pfd, 1, inotify_timeout);

			if (ret < 0)		//error
				return false;
			else if (ret == 0)	//not changed
				continue;
			else			//changed
				return true;
		}
	}*/
}
