--all CORE tables are normalised meaning that there should not be any duplicates present
--this is to create the purchases table, a purchase is a completed transaction by customer for an event

CREATE TABLE purchases (
	id int NOT NULL unique
	, user_id int
	, event_id int
	, payment_date datetime
	, completed_date datetime
	, payment_provider varchar(150)
	, status varchar(150)
	, transaction_response varchar(150)
	, voucher_code varchar(300)
	, payment_amount float(100000,2)
	, created_at datetime
	, updated_at datetime
	, PRIMARY KEY (id)
	)
	
	;