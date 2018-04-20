all: daemon

daemon:
	${MAKE} -C daemon

tests: daemon
	${MAKE} -C daemon tests

install: daemon
	# Copy bellsystem daemon
	${MAKE} -C daemon install
	# Create directories
	mkdir -p "${DESTDIR}/etc/httpd/conf/extra/"
	mkdir -p "${DESTDIR}${PREFIX}/share/bellsystem/"
	mkdir -p "${DESTDIR}${PREFIX}/share/webapps/bellsystem/"
	# Copy systemd service
	install -Dm755 "install/bellsystem.service"         "${DESTDIR}${PREFIX}/lib/systemd/system/"
	# Copy Apache config files
	install -Dm644 "install/httpd-bellsystem.conf"      "${DESTDIR}/etc/httpd/conf/extra/"
	install -Dm644 "install/httpd-bellsystem-root.conf" "${DESTDIR}/etc/httpd/conf/extra/"
	# Copy website password changer script
	install -Dm755 "install/password.sh"                "${DESTDIR}${PREFIX}/bin/bellsystem-password"
	# Copy website files
	cp -ra www/.htaccess www/*                          "${DESTDIR}${PREFIX}/share/webapps/bellsystem/"
	# Make the config file writable by the Apache PHP website
	chown www-data                                      "${DESTDIR}${PREFIX}/share/webapps/bellsystem/config.xml"

uninstall:
	${MAKE} -C daemon uninstall
	# Remove bellsystem-specific directories
	rm -r "${DESTDIR}${PREFIX}/share/bellsystem/"
	rm -r "${DESTDIR}${PREFIX}/share/webapps/bellsystem/"
	# Remove systemd service
	rm "${DESTDIR}/lib/systemd/system/bellsystem.service"
	# Remove Apache config files
	rm "${DESTDIR}/etc/httpd/conf/extra/httpd-bellsystem.conf"
	rm "${DESTDIR}/etc/httpd/conf/extra/httpd-bellsystem-root.conf"
	# Remove website password changer script
	rm "${DESTDIR}${PREFIX}/bin/bellsystem-password"

clean:
	${MAKE} -C daemon clean

.PHONY: all daemon tests install uninstall clean
