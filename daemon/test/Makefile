OUT		= ${wildcard *.out}
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
	${CC} -o date_test.out        date_test.cpp        ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o gpio.out             gpio.cpp             ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o print_config.out     print_config.cpp     ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o print_date.out       print_date.cpp       ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o string_functions.out string_functions.cpp ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o wait_till_minute.out wait_till_minute.cpp ${OBJ} ${LDFLAGS} ${CFLAGS}
	${CC} -o serial.out serial.cpp ${OBJ} ${LDFLAGS} ${CFLAGS}

clean:
	${RM} ${OUT} ${OBJ}

.PHONY: all daemon tests clean
