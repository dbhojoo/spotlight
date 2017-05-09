--all CORE tables are normalised meaning that there should not be any duplicates present
--this is to create the events table, an event is an organised activity that will be attended by customers

CREATE TABLE events (
	id int NOT NULL unique
	, name varchar(150) NOT NULL
	, location varchar(150) NOT NULL
	, event_date datetime
	, event_type varchar(150)
	, ticket_code int 
	, address varchar(300) NOT NULL
	, postal_code varchar(150)
	, telephone_contact int(14)
	, facebook_api varchar(300)
	, created_at datetime
	, updated_at datetime
	, created_by_id int
	, restricted BINARY(1)
	, restriction_desc varchar(300)
	, PRIMARY KEY (id)
	)
	
	;