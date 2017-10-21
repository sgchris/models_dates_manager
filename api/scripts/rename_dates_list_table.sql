CREATE TABLE "dates_list_tmp" (
	date_ts number primary key,
	description text,
	available_models text,
	excluded_models text,
	chosen_models text,
	hash text
);

insert into dates_list_tmp (date_ts, description, available_models, excluded_models, chosen_models, hash) 
select date_ts, '', available_models, excluded_models, chosen_models, hash from dates_list;

drop table dates_list;

alter table dates_list_tmp rename to dates_list;