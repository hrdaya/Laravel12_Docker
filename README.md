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
