#!/bin/bash

rabbitmq-server &
apache2ctl -D FOREGROUND &
mysqld --init-file=/app/init.sql --user=root --bind=0.0.0.0 &
python3 -u /app/report_generator.py &
mitmdump -s /app/waf.py --mode reverse:http://localhost --set block_global=false &

wait -n
exit $?