.DEFAULT_GOAL := build


build:
	php --define phar.readonly=0 /usr/local/bin/box build -c box.json
	chown 1000.1000 metaLogger.phar
	cp metaLogger.phar /tmp/release