CREATE DATABASE shop;

GRANT ALL ON shop.* TO `shop`@`localhost` IDENTIFIED BY 'password';

GRANT SELECT ON shop.* TO `viewer`@`localhost` IDENTIFIED BY 'password';

CREATE TABLE `category` (
	category_id integer AUTO_INCREMENT,
	category_name varchar(30) NOT NULL,
	PRIMARY KEY (category_id)
);

INSERT INTO `category` (category_name) VALUES
	('Mobile'),
	('PC'),
	('TV')
;

CREATE TABLE `item` (
	item_id integer AUTO_INCREMENT,
	category_id integer NOT NULL,
	item_name varchar(30) NOT NULL,
	item_price double NOT NULL,
	PRIMARY KEY (item_id)
);

ALTER TABLE `item` ADD CONSTRAINT fk_item_category FOREIGN KEY (category_id) REFERENCES `category` (category_id);

INSERT INTO `item` (category_id, item_name, item_price) VALUES
	('1', 'nokia', '1.0'),
	('1', 'iphone', '1.0'),
	('1', 'samsung', '1.0'),
	('1', 'htc', '5.2'),
	('2', 'aser', '5.7'),
	('2', 'asus', '8.2'),
	('2', 'bravo', '6.2'),
	('3', 'sony', '7.5'),
	('3', 'samsung', '8.3'),
	('3', 'lg', '9.4')
;

UPDATE `item` SET item_price='3.50' WHERE item_id='1';

UPDATE `item` SET item_price=(SELECT item_price) * 1.1;

DELETE FROM `item` WHERE item_id='2';

SELECT item_name FROM `item` ORDER BY item_name;

SELECT item_name FROM `item` ORDER BY item_price DESC;

SELECT item_name FROM `item` ORDER BY item_price DESC LIMIT 3;

SELECT item_name FROM `item` ORDER BY item_price LIMIT 3;

SELECT item_name FROM `item` ORDER BY item_price DESC LIMIT 3, 3;

SELECT item_name FROM `item` WHERE item_price = (SELECT MAX(item_price) FROM `item`);

SELECT item_name FROM `item` ORDER BY item_price DESC LIMIT 1;

SELECT item_name FROM `item` WHERE item_price = (SELECT MIN(item_price) FROM `item`);

SELECT item_name FROM `item` ORDER BY item_price LIMIT 1;

SELECT COUNT(item_name) FROM `item`;

SELECT AVG(item_price) FROM `item`;

CREATE VIEW `expensiveItem` AS SELECT * FROM `item` ORDER BY item_price DESC LIMIT 3;