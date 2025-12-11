# myshop

## データベースセットアップ

```
$ create database myshop;
$ use myshop;

$ create table account(id int primary key auto_increment, user char(32) unique not null, password char(64) not null);

$ create table item(id int primary key auto_increment, name char(64) not null, price decimal(10, 0) not null, description varchar(255));
$ load data infile "path\\to\\item.csv" into table item fields terminated by "," lines terminated by "\n" (name, price, description);

$ create table purchase(id int primary key auto_increment, account_id int not null, date datetime not null, total decimal(10, 0) not null);

$ create table purchase_item(purchase_id int not null, item_id int not null, price decimal(10, 0) not null, quantity int not null, primary key(purchase_id, item_id));

$ create table login_failure(ip char(64) not null, time timestamp not null);
$ create event delete_old_login_failure on schedule every 1 minute do delete from login_attempt where time < (now() - interval 5 minute);
$ set global event_scheduler = on;
```

or

```
$ mysql -u root -p < schema.sql
```
