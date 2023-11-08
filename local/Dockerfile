#########
# stage #
#########
FROM thecodingmachine/php:8.2-v4-fpm AS main

FROM main AS symfony

# copy configurations
COPY ./conf.d/* "$PHP_INI_DIR/conf.d/"
