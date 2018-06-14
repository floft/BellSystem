all: daemon

daemon:
	${MAKE} -C daemon

tests: daemon
	${MAKE} -C daemon tests

install: daemon
	# Copy bellsystem daemon
	${MAKE} -C daemon install
	# Create directories
	mkdir -p "${DESTDIR}/etc/apache2/conf-available/"
	mkdir -p "${DESTDIR}/etc/apache2/sites-available/"
	mkdir -p "${DESTDIR}${PREFIX}/share/bellsystem/"
	mkdir -p "${DESTDIR}${PREFIX}/share/webapps/bellsystem/"
	# Copy systemd service
	install -Dm644 "install/bellsystem.service"         "${DESTDIR}/lib/systemd/system/"
	# Copy Apache config files
	install -Dm644 "install/httpd-bellsystem.conf"      "${DESTDIR}/etc/apache2/conf-available/"
	install -Dm644 "install/httpd-bellsystem-root.conf" "${DESTDIR}/etc/apache2/sites-available/"
	# Copy website password changer script
	install -Dm755 "install/password.sh"                "${DESTDIR}${PREFIX}/bin/bellsystem-password"
	# Backup existing config file if it exists
	[ -f "${DESTDIR}${PREFIX}/share/webapps/bellsystem/config.xml" ] && \
	    mv "${DESTDIR}${PREFIX}/share/webapps/bellsystem/config.xml" \
	        "${DESTDIR}${PREFIX}/share/webapps/bellsystem/config.xml.make-bak"
	# Copy website files
	cp -ra www/.htaccess www/*                          "${DESTDIR}${PREFIX}/share/webapps/bellsystem/"
	# Restore existing config file if it exists
	[ -f "${DESTDIR}${PREFIX}/share/webapps/bellsystem/config.xml.make-bak" ] && \
	    mv "${DESTDIR}${PREFIX}/share/webapps/bellsystem/config.xml.make-bak" \
	        "${DESTDIR}${PREFIX}/share/webapps/bellsystem/config.xml"
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
	rm "${DESTDIR}/etc/apache2/conf-available/httpd-bellsystem.conf"
	rm "${DESTDIR}/etc/apache2/sites-available/httpd-bellsystem-root.conf"
	# Remove website password changer script
	rm "${DESTDIR}${PREFIX}/bin/bellsystem-password"

clean:
	${MAKE} -C daemon clean

.PHONY: all daemon tests install uninstall clean
