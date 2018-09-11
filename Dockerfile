# if you find this Dockerfile useful, consider donating some ARO:
# 5nbhh6gdHkiZg8rdQh7ycSAg7umpRZ31Yau88esgzctxqsE9EyR4eJbNPGsDo1Z3fcNjQ6RgtXQAk9iYYqaB46cU

FROM php:7.2-cli

LABEL maintainer="Nuno Ferro <mail@nunoferro.com>"

RUN apt-get update && apt-get install -y libgmp-dev \
    && docker-php-ext-install -j$(nproc) gmp

RUN ln -s /usr/local/bin/php /usr/bin/php

COPY *.php /aro/
WORKDIR /aro
ENTRYPOINT [ "/aro/lightArionumCLI.php" ]
