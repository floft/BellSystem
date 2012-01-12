all: daemon

daemon:
	${MAKE} -C daemon

tests: daemon
	${MAKE} -C daemon tests

deploy: clean
	git add -A
	git commit
	git push
	echo "Press enter to deploy..."
	read -s
	ssh b 'cd PKGBUILDs/bellsystem-git; git pull; makepkg -sif'

install: daemon
	${MAKE} -C daemon install

uninstall:
	${MAKE} -C daemon uninstall

clean:
	${MAKE} -C daemon clean
	find -name '*~' -delete

.PHONY: all daemon tests deploy install uninstall clean
