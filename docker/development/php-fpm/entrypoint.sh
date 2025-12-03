#!/bin/bash

# Run composer scripts (package discovery) after dependencies are installed
if [ -f /var/www/composer.json ]; then
    echo "Running composer post-install scripts..."
    composer dump-autoload --optimize
fi

# Prüfe, ob APP_KEY in der .env fehlt oder leer ist, und generiere ihn ggf.
if [ ! -f /var/www/.env ]; then
	echo ".env nicht gefunden, bitte sicherstellen, dass sie vorhanden ist!"
else
	if grep -q '^APP_KEY=$' /var/www/.env || ! grep -q '^APP_KEY=' /var/www/.env; then
		echo "Kein APP_KEY gefunden, generiere neuen Key..."
		php artisan key:generate
	fi
fi

exec "$@"
