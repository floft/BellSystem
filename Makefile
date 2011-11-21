OUT		= bell-daemon
VERSION		= 0.2

SRC		= ${wildcard *.cpp}
OBJ		= ${SRC:.cpp=.o}
DISTFILES	= Makefile README bell-daemon.cpp

PREFIX		?= /usr/local
MANPREFIX	?= ${PREFIX}/share/man

CPPFLAGS	:= -
