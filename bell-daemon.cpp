#include <string>
#include <fstream>
#include <iostream>
#include <stdexcept>
#include <getopt.h>
#include <unistd.h>		//sleep
#include <stropts.h>		//ioctl
//#include <sys/ioctl.h>
//#include <sys/types.h>
#include <xercesc/dom/DOM.hpp>
#include <xercesc/util/PlatformUtils.hpp>

using namespace std;
using namespace xercesc;

//for USB to Serial adapter
const int set_bits_off = 0;
const int set_bits_on  = 6;

void help()
{
	cout << "Usage: bell-daemon -c /path/to/config.xml" << endl;
}

void error(const string& s)
{
	throw runtime_error(s);
}

void turn_on(string device, int miliseconds)
{
	int fd;
	
	if ((fd = open(device.c_str(), O_RDWR | O_NDELAY)) < 0)
	{
		error("could not turn device on");
	}

	ioctl(fd, TIOCMSET, &set_bits_on)
	sleep(miliseconds);
	close(fd);
}

void daemon(ifstream filename)
{
	//ifstream ifile(filename.c_str());
	//if  (!ifile) error("could not read config");
	//close(ifile);

	//Xerces junk
	XMLPlatformUtils::Initialize();

	XMLDOMParser* parser = new XercesDOMParser();
	parser->setValidationScheme(XercesDOMParser::Val_Always);

	ErrorHandler* errHandler = (ErrorHandler*) new HandlerBase();
	parser->setErrorHandler(errHandler);

	parser->parse(filename.c_str());
	root = XMLString::transcode("");


	delete parser;
	delete errHandler;
	XMLPlatformUtils::Terminate();

	turn_on(device, seconds*1000);
}

int main(int argc, char *argv[])
{
	int c;
	string config;

	while ((c = getopt(argc, argv, "c:h")) != -1)
	{
		switch (c)
		{
			case 'c':
				config=optarg;
				break;
			case 'h':
				help();
				return 0;
			case '?':
				help();
				return 1;
		}
	}

	try
	{
		daemon(config);
	}
	catch (const XMLException& e)
	{
		char* msg = XMLString::transcode(e.getMessage());

		cerr << "Error: could not initialize xerces-c" << endl;
		cerr << msg << endl;

		XMLString::release(&msg);

		return 1;
	}
	catch (const DOMException& e)
	{
		char* msg = XMLString::transcode(e.msg);

		cerr << "Error: could not parse XML" << endl;
		cerr << msg << endl;

		XMLString::release(&msg);

		return 1;
	}
	catch (exception& e)
	{
		cerr << "Error: " << e.what() << endl;
		return 1;
	}
	catch (...)
	{
		cerr << "Unexpected Exception" << endl;
		return 1;
	}

	return 0;
}
