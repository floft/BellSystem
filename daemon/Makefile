OUT		= bell-daemon.out
SRC		= ${wildcard *.cpp}
OBJ		= ${SRC:.cpp=.o}

CC		= g++
CFLAGS		:= -Wall ${CFLAGS}

CFLAGS		+= $(shell pkg-config --cflags libxml++-2.6)
LDFLAGS		+= $(shell pkg-config --libs   libxml++-2.6)

PREFIX		?= /usr/local
MANPREFIX	?= ${PREFIX}/share/man

all: ${OUT}

tests: ${OUT}
	${MAKE} -C test

${OUT}: ${OBJ}
	${CC} -o $@ ${OBJ} ${LDFLAGS}

.cpp.o:
	${CC} -c -o $@ $< ${CFLAGS}

install:
	install -Dm755 ${OUT} ${DESTDIR}${PREFIX}/bin/bellsystem-daemon
	
uninstall:
	rm -f ${DESTDIR}${PREFIX}/bin/bellsystem-daemon

clean:
	${MAKE} -C test clean
	${RM} ${OUT} ${OBJ}

.PHONY: all tests install uninstall clean
