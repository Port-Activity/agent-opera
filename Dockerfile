FROM php:7.3-cli

RUN apt-get update
RUN apt-get install -y libssh2-1-dev
RUN pecl install ssh2-1.2
RUN docker-php-ext-enable ssh2

COPY . /src/lib
WORKDIR /src/lib

CMD [ "php", "./src/lib/run.php" ]
