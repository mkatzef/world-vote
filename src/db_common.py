import mysql.connector
import db_config

DB_CNX = mysql.connector.connect(
            user=db_config.username,
            password=db_config.password,
            host=db_config.endpoint,
            database=db_config.db_name
        )
