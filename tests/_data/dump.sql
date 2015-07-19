BEGIN TRANSACTION;
CREATE TABLE `template_has_item` (
	`template_id`	INTEGER,
	`item_id`	INTEGER,
	`ord`	INTEGER
);
INSERT INTO `template_has_item` VALUES (2,1,1),
	(2,2,0),
	(3,3,0);
CREATE TABLE `template` (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`title`	TEXT NOT NULL
);
INSERT INTO `template` VALUES (1,'Первый шаблон'),
	(2,'Второй шаблон'),
	(3,'Третий шаблон');
CREATE TABLE `record` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`template_id`	INTEGER,
	`title`	INTEGER
);
INSERT INTO `record` VALUES (1,1,'Запись 1'),
	(2,2,'Запись 2');
CREATE TABLE `item` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`title`	TEXT
);
INSERT INTO `item` VALUES (1,'First item'),
	(2,'Second item'),
	(3,'Third item');
COMMIT;
