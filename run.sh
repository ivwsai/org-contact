#!/bin/bash

env="prod" # prod
curr_dir=$(pwd)
docker_dir="$(pwd)/docker"

app_data="$(pwd)/group"
log_dir="${app_data}/logs"
app_log_dir="$(pwd)/application/logs"

php="org-php"

mkdir -p ${log_dir}
mkdir -p ${app_log_dir}
chmod -R 0777 ${app_log_dir}

is_running() {
    return $(docker ps -a |grep $1 | wc -l)
}

start_momo() {
    name=${php}
    image="php"
    is_running=$(docker ps -a |grep ${name} | wc -l)
    if [ ${is_running} == 0 ]; then
        echo "start ${name}..."
        docker run -d --restart=always --net=host --name ${name} -v "${docker_dir}/php.ini":/etc/php5/fpm/php.ini -v "${docker_dir}/group_www.conf":/etc/php5/fpm/pool.d/www.conf -v "${curr_dir}":/data/wwwroot/org -v "${log_dir}/php5":/var/log/php5 ${image} /usr/sbin/php5-fpm -R -c /etc/php5/fpm
    else
        echo "restart ${name}..."
        docker restart ${name}
    fi
}

start() {
    start_momo
}

restart() {
    docker restart ${php}
}

stop() {
    docker stop ${php}
}

rm() {
    docker rm ${php}
}

case "$1" in
    "")
        start
        ;;
    start)
        start
        ;;
    restart)
        restart
        ;;
    stop)
        stop
        ;;
    rm)
        rm
        ;;
    *)
        echo $"Usage: $0 {start|restart|stop|rm}"
        exit 2
esac

exit $?
