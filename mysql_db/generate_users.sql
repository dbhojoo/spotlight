--all CORE tables are normalised meaning that there should not be any duplicates present
--this is to create the users table, an user is an indivudal that may access the platform (marketer or customer)

CREATE TABLE users (
	id int NOT NULL unique
	, last_name varchar(150) NOT NULL
	, first_name varchar(150) NOT NULL
	, age int
	, address varchar(300) NOT NULL
	, postal_code varchar(150)
	, telephone_contact int(14)
	, sex CHAR(1)
	, birthdate DATE
	, facebook_api varchar(300)
	, created_at datetime
	, updated_at datetime
	, marketer BINARY(1)
	, PRIMARY KEY (id)
	)
	
	;