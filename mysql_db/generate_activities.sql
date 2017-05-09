--all CORE tables are normalised meaning that there should not be any duplicates present
--this is to create the activities table, an activity is any action taken by an individual with the app/platform

CREATE TABLE activities (
	id int NOT NULL unique
	, user_id int
	, event_id int
	, pageid int
	, created_at datetime
	, updated_at datetime
	, PRIMARY KEY (id)
	)
	
	;