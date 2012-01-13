all: daemon

daemon:
	${MAKE} -C daemon

tests: daemon
	${MAKE} -C daemon tests

deploy: clean
	git add -A
	git commit && git push || true
	@echo "Press enter to deploy..."
	@read -s
	ssh -t bellsystem 'cd PKGBUILDs/bellsystem-git; git pull; makepkg -sif --holdver; tail /var/log/bellsystem.log; ps -p $$(</var/run/bellsystem.lock) &>/dev/null || echo "Warning: Bell System daemon is not running!"'

install: daemon
	${MAKE} -C daemon install

uninstall:
	${MAKE} -C daemon uninstall

clean:
	${MAKE} -C daemon clean
	find -name '*~' -delete

.PHONY: all daemon tests deploy install uninstall clean
