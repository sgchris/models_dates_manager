-- useful scripts

---- schemas
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

-- alter table dates_list !!!! SHOULD BE MODIFIED !!!!!

CREATE TABLE dates_list_tmp (
	date_ts number primary key,
	excluded_models text,
	chosen_models text,
	type text,
	hash text
);

insert into dates_list_tmp (date_ts, excluded_models, chosen_models, hash) select * from dates_list;

drop table dates_list;

alter table dates_list_tmp rename to dates_list;
