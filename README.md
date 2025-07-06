# Laravel12のDocker環境などを構築する

## Dockerの環境を構築する前にVSCode用の設定を行う

### 拡張機能の追加

`.vscode/extensions.json` にプロジェクトで推奨される拡張機能を設定しています。

拡張機能の検索窓に **@recommended** と入れるとプロジェクト推奨の拡張機能が表示されるのでインストールします。

推奨機能に入れているのは下記の拡張機能

* DotENV
* Log File Highlighter
* Remote Development
* change-case
* Code Spell Checker
* Container Tools
* Docker DX
* EditorConfig for VS Code
* i18n Ally
* indent-rainbow
* Laravel
* php cs fixer
* PHP Debug
* PHP Intelephense
* PHP Namespace Resolver
* PHP Static Analysis
* REST Client
* SFTP
* Trailing Spaces
* YAML

### プロジェクト共通のVSCodeの設定

`.vscode/settings.json` にプロジェクト共通のVSCodeの設定を記載しています。

### デバッグ用の設定

`.vscode/launch.json` にデバッグ用の設定を記載しています。

## Docker

### 技術スタック

* PHP8.3.23
* Apache2.4
* MySQL8.0.39
  * Read/Write構成
* Valkey8.0.3
  * Redis代替（OSSで無くなったので、こちらに移行中）
* MinIO
  * S3互換ストレージ
  * S3を使用するために `composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies` を実行

### 使い方

Docker上のユーザーIDとホストのユーザーIDが異なることによりPermisionエラーが出ることを回避するため、`bash dc …` を使用してDockerの操作を行います。このファイルはLaravelのsailの元となったファイルを参考に作成されています。

#### .envファイルの作成

`.env.sample` をコピーして `.env` ファイルを作成します。

```sh
cp .env.sample .env
```

#### 初回起動時

```sh
# Dockerのイメージの生成やコンテナの起動を行う
bash dc up

# アプリケーションキーの生成
bash dc php artisan key:generate

# コンテナの中のComposerを使用してcomposerのインストールを行う
bash dc composer install
```

#### 使えるコマンド等の確認

```sh
bash dc help
```

#### ローカルでデータを確認するためのシステムの各リンク

```sh
bash dc links
```

## 各種データの確認

### メール

Mailpit を使用してローカルから送信したメールの内容を確認することができます。

[メール管理画面](http://localhost:8025) からメールの内容を確認してください。

### Valkey（Redis）

Redis Insight を使用して Valkey の中のデータを確認することができます。

[Redis Insight管理画面](http://localhost:5540) から管理画面にログインすることができます。

1. 画面を開いたらONにできるところを全てONにして「Submit」ボタンを押してください
2. 左上の「Add Redis database」ボタンを押して設定画面を開きます
3. 「Connction URL」の中の `127.0.0.1` 部分の設定を `valkey`（compose.yamlに設定されている値）に変更します
4. 「Test Connection」を押して接続ができることを確認します
5. 接続が確認できたら「Add Database」ボタンを押して接続を追加します

### MinIO（S3）

S3互換のMinIOを使用してアップロードした画像の確認を行うことができます。

[MinIO管理画面](http://localhost:8900) から管理画面にログインすることができます。

* Usernam: `.env` の `AWS_ACCESS_KEY_ID` の値
* Password: `.env` の `AWS_SECRET_ACCESS_KEY` の値
