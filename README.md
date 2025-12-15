# attendance-app (coachtech 勤怠管理アプリ)

## 環境構築


**Dockerビルド手順**

1. リポジトリをクローン
``` bash
git clone git@github.com:Oda-mi/attendance-app.git
```
2. docker-compose.yml があるディレクトリへ移動
``` bash
cd attendance-app
```
3. Docker Desktop を起動
4. コンテナをビルドして起動
``` bash
docker-compose up -d --build
```


**Laravel環境構築手順**

※ 本プロジェクトは PHP 8.1 を使用しています<br>
※ PHP 8.2 以上では一部パッケージが対応しておらず、composer install でエラーが発生する可能性があります

***Dockerを使用する場合***

1. PHPコンテナに入る
``` bash
docker-compose exec php bash
```
2. 依存関係をインストール
``` bash
composer install
```

***Dockerを使用しない場合***
1. Laravel 本体が src 配下にあるため、src へ移動してください
```
cd src
```
2. 依存関係をインストール
``` bash
composer install
```
***共通設定（Dockerあり/なし共通）***
1. .env.example をコピーして .env ファイルを作成
``` bash
cp .env.example .env
```
1. .env に以下の環境変数を追加
```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
1. アプリケーションキーを生成
``` bash
php artisan key:generate
```
1. マイグレーションを実行
```bash
php artisan migrate
```
1. シーディングを実行
```bash
php artisan db:seed
```

## ダミーユーザー情報（シーディング用）
1. **管理ユーザー**
- 名前: 管理者
- メール: admin@example.com
- パスワード: admin123
2. **一般ユーザー**
- 名前: テスト太郎
- メール: test@example.com
- パスワード: password123

※シーダー実行で自動的に作成されます


## 開発用 Laravel サーバーの起動について
- 本プロジェクトでは、Docker コンテナ起動時に Laravel 開発サーバーは自動起動されません
- 環境構築完了後、実装確認を行う際は以下の手順で手動起動してください
```bash
docker-compose exec php bash
php artisan serve --host=0.0.0.0 --port=8000
```
- ブラウザで以下の URL にアクセスしてください
  - http://localhost:8000/attendance


## メール認証機能について
MailHog を使用して開発環境でメール認証を確認します

### MailHog のセットアップ
1. MailHog をダウンロード・インストール
   - 本プロジェクトでは MailHog v1.0.1 を使用しています<br>
     動作保証のため、以下のバージョンをダウンロードしてください<br>
     [GitHubのリリースページ](https://github.com/mailhog/MailHog/releases/v1.0.1) から使用しているOSに適したバージョンをダウンロードしてください
2. Docker を使用時は `docker-compose.yml` に定義済みです
3. `.env` に以下の環境変数を追加
```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@email.com
MAIL_FROM_NAME="${APP_NAME}"
```
4. MailHog を起動後、以下で送信メールを確認可能
   - http://localhost:8025


## テスト機能について

- 本アプリでは Laravel 標準の PHPUnit を使用してテストを実行します
- テスト実行時には Factory により必要なダミーデータが自動的に生成されます


### 1. テスト環境設定

1. .env.testing ファイルを作成
```bash
cp .env .env.testing
```
2. テスト用DBの作成
```
docker-compose exec mysql bash
```
```
mysql -u root -p
```
```
CREATE DATABASE demo_test
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```
※ パスワードは docker-compose.yml の MYSQL_ROOT_PASSWORD を使用してください<br>

3. .env.testing に以下の環境変数を設定
```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root
```

4. アプリケーションキーを生成
```bash
php artisan key:generate --env=testing
```
5. キャッシュをクリア
```bash
php artisan config:clear
```

### 2. テスト実行手順
1. PHPコンテナに入る
```bash
docker-compose exec php bash
```
2. マイグレーションとシーディングを実行
```bash
php artisan migrate --env=testing
```
※ マイグレーション実行時にエラーが発生する場合 <br>
MySQL の文字コード設定が原因の可能性があります <br>
その場合は、上記のように utf8mb4 / utf8mb4_unicode_ci を指定して <br>
データベースを作成し直してください

3. キャッシュをクリア
```bash
php artisan optimize:clear
```
4. テストを実行
```bash
php artisan test
```

### 3. テストファイル構成について

本アプリではテストを機能ごとにファイルに分けています

| テストID | ファイル                | テスト対象                     |
| ------- | ----------------------- | ----------------------------- |
| ①②③⑯    | AuthAppTest.php         | 認証機能（一般ユーザー・管理者） |
| ④～⑪    | UserAttendanceTest.php  | 一般ユーザー勤怠機能            |
| ⑫～⑮    | AdminAttendanceTest.php | 管理者勤怠機能・ユーザー管理機能 |


### 4. テスト用ダミーデータについて
- ユーザー情報、勤怠情報などはFactoryを用いて自動生成されます
- テスト実行のたびにデータベースが初期化・再生成されます
- テスト内で生成されたデータはテスト終了時に自動的に破棄されます
- Seederは使用していません

## 使用技術（実行環境）
- Laravel : 8.83.8
- PHP : 8.1
- MySQL : 8.0

## テーブル仕様

### usersテーブル
| カラム名           | 型           | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| ----------------- | ------------ | ----------- | ---------- | -------- | ----------- |
| id                | bigint       | ○           | ○          | ○        |             |
| name              | varchar(255) |             |            | ○        |             |
| email             | varchar(255) |             | ○          | ○        |             |
| email_verified_at | timestamp    |             |            |          |             |
| password          | varchar(255) |             |            | ○        |             |
| is_admin          | tinyint(1)   |             |            | ○        |             |
| created_at        | timestamp    |             |            |          |             |
| updated_at        | timestamp    |             |            |          |             |

### attendancesテーブル
**※ user_id と work_date の組み合わせに複合ユニーク制約あり**
| カラム名               | 型           | PRIMARY KEY | UNIQUE KEY                     | NOT NULL | FOREIGN KEY |
| --------------------- | ------------ | ----------- | ------------------------------ | -------- | ----------- |
| id                    | bigint       | ○           |                                | ○        |             |
| user_id               | bigint       |             | ○（work_date とセットでユニーク）| ○        | users(id)   |
| work_date             | date         |             | ○（user_id とセットでユニーク）  | ○        |             |
| start_time            | datetime     |             |                                |          |             |
| end_time              | datetime     |             |                                |          |             |
| status                | varchar(255) |             |                                | ○        |             |
| note                  | text         |             |                                |          |             |
| created_at            | timestamp    |             |                                |          |             |
| updated_at            | timestamp    |             |                                |          |             |

### attendance_breaksテーブル
| カラム名       | 型        | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY     |
| ---------- | ------------ | ----------- | ---------- | -------- | --------------- |
| id            | bigint    | ○           |            | ○        |                 |
| attendance_id | bigint    |             |            | ○        | attendances(id) |
| start_time    | datetime  |             |            | ○        |                 |
| end_time      | datetime  |             |            |          |                 |
| created_at    | timestamp |             |            |          |                 |
| updated_at    | timestamp |             |            |          |                 |

### attendance_update_requestsテーブル
| カラム名       | 型          | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY     |
| ------------- | ----------- | ----------- | ---------- | -------- | --------------- |
| id            | bigint      | ○           |            | ○        |                 |
| user_id       | bigint      |             |            | ○        | users(id)       |
| attendance_id | bigint      |             |            | ○        | attendances(id) |
| work_date     | date        |             |            | ○        |                 |
| start_time    | time        |             |            |          |                 |
| end_time      | time        |             |            |          |                 |
| breaks        | json        |             |            |          |                 |
| note          | text        |             |            |          |                 |
| status        | varchar(20) |             |            | ○        |                 |
| created_at    | timestamp   |             |            |          |                 |
| updated_at    | timestamp   |             |            |          |                 |

## ER図
![ER図](AttendanceApp_ER.png)


## URL (開発環境)
- 一般ユーザー会員登録: http://localhost:8000/register
- 一般ユーザーログイン: http://localhost:8000/login
- 管理者ログイン: http://localhost:8000/admin/login
- phpMyAdmin: http://localhost:8080


## 要件画面定義以外の追加ルート
※画面定義パスに含まれない操作用ルートとして追加しています
### 一般ユーザー
**勤怠打刻操作用のルート（出勤・休憩・退勤）**
- POST /attendance/start … 出勤開始
- POST /attendance/start_break … 休憩開始
- POST /attendance/end_break … 休憩終了
- POST /attendance/end … 退勤

**応用機能メール認証用ルート**
- GET /email/verify … メールアドレス認証画面表示

### 管理者
**応用機能CSV出力用のルート**
- POST /admin/export … 勤怠データをCSV形式で出力


## 追加実装機能
※本機能はコーチとの面談で追加機能提案として挙がり、追加実装したものです

**1. 勤怠データの統合表示（通常勤怠＋申請中［承認待ち］勤怠）**<br>
以下の３画面で本機能を実装しています：

- 一般ユーザー画面：勤怠一覧
- 管理ユーザー画面：日次勤怠一覧
- 管理ユーザー画面：スタッフ毎の月次勤怠一覧

面談時に「申請データも一覧で確認できた方が実務に近く、管理しやすい」とのフィードバックを受け、<br>
通常の Attendance と AttendanceUpdateRequest（修正申請） をまとめて一覧に表示できるように改善<br>
申請データが存在する場合は、申請内容を優先して表示する仕様にしています<br>

**2. 申請中［承認待ち］レコードの視覚的な強調表示**<br>
コーチから「申請中であることが一目でわかるUIにした方が良い」とのフィードバックを受け、<br>
申請中の勤怠行に背景色（薄いピンク）を適用し、申請状態が視覚的に判断しやすいように改善しています<br>

**3. ソート機能の追加**<br>
「申請一覧を確認するときに、対象日順と申請日順を切り替えられると業務で使いやすい」とのフィードバックを受け、<br>
一覧の操作性向上のためソート機能を追加しています<br>
一般ユーザー画面、および管理ユーザー画面の申請一覧において、以下のカラムで昇順/降順の切り替えが可能です

- 対象日（work_date）
- 申請日（created_at）

**4. 未来日の勤怠に対する詳細ボタンの非表示制御**<br>
実装を進める中で、未来日の勤怠データも修正できてしまう点に違和感を持ち、コーチに相談した結果、<br>
一般ユーザーについては、未来日（当日より後の日付）の勤怠レコードでは詳細ボタンを非表示にする仕様に変更しました<br>

一方で管理ユーザーについては、<br>
「事前に勤怠を修正・調整する運用も実務では想定される」との助言を受け、<br>
未来日の勤怠であっても勤怠詳細画面から修正可能な仕様としています<br>

## 追加機能実装によるCSV出力に関する補足
本アプリでは応用機能としてスタッフ毎の月次勤怠一覧のCSV出力を実装しています<br>
この月次勤怠一覧は、追加実装機能として「通常勤怠（Attendance）」と「修正申請（AttendanceUpdateRequest）」を結合して表示する仕様になっています<br>
画面上では以下のように動作します：

- 申請データ（AttendanceUpdateRequest）が存在する場合<br>
  → 申請内容を優先して表示

ただしCSV出力では仕様上、親テーブルである attendances テーブルのデータのみを出力しています<br>
そのため、画面では申請内容が表示されていても、CSVには反映されず、承認前の修正申請内容はCSV出力には含まれません<br>
これは、承認前の申請データを正式な勤怠情報として扱わない仕様であるためです<br>

