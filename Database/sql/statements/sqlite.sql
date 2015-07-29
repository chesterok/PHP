sqlite3 shop;

"N/A";

"N/A";

CREATE TABLE "category" (
	category_id integer NOT NULL,
	category_name varchar(30) NOT NULL,
	PRIMARY KEY (category_id)
);

INSERT INTO "category" (category_id, category_name) VALUES
	('1', 'Mobile'),
	('2', 'PC'),
	('3', 'TV')
;

CREATE TABLE "item" (
	item_id integer NOT NULL,
	category_id integer NOT NULL,
	item_name varchar(30) NOT NULL,
	item_price decimal NOT NULL,
	PRIMARY KEY (item_id),
	FOREIGN KEY (category_id) REFERENCES "category" (category_id)
);

INSERT INTO "item" (item_id, category_id, item_name, item_price) VALUES
	('1', '1', 'nokia', '1.0'),
	('2', '1', 'iphone', '1.0'),
	('3', '1', 'samsung', '1.0'),
	('4', '1', 'htc', '5.2'),
	('5', '2', 'aser', '5.7'),
	('6', '2', 'asus', '8.2'),
	('7', '2', 'bravo', '6.2'),
	('8', '3', 'sony', '7.5'),
	('9', '3', 'samsung', '8.3'),
	('10', '3', 'lg', '9.4')
;

UPDATE "item" SET item_price='3.50' WHERE item_id='1';

UPDATE "item" SET item_price=(SELECT item_price) * 1.1;

DELETE FROM "item" WHERE item_id='2';

SELECT item_name FROM "item" ORDER BY item_name;

SELECT item_name FROM "item" ORDER BY item_price DESC;

SELECT item_name FROM "item" ORDER BY item_price LIMIT 3;

SELECT item_name FROM "item" ORDER BY item_price DESC LIMIT 3 OFFSET 3;

SELECT item_name FROM "item" WHERE item_price = (SELECT MAX(item_price) FROM "item");

SELECT item_name FROM "item" ORDER BY item_price DESC LIMIT 1;

SELECT item_name FROM "item" WHERE item_price = (SELECT MIN(item_price) FROM "item");

SELECT item_name FROM "item" ORDER BY item_price LIMIT 1;

SELECT COUNT(item_name) FROM "item";

SELECT AVG(item_price) FROM "item";

CREATE VIEW "expensiveItem" AS SELECT * FROM "item" ORDER BY item_price DESC LIMIT 3;