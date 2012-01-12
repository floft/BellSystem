all: daemon

daemon:
	${MAKE} -C daemon

tests: daemon
	${MAKE} -C daemon tests
clean:
	${MAKE} -C daemon clean
	find -name '*~' -delete

deploy: clean
	git add -A
	git commit
	git push
	ssh b 'cd bellsystem-git; makepkg -sif'

.PHONY: daemon tests clean deploy
