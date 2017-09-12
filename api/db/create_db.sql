
CREATE TABLE "models" (
	id integer primary key autoincrement,
	name text,
	category text,
	tags,
	notes text,
	private_notes,
	images text,
	hash text,
	display_order number
);
CREATE TABLE models_categories (id integer primary key autoincrement, name text);
CREATE TABLE "dates_models_categories"  (
	date_id integer,
	models_category_id integer,
	hash text,
	PRIMARY KEY (hash)
);
CREATE TABLE "dates_list" (
	date_ts number primary key,
	available_models text,
	excluded_models text,
	chosen_models text,
	hash text
);
