#!/bin/bash
set -eux

# マスタ側のmysqlがまだ起動していない場合があるので立ち上がるまで待機
until mysqladmin ping -h "$WRITE_HOST" -u root --password="$MYSQL_ROOT_PASSWORD" --silent; do
    sleep 1
done

# テスト用のデータベースを作成
mysql -h localhost -u root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS $TEST_DATABASE;
    GRANT ALL PRIVILEGES ON \`$TEST_DATABASE%\`.* TO '$MYSQL_USER'@'%';
EOSQL

# マスタ側のデータをダンプ
mysqldump \
    -h "$WRITE_HOST" \
    -u "$MYSQL_USER" \
    --password="$MYSQL_PASSWORD" \
    --databases "$MYSQL_DATABASE" "$TEST_DATABASE" \
    --single-transaction \
    --flush-logs \
    --set-gtid-purged > /tmp/all.sql

# スレーブ側にリストア
mysql -h localhost -u root --password="$MYSQL_ROOT_PASSWORD" < /tmp/all.sql

# レプリケーションの設定
mysql -h localhost -u root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    -- レプリケーションの設定
    CHANGE REPLICATION SOURCE TO
        SOURCE_HOST = '$WRITE_HOST',
        SOURCE_PORT = $WRITE_PORT,
        SOURCE_USER = '$MYSQL_USER',
        SOURCE_PASSWORD = '$MYSQL_PASSWORD',
        SOURCE_AUTO_POSITION = 1;

    -- レプリケーションを開始
    START REPLICA;
EOSQL
