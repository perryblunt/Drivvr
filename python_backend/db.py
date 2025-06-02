import pymysql

def get_db_connection():
    connection = pymysql.connect(host='localhost',
                                 user='csctheco_user',
                                 password='ctMk_mT+x*IU',
                                 database='csctheco_drivvr',
                                 cursorclass=pymysql.cursors.DictCursor)
    return connection
