#include <sys/types.h>
#include <sys/ioctl.h>
#include <fcntl.h>
#include <errno.h>
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <signal.h>
#include <termios.h>
/* Main program. */
int main(int argc, char **argv)
{
 int fd;
 int set_bits = 0;

 /* Open monitor device. */
 if ((fd = open(argv[1], O_RDWR | O_NDELAY)) < 0) {
  fprintf(stderr, "ar-2: %s: %s\n",
          argv[1], sys_errlist[errno]);
  exit(1);
 }
 
 if (strcmp(argv[2], "on") != 0 && strcmp(argv[2], "off") != 0) {
  printf("Error: please specify on or off", argv[2]);
  exit(1);
 }
 
 if (strcmp(argv[2], "off") == 0) {
  set_bits = 0 ;
 } else {
  set_bits = 6 ;
 }

 ioctl(fd, TIOCMSET, &set_bits);
 //ioctl(fd, TIOCMGET, &status);

 sleep(2592000);
 close(fd);
}
