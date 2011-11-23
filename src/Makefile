OUT		= bell-daemon.out
SRC		= ${wildcard *.cpp}
OBJ		= ${SRC:.cpp=.o}

CC		= g++
CFLAGS		:= -Wall ${CFLAGS}
LDFLAGS		:= -lboost_date_time ${LDFLAGS}

CFLAGS		+= $(shell pkg-config --cflags libxml++-2.6)
LDFLAGS		+= $(shell pkg-config --libs   libxml++-2.6)

PREFIX		?= /usr/local
MANPREFIX	?= ${PREFIX}/share/man

all: ${OUT}

${OUT}: ${OBJ}
	${CC} -o $@ ${OBJ} ${LDFLAGS}

.cpp.o:
	${CC} -c -o $@ $< ${CFLAGS}

clean:
	${RM} ${OUT} ${OBJ}

.PHONY: clean
