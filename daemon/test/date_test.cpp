#include <iostream>
#include "../date.h"

using namespace std;

int main()
{
	DateTime::date d1(2000, 1,  1);
	DateTime::date d2(1999, 12, 31);
	DateTime::date d3(1999, 12, 31);
	DateTime::date d4(2001, 1,  1);
	DateTime::date d5(1999, 11, 10);

	if (d1 < d2)     return 1;
	if (d1 > d4)     return 1;
	if (d2 != d3)    return 1;
	if (!(d2 <= d3)) return 1;
	if (!(d2 >= d3)) return 1;
	if (d4 == d5)    return 1;

	DateTime::time t1(0,  0);
	DateTime::time t2(23, 59);
	DateTime::time t3(10, 10);
	DateTime::time t4(10, 11);
	DateTime::time t5(11, 10);
	DateTime::time t6(10, 10);

	if (t1 > t2)     return 1;
	if (t3 < t6)     return 1;
	if (t3 != t6)    return 1;
	if (!(t3 <= t6)) return 1;
	if (!(t3 >= t6)) return 1;
	if (t4 > t5)     return 1;
	if (t5 < t6)     return 1;

	return 0;
}
