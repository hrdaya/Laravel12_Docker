# PHPのバージョン
ARG PHP_VER=8.3.23

# ベースとなるイメージのビルド =========================================================================================
FROM php:${PHP_VER}-apache AS builder

# 各種インストール
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    # healthcheck用
    lsof \
    # SSL用
    openssl \
    # Guzzle関連（同時リクエストを送信する場合に必要）
    curl \
    # ICU(Unicode関連ライブラリ)
    libicu-dev \
    # 鬼車(正規表現ライブラリ)
    libonig-dev \
    # ZIP関連
    libzip-dev zip unzip \
    # GDの最小構成(PNG)
    zlib1g-dev libpng-dev \
    # GDのフォント描画(FreeType --with-freetype)
    libfreetype6-dev \
    # GDのJPEG(JPEG --with-jpeg)
    libjpeg62-turbo-dev \
    # GDのWebP(WebP --with-webp)
    libwebp-dev \
    # GDの設定
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    # プロセス制御の設定
    && docker-php-ext-configure pcntl --enable-pcntl \
    # PHPのモジュールのインストール
    && docker-php-ext-install -j$(nproc) \
    # 画像処理 (GD)
    gd \
    # プロセス制御
    pcntl \
    # BCMath 任意精度数学関数
    bcmath \
    # 国際化関数
    intl \
    # マルチバイト文字列
    mbstring \
    # PDO
    pdo_mysql \
    # pdo_pgsql \
    # OPcache
    opcache \
    # ZIP
    zip \
    # phpredisのインストール
    && pecl install redis \
    # phpredisの有効化
    && docker-php-ext-enable redis \
    # 不要になったものを削除
    && docker-php-source delete \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    # ApacheのRewriteモジュールの有効化
    && a2enmod rewrite

# 開発環境のビルド（ベースとなるイメージから生成） =====================================================================
FROM builder AS development

# ARGの定義（compose.yamlで指定した値を引き継ぐ）
ARG DOCKER_PHP_DIR
ARG CONTAINER_APP_USER
ARG WWWGID

# Xdebugのインストール、有効化
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# ユーザーを追加
RUN groupadd --force -g ${WWWGID} ${CONTAINER_APP_USER} \
    && useradd -ms /bin/bash --no-user-group -g ${WWWGID} -u 1337 ${CONTAINER_APP_USER}

# ApacheのUser/Groupをセット
ENV APACHE_RUN_USER=${CONTAINER_APP_USER}
ENV APACHE_RUN_GROUP=${CONTAINER_APP_USER}

# 開発用のphp.iniの名前を変更
RUN mv ${PHP_INI_DIR}/php.ini-development ${PHP_INI_DIR}/php.ini

# php.iniの上書き用ファイルをコピー（compose側でマウントするので不要）
# COPY ${DOCKER_PHP_DIR}/zzz-development.ini ${PHP_INI_DIR}/conf.d/zzz-development.ini

# composer のインストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.jsのインストール
COPY --from=node:22 /usr/local/bin/ /usr/local/bin/
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules

# VITE用
EXPOSE 5173

# コンテナ起動時に実行するentrypoint.shをセットアップ
COPY ${DOCKER_PHP_DIR}/entrypoint.sh /var/tmp/entrypoint.sh
RUN chmod +x /var/tmp/entrypoint.sh
ENTRYPOINT ["/var/tmp/entrypoint.sh"]

CMD ["/bin/bash"]

# 本番環境用の依存パッケージのインストール、キャッシュ生成 =============================================================
FROM builder AS dependency

# ARGの定義（compose.yamlで指定した値を引き継ぐ）
ARG DOCKER_PHP_DIR
ARG APP_BASE_PATH

# composer のインストールに必要なパッケージのインストール
RUN apt-get update \
    && apt-get install -y git \
    # 不要になったものを削除
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# 作業用ディレクトリを作成
# 本番のパスと合わせておかないとキャッシュした際にパスがずれるので注意
RUN mkdir -p ${APP_BASE_PATH}
WORKDIR ${APP_BASE_PATH}

# ソースコードをコピー
COPY ./ ${APP_BASE_PATH}

# tinker用の設定ファイルをコピー
COPY ${DOCKER_PHP_DIR}/tinker.php ${APP_BASE_PATH}/.config/psysh/config.php

# composer のインストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 依存パッケージを本番用にインストール
RUN composer install --no-dev -o

# キャッシュ生成
RUN php artisan optimize

# Node.jsのインストール
COPY --from=node:22 /usr/local/bin/ /usr/local/bin/
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules

# アセットのビルド（Laravel Modules用にModulesフォルダ内でもnpmのコマンドを実行）
RUN npm install \
    && npm run build \
    && for directory in $(find ./Modules -mindepth 1 -maxdepth 1 -type d); do \
        cd "$directory" \
        && npm install \
        && npm run build \
        && cd - > /dev/null; \
    done

# 不要ファイルの削除
RUN find . -maxdepth 3 -type d \( -name config -o -name database -o -name routes -o -name node_modules \) -exec rm -rf {} +

# 本番環境のビルド（ベースとなるイメージから生成） =====================================================================
FROM builder AS production

# ARGの定義（compose.yamlで指定した値を引き継ぐ）
ARG DOCKER_PHP_DIR
ARG APP_BASE_PATH

# 依存バッケージを含むアプリケーションに必要なファイルをコピー
COPY --from=dependency ${APP_BASE_PATH} ${APP_BASE_PATH}

# 本番用のphp.iniの名前を変更
RUN mv ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

# php.iniの上書き用ファイルをコピー
COPY ${DOCKER_PHP_DIR}/zzz-production.ini ${PHP_INI_DIR}/conf.d/zzz-production.ini

# Apacheの設定ファイルをコピー
COPY ${DOCKER_PHP_DIR}/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# ディレクトリの所有者を変更
RUN chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} ${APP_BASE_PATH}

# ユーザーを変更
USER www-data

# SSL用
EXPOSE 443

# ReadOnlyで実行している場合でも書き込みが必要なディレクトリ
VOLUME ["${APP_BASE_PATH}/storage/app", "${APP_BASE_PATH}/storage/framework", "/tmp"]

# Apacheをフォアグラウンドで実行
CMD ["apache2-foreground"]
