# attendance-app (coachtech 勤怠管理アプリ)

## 環境構築


### Dockerビルド手順

1. リポジトリをクローン
```bash
git clone git@github.com:Oda-mi/attendance-app.git
```
2. docker-compose.yml があるディレクトリへ移動
```bash
cd attendance-app
```
3. Docker Desktop を起動

4. コンテナをビルドして起動
```bash
docker-compose up -d --build
```


### Laravel環境構築手順

※ 本プロジェクトは PHP 8.1 を使用しています<br>
※ PHP 8.2 以上では依存関係の都合により `composer install` でエラーが発生する可能性があります

***Dockerを使用する場合***

1. PHPコンテナに入る
```bash
docker-compose exec php bash
```
2. 依存関係をインストール
```bash
composer install
```

***Dockerを使用しない場合***
1. Laravel 本体が `src` 配下にあるため、`src` へ移動してください
```bash
cd src
```
2. 依存関係をインストール
```bash
composer install
```

***共通設定（Dockerあり/なし共通）***
1. `.env.example` をコピーして `.env` ファイルを作成
```bash
cp .env.example .env
```
2. `.env` に以下の環境変数を設定
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
3. アプリケーションキーを生成
```bash
php artisan key:generate
```
4. マイグレーションを実行
```bash
php artisan migrate
```
> ※マイグレーション実行時にエラーが発生する場合は、Docker の状態が原因の可能性があります<br>
> その場合は、コンテナの再起動やキャッシュクリアを行った上で再実行してください
5. シーディングを実行
```bash
php artisan db:seed
```


## ダミーユーザー情報（シーディング用）
- **管理ユーザー**
  - 名前: 管理者
  - メール: admin@example.com
  - パスワード: admin123
- **一般ユーザー**
  - 名前: テスト太郎
  - メール: test@example.com
  - パスワード: password123

> ※シーダー実行で自動的に作成されます


## 開発用 Laravel サーバーの起動について
- 本プロジェクトでは、Docker コンテナ起動時に Laravel 開発サーバーは自動起動されません
- 環境構築完了後、実装確認を行う際は以下の手順で手動起動してください
1. PHPコンテナに入る
```bash
docker-compose exec php bash
```
2. Laravel開発サーバーを起動
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
- ブラウザで以下の URL にアクセスしてください
  - http://localhost:8000/attendance


## メール認証機能について
MailHog を使用して開発環境でメール認証を確認します

### MailHog のセットアップ
1. MailHog をダウンロード・インストール<br>
- 本プロジェクトでは MailHog v1.0.1 を使用しています<br>
- 動作保証のため、以下のバージョンをダウンロードしてください<br>
- [GitHubのリリースページ](https://github.com/mailhog/MailHog/releases/v1.0.1) から使用しているOSに適したバージョンをダウンロードしてください
2. Docker を使用時は `docker-compose.yml` に定義済みです
3. `.env` に以下の環境変数を設定
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


### テスト環境構築＆テスト実行手順（Docker）

1. MySQL コンテナに入る
```bash
docker-compose exec mysql bash
```
2. MySQL接続
```bash
mysql -u root -p
```
> ※ パスワードは docker-compose.yml の MYSQL_ROOT_PASSWORD を使用してください<br>
3. テスト用DB作成
```bash
CREATE DATABASE demo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
> ※ テスト用DB作成時に文字コード・照合順序を指定しています<br>
> 環境によっては `CREATE DATABASE demo_test;` のみでは<br>
> マイグレーション実行時に文字コード不一致エラーが発生するため、<br>
> 本手順では `utf8mb4 / utf8mb4_unicode_ci` を明示しています

4. MySQLコンテナから抜ける（`exit`は２回実行）
```bash
exit
exit
```
5. PHPコンテナに入る
```bash
docker-compose exec php bash
```

6. コンテナ内で `.env.testing` ファイルを作成
```bash
cp .env .env.testing
```

7. `.env.testing` に以下の環境変数を設定
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root
```

8. アプリケーションキーを生成
```bash
php artisan key:generate --env=testing
```
9. キャッシュをクリア
```bash
php artisan config:clear
```
10. マイグレーション実行
```bash
php artisan migrate --env=testing
```
> ※ マイグレーション実行時にエラーが発生する場合、MySQL の文字コード設定が原因の可能性があります<br>
その場合は、上記のように `utf8mb4 / utf8mb4_unicode_ci` を指定してデータベースを作成し直してください
11. キャッシュをクリア
```bash
php artisan optimize:clear
```
12. テストを実行
```bash
php artisan test
```

### テストファイル構成について

本アプリではテストを機能ごとにファイルに分けています

| テストID | ファイル                | テスト対象                     |
| ------- | ----------------------- | ----------------------------- |
| ①②③⑯    | AuthAppTest.php         | 認証機能（一般ユーザー・管理者） |
| ④～⑪    | UserAttendanceTest.php  | 一般ユーザー勤怠機能            |
| ⑫～⑮    | AdminAttendanceTest.php | 管理者勤怠機能・ユーザー管理機能 |


### テスト用ダミーデータについて
- ユーザー情報、勤怠情報などはFactoryを用いて自動生成されます
- テスト実行のたびにデータベースが初期化・再生成されます
- テスト内で生成されたデータはテスト終了時に自動的に破棄されます
- Seederは使用していません


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

## 使用技術（実行環境）
- Laravel : 8.83.8
- PHP : 8.1
- MySQL : 8.0

## URL (開発環境)
- 一般ユーザー会員登録: http://localhost:8000/register
- 一般ユーザーログイン: http://localhost:8000/login
- 管理者ログイン: http://localhost:8000/admin/login
- phpMyAdmin: http://localhost:8080


## 要件画面定義以外の追加ルート
※画面定義パスに含まれない操作用ルートとして追加しています
### 一般ユーザー
- **勤怠打刻操作用のルート（出勤・休憩・退勤）**
  - POST /attendance/start … 出勤開始
  - POST /attendance/start_break … 休憩開始
  - POST /attendance/end_break … 休憩終了
  - POST /attendance/end … 退勤

- **応用機能メール認証用ルート**
  - GET /email/verify … メールアドレス認証画面表示

### 管理者
- **応用機能CSV出力用のルート**
  - POST /admin/export … 勤怠データをCSV形式で出力



## 追加実装機能
※本機能はコーチとの面談で追加機能提案として挙がり、追加実装したものです

**1. 勤怠データの統合表示（通常勤怠＋申請中［承認待ち］勤怠）**<br>
以下の３画面で本機能を実装しています

- 一般ユーザー　：勤怠一覧画面
- 管理者ユーザー：日次勤怠一覧画面
- 管理者ユーザー：スタッフ毎の月次勤怠一覧画面

本機能は実装を進める中で、<br>
「申請中の勤怠データが一覧画面上でどのように見えるべきか」<br>
という点に疑問を感じたことをきっかけに追加実装したものです<br>

具体的には、以下の点でUX上の違和感がありました

- 勤怠一覧画面では Attendance のレコードが表示されるため、一覧上では申請中かどうか判別しにくい点
- 既存勤怠が存在しない状態で一般ユーザーが新規勤怠申請を行った場合、 Attendance 側の表示が空欄となる点

これらの点についてコーチに相談した結果、<br>
「申請データも一覧で確認できた方が実務に近く、管理しやすい」とのフィードバックを受け、<br>
通常の Attendance と AttendanceUpdateRequest（修正申請） をまとめて一覧に表示する仕様に改善を行いました<br>

申請データが存在する場合は、申請内容を優先して表示することで、<br>
一覧画面上でも申請状況を直感的に把握できるようにしています

**2. 申請中［承認待ち］レコードの視覚的な強調表示**<br>
上記の「勤怠データの統合表示」により、<br>
一覧画面上でも申請中の勤怠データを表示できるようになった一方で、<br>
申請状態がさらに直感的に把握できるよう、UI面での改善も行っています<br>

コーチから「申請中であることが一目でわかるUIにした方が良い」とのフィードバックを受け、<br>
申請中の勤怠行に背景色（薄いピンク）を適用しています<br>

これにより、一覧画面上でも申請状態を視覚的に判断しやすいUIとなるように改善を行いました<br>

**3. ソート機能の追加**<br>
コーチから、<br>
「申請一覧を確認するときに、対象日順と申請日順を切り替えられると業務で使いやすい」<br>
とのフィードバックを受け、一覧の操作性向上のためソート機能を追加しています<br>
一般ユーザー画面、および管理ユーザー画面の申請一覧において、<br>
以下のカラムで昇順/降順の切り替えが可能です

- 対象日（work_date）
- 申請日（created_at）

**4. 未来日の勤怠に対する詳細ボタンの非表示制御**<br>
実装を進める中で、<br>
未来日の勤怠データも修正できてしまう点に違和感を持ち、コーチに相談しました<br>

その結果、一般ユーザーについては、<br>
未来日（当日より後の日付）の勤怠レコードでは詳細ボタンを非表示にする仕様に変更しています<br>

一方で管理ユーザーについては、<br>
「事前に勤怠を修正・調整する運用も実務では想定される」との助言を受け、<br>
未来日の勤怠であっても勤怠詳細画面から修正可能な仕様としています<br>

**5. 修正申請承認画面（管理者）における休憩表示仕様の調整**<br>
管理者側の修正申請承認画面について、<br>
参考UIでは存在する休憩レコードに加えて「空の休憩欄」が表示される仕様となっていました<br>

しかし、本アプリでは、以下の仕様で実装しています

- 休憩時間は存在するレコードのみを表示
- 一般ユーザーの勤怠詳細画面（承認待ち勤怠ケース）と同一のUI・表示ルール

一般ユーザーと管理者ユーザーで参考UIの挙動が異なる点についてコーチに相談したところ、<br>
「一般ユーザーの承認待ち画面と同じ仕様で問題ない」との回答を受けたため、<br>
管理者側の修正申請承認画面でも空の休憩欄は表示しない仕様に統一しました

これにより、不要な空欄表示をなくし、<br>
実際に存在するデータのみが確認できる分かりやすいUIとなるよう調整しています

## 追加機能実装によるCSV出力に関する補足
本アプリでは応用機能として<br>
スタッフ毎の月次勤怠一覧のCSV出力を実装しています<br>

この月次勤怠一覧は、追加実装機能として<br>
「通常勤怠（Attendance）」と<br>
「修正申請（AttendanceUpdateRequest）」を結合して表示する仕様になっています<br>

画面上では以下のように動作します

- 申請データ（AttendanceUpdateRequest）が存在する場合<br>
  → 申請内容を優先して表示

ただしCSV出力では仕様上、<br>
親テーブルである attendances テーブルのデータのみを出力しています<br>
そのため、画面では申請内容が表示されていても、<br>
承認前の修正申請内容はCSVには反映されません<br>
これは、承認前の申請データを正式な勤怠情報として扱わない運用を想定とした仕様としています

