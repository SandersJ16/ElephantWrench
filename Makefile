main: install

install:
ifeq ($(command -v composer), '')
	echo "Composer not detected, please install composer to proceed"
else
	composer install
endif

test:
	./vendor/bin/phpunit -c phpunit.xml

checkStyle:
	./vendor/bin/phpcs --standard=phpcs.xml -p

updateStyle:
	./vendor/bin/phpcbf --standard=phpcs.xml -p
