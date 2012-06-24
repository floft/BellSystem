all: daemon

daemon:
	${MAKE} -C daemon

tests: daemon
	${MAKE} -C daemon tests

commit: clean
	git add -A
	git commit && git push || true

deploy: commit
	ssh -t e "ssh -t b 'cd PKGBUILDs/bellsystem-git; git pull; rm *.xz; makepkg -sif --holdver; sudo rc.d restart bellsystem; tail /var/log/bellsystem.log; pidof bellsystem-daemon &>/dev/null || echo \"Warning: Bell System daemon is not running!\"'"

install: daemon
	${MAKE} -C daemon install

uninstall:
	${MAKE} -C daemon uninstall

clean:
	${MAKE} -C daemon clean
	find -name '*~' -delete

.PHONY: all daemon tests commit deploy install uninstall clean
