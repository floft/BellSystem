OUT		= ${wildcard *.o}
OBJ		= ${wildcard ../*.o}

CC		= g++
CFLAGS		:= -Wall ${CFLAGS}

CFLAGS		+= $(shell pkg-config --cflags libxml++-2.6)
LDFLAGS		+= $(shell pkg-config --libs   libxml++-2.6)

all: daemon tests

daemon:
	${MAKE} -C ../
	${RM} ../daemon.o

tests: daemon
	${CC} -o date_test.o        date_test.cpp        ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o print_config.o     print_config.cpp     ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o print_date.o       print_date.cpp       ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o wait_till_minute.o wait_till_minute.cpp ${OBJ} ${LDFLAGS} ${CFLAGS}

clean:
	${RM} ${OUT} ${OBJ}

.PHONY: all daemon tests clean
