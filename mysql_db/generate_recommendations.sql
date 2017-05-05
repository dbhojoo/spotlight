--all CORE tables are normalised meaning that there should not be any duplicates present
--this is to create the recommendations table, a recommendation is any event ranked and shown to the user

CREATE TABLE recommendations (
	id int NOT NULL unique
	, user_id int
	, event_id int
	, rank int
	, accepted binary(1)
	, created_at datetime
	, updated_at datetime
	, PRIMARY KEY (id)
	)
	
	;