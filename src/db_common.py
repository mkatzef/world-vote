import mysql.connector
import rds_config

DB_CNX = mysql.connector.connect(
            user=rds_config.db_username,
            password=rds_config.db_password,
            host=rds_config.db_endpoint,
            database=rds_config.db_name
        )
