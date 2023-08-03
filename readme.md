Setup & Installation
--------------------
The main dependency of this project is `Ratchet` for handling Websockets in PHP.
First, you need to install `composer`, a dependency manager for PHP.
Run `composer install` to install Ratchet and other related dependencies.
They will be located on a `vendor/` directory.

On actual production/in a live server, a TCP port needs to be exposed for WebSockets to work.
Modify the `ws_server.php` file accordingly to use that given port and run the PHP script in the background.
You should also setup a proper SSL certificate for `https://` and `wss://` protocols to work properly on the website.
Self-signed certificates are provided and left on the projet's root for testing purposes.
You have to add/accept these certificates manually to your browser.

Usage
-----
First, run the web server, either through the Apache service or any similar methods or tools, like the command `php -S localhost:8000`, with the `-S` option for running a built-in web server, or a bundle like XAMPP.
Then, run the websockets server PHP script, preferably in the background, for example with the command `php ws_server.php &`
