-- Вывести все товары и категорию, в которой они находятся.
SELECT "item_name", "category_title" FROM "item" NATURAL JOIN "category";

-- Вывести все товары из конкретного заказа.
SELECT "item_name" FROM "item__order" NATURAL JOIN "item" WHERE "item__order".order_id=?;

-- Вывести все заказы с конкретной единицей товара.
SELECT "order_address" FROM "item__order" NATURAL JOIN "order" WHERE "item__order".item_id=?;

-- Вывести все товары, заказанные за последний час.
SELECT "item_name" FROM "order" NATURAL JOIN "item__order" NATURAL JOIN "item" WHERE "order_created" >= NOW() - '1 HOUR'::INTERVAL;

-- Вывести все товары, заказанные за сегодня.
SELECT "item_name" FROM "order" NATURAL JOIN "item__order" NATURAL JOIN "item" WHERE "order_created" >= CURRENT_DATE;

-- Вывести все товары, заказанные за вчера.
SELECT "item_name" FROM "order" NATURAL JOIN "item__order" NATURAL JOIN "item" WHERE "order_created" >= CURRENT_DATE - '24 HOUR'::INTERVAL AND "order_created" <= CURRENT_DATE;

-- Вывести все товары из заданной категории, заказанные за последний час.
SELECT "item_name" FROM "order" NATURAL JOIN "item__order" NATURAL JOIN "item" WHERE "order_created" >= NOW() - '1 HOUR'::INTERVAL AND "item".category_id=?;

-- Вывести все товары из заданной категории, заказанные за сегодня.
SELECT "item_name" FROM "order" NATURAL JOIN "item__order" NATURAL JOIN "item" WHERE "order_created" >= CURRENT_DATE AND "item".category_id=?;

-- Вывести все товары из заданной категории, заказанные за вчера.
SELECT "item_name" FROM "order" NATURAL JOIN "item__order" NATURAL JOIN "item" WHERE "order_created" >= CURRENT_DATE - '24 HOUR'::INTERVAL AND "order_created" <= CURRENT_DATE AND "item".category_id=?;

-- Вывести все товары, названия которых начинаются с заданной последовательности букв (см. LIKE).
SELECT "item_name" FROM "item" WHERE "item_name" LIKE '?%';

-- Вывести все товары, названия которых заканчиваются заданной последовательностью букв (см. LIKE).
SELECT "item_name" FROM "item" WHERE "item_name" LIKE '%?';

-- Вывести все товары, названия которых содержат заданные последовательности букв (см. LIKE).
SELECT "item_name" FROM "item" WHERE "item_name" LIKE '%?%';

-- Вывести список категорий и количество товаров в каждой категории.
SELECT "category_title", COUNT("item_id") FROM "item" NATURAL JOIN "category" GROUP BY "category_title";

-- Вывести список всех заказов и количество товаров в каждом.
SELECT "order_address", COUNT("item_id") FROM "item__order" RIGHT OUTER JOIN "order" ON "order".order_id="item__order".order_id GROUP BY "order_address";

-- Вывести список всех товаров и количество заказов, в которых имеется этот товар.
SELECT "item_name", COUNT("order_id") FROM "item__order" RIGHT OUTER JOIN "item" ON "item".item_id="item__order".item_id GROUP BY "item_name";

-- Вывести список заказов, упорядоченный по дате заказа и суммарную стоимость товаров в каждом из них.
SELECT "order_address", SUM("item_price" * "item__order_quantity") FROM "item__order" NATURAL JOIN "item" NATURAL JOIN "order" GROUP BY "order_address", "order_created" ORDER BY "order_created";

-- Вывести список товаров, цену, количество и суммарную стоимость каждого из них в заказе с заданным ID.
SELECT "item_name", "item_price", "item__order_quantity", SUM("item_price"*"item__order_quantity") FROM "item__order" NATURAL JOIN "item" WHERE "order_id"=? GROUP BY "item_name", "item_price", "item__order_quantity";

-- Для заданного ID заказа вывести список категорий, товары из которых присутствуют в этом заказе. Для каждой из категорий вывести суммарное количество и суммарную стоимость товаров.
SELECT "category_title", SUM("item__order_quantity"), SUM("item_price" * "item__order_quantity") FROM "item__order" NATURAL JOIN "item" NATURAL JOIN "category" WHERE "order_id"=? GROUP BY "category_title";

-- Вывести список клиентов, которые заказывали товары из категории с заданным ID за последние 3 дня.
SELECT "customer_name" FROM "item" NATURAL JOIN customer NATURAL JOIN "order" NATURAL JOIN "item__order" WHERE "order_created" >= CURRENT_DATE - '3 DAY'::INTERVAL AND "category_id"=?;

-- Вывести имена всех клиентов, производивших заказы за последние сутки.
SELECT "customer_name" FROM "order" NATURAL JOIN "customer" WHERE "order_created" >= NOW() - '1 DAY'::INTERVAL;

-- Вывести всех клиентов, производивших заказы, содержащие товар с заданным ID.
SELECT "customer_name" FROM "item__order" NATURAL JOIN "customer" NATURAL JOIN "order" WHERE "item_id"=?;

-- Для каждой категории вывести урл загрузки изображения с именем category_image в формате 'http://img.domain.com/category/<category_id>.jpg' для включенных категорий, и 'http://img.domain.com/category/<category_id>_disabled.jpg' для выключеных.
SELECT CASE "category_enabled" WHEN true THEN format('http://img.domain.com/category/%s.jpg', "category_id") ELSE format( 'http://img.domain.com/category/%s_disabled.jpg', "category_id") END AS category_image FROM "category";

-- Для товаров, которые были заказаны за все время во всех заказах общим количеством более X единиц, установить item_popular = TRUE, для остальных — FALSE.
UPDATE "item" SET "item_popular" = result FROM ( SELECT "item_id", SUM("item__order_quantity") < ? AS result FROM "item__order" GROUP BY "item_id" ) AS "item__order" WHERE "item".item_id="item__order".item_id;

-- Одним запросом для указанных ID категорий установить флаг category_enabled = TRUE, для остальных — FALSE. Не применять WHERE.
UPDATE "category" SET "category_enabled"= CASE WHEN "category_id" = ? OR "category_id" = ? THEN TRUE ELSE FALSE END;
