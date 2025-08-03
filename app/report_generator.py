import pika
import json
import mysql.connector
import time
import datetime
import random
import pandas as pd

time.sleep(20)

credentials = pika.PlainCredentials("admin", "b4294ebfa570c4be2d185472ca782bcf")
connection = pika.BlockingConnection(pika.ConnectionParameters(host="localhost", port=5672, virtual_host="/", credentials=credentials))
channel = connection.channel()
channel.queue_declare(queue="requests", durable=True)

def callback(ch, method, properties, body):
    read_db = mysql.connector.connect(
        host="localhost",
        user="admin",
        passwd="32e575006fb439274643852ea2ab2e6c",
        get_warnings=True
    )
    read_cursor = read_db.cursor(dictionary=True)

    time.sleep(random.random() * 5)
    query = ""
    try:
        ch.basic_ack(delivery_tag=method.delivery_tag)
        msg = json.loads(body)
        print("Received message: %s" % msg)
        
        group_by = msg.get("group_by", [])
        aggregate = msg.get("aggregate", [])
        filename = msg["filename"]

        if group_by is None: group_by = []
        if aggregate is None: aggregate = []

        if len(group_by) + len(aggregate) <= 0:
            return

        p1 = ", ".join(group_by)
        p2 = ", ".join(aggregate)

        select = p1 + ", " + p2
        if len(group_by) == 0 or len(aggregate) == 0:
            select = p1 + p2
        
        group_by_query = ""
        if len(group_by) > 0:
            group_by_query = " GROUP BY " + ", ".join([q.split(" ")[-1] for q in group_by])

        query = "SELECT " + select + " FROM report_data.data" + group_by_query + ";"
        read_cursor.execute(query)
        df = pd.DataFrame(read_cursor.fetchall())
        report_out = "Patient Visit Statistics Report\n"
        report_out += "Requested by: " + msg["report_by"] + "\n"
        report_out += "Generated on: " + str(datetime.datetime.today()) + "\n"
        report_out += "Copyright (C) Horizon Health Network\n\n"
        report_out += df.to_string()

    except Exception as e:
        report_out = "An error has occured trying to process your request. Please try again later."

    with open("/var/www/html/reports/" + str(filename), "w+") as f:
        f.write(report_out)

    read_cursor.close()
    read_db.close()

    time.sleep(1)

print("Starting consumer.")

channel.basic_qos(prefetch_count=1)
channel.basic_consume(queue="requests", on_message_callback=callback)
channel.start_consuming()
