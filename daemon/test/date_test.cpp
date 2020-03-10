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

	// 23:59 + 00:01 = 00:00
	DateTime::time t7(23,  59);
	t7.add(0, 1);
	if (t7.hour() != 0 || t7.minute() != 0) return 1;

	// 22:05 + 02:07 = 00:12
	DateTime::time t8(22,  5);
	t8.add(2, 7);
	if (t8.hour() != 0 || t8.minute() != 12) return 1;

	// 22:05 + 01:07 = 23:12
	DateTime::time t9(22,  5);
	t9.add(1, 7);
	if (t9.hour() != 23 || t9.minute() != 12) return 1;

	// 01:02 + (-02):05 = 23:07
	DateTime::time t10(1,  2);
	t10.add(-2, 5);
	if (t10.hour() != 23 || t10.minute() != 7) return 1;

	// 01:02 + 00:-05 = 00:57
	DateTime::time t11(1,  2);
	t11.add(0, -5);
	if (t11.hour() != 0 || t11.minute() != 57) return 1;

	// 00:02 + 00:-05 = 23:57
	DateTime::time t12(0,  2);
	t12.add(0, -5);
	if (t12.hour() != 23 || t12.minute() != 57) return 1;

	return 0;
}
