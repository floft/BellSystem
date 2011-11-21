#include "config.h"
#include <stdexcept>

int main()
try {
	Config c("../config.xml");
	return 0;
} catch (Config::Error& e) {
	cout << "Error: " << e.what() << endl;
	return 1;
}

