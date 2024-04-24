CREATE TABLE tx_laposta_domain_model_subscriptionlist
(
	list_label    varchar(255) DEFAULT '' NOT NULL,
	list_id       varchar(255) DEFAULT '' NOT NULL,
	double_opt_in tinyint(4) DEFAULT '0' NOT NULL,
	info          text
);
