#!/bin/bash
set -eux

# テスト用のデータベースを作成
mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS $TEST_DATABASE;
    GRANT ALL PRIVILEGES ON \`$TEST_DATABASE%\`.* TO '$MYSQL_USER'@'%';
EOSQL

# レプリケーションの設定
mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    GRANT RELOAD, FLUSH_TABLES ON *.* TO '$MYSQL_USER'@'%';
    GRANT REPLICATION SLAVE ON *.* TO '$MYSQL_USER'@'%';
EOSQL
