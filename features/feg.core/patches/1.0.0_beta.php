<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// `worker` =============================
if(!isset($tables['worker'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS worker (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			first_name VARCHAR(255) DEFAULT '' NOT NULL,
			last_name VARCHAR(255) DEFAULT '' NOT NULL,
			title VARCHAR(255) DEFAULT '' NOT NULL,
			email VARCHAR(255) DEFAULT '' NOT NULL,
			pass VARCHAR(32) DEFAULT '' NOT NULL,
			is_superuser TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			last_activity_date INT UNSIGNED DEFAULT 0 NOT NULL,
			last_activity TEXT,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id),
			INDEX last_activity_date (last_activity_date)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `worker_pref` =============================
if(!isset($tables['worker_pref'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS worker_pref (
			worker_id SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
			setting VARCHAR(255) DEFAULT '' NOT NULL,
			value TEXT,
			PRIMARY KEY (worker_id, setting)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `custom_field` =============================
if(!isset($tables['custom_field'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS custom_field (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			name VARCHAR(255) DEFAULT '' NOT NULL,
			type VARCHAR(1) DEFAULT 'S' NOT NULL,
			pos SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
			options LONGTEXT,
			source_extension VARCHAR(255) DEFAULT '' NOT NULL,
			PRIMARY KEY (id),
			INDEX pos (pos),
			INDEX source_extension (source_extension)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `custom_field_clobvalue` =============================
if(!isset($tables['custom_field_clobvalue'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS custom_field_clobvalue (
			field_id INT UNSIGNED DEFAULT 0 NOT NULL,
			source_id INT UNSIGNED DEFAULT 0 NOT NULL,
			field_value MEDIUMTEXT,
			source_extension VARCHAR(255) DEFAULT '' NOT NULL,
			INDEX field_id (field_id),
			INDEX source_id (source_id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `custom_field_numbervalue` =============================
if(!isset($tables['custom_field_numbervalue'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS custom_field_numbervalue (
			field_id INT UNSIGNED DEFAULT 0 NOT NULL,
			source_id INT UNSIGNED DEFAULT 0 NOT NULL,
			field_value INT UNSIGNED DEFAULT 0 NOT NULL,
			source_extension VARCHAR(255) DEFAULT '' NOT NULL,
			INDEX field_id (field_id),
			INDEX source_id (source_id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `custom_field_stringvalue` =============================
if(!isset($tables['custom_field_stringvalue'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS custom_field_stringvalue (
			field_id INT UNSIGNED DEFAULT 0 NOT NULL,
			source_id INT UNSIGNED DEFAULT 0 NOT NULL,
			field_value VARCHAR(255) DEFAULT '' NOT NULL,
			source_extension VARCHAR(255) DEFAULT '' NOT NULL,
			INDEX field_id (field_id),
			INDEX source_id (source_id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `worker_role` =============================
if(!isset($tables['worker_role'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS worker_role (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			name VARCHAR(255) DEFAULT '' NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `worker_role_acl` =============================
if(!isset($tables['worker_role_acl'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS worker_role_acl (
			role_id INT UNSIGNED DEFAULT 0 NOT NULL,
			priv_id VARCHAR(255) DEFAULT '' NOT NULL,
			has_priv TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL,
			INDEX role_id (role_id),
			INDEX priv_id (priv_id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `worker_to_role` =============================
if(!isset($tables['worker_to_role'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS worker_to_role (
			worker_id INT UNSIGNED DEFAULT 0 NOT NULL,
			role_id INT UNSIGNED DEFAULT 0 NOT NULL,
			INDEX worker_id (worker_id),
			INDEX role_id (role_id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `worker_event` =============================
if(!isset($tables['worker_event'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS worker_event (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			created_date INT UNSIGNED DEFAULT 0 NOT NULL,
			worker_id INT UNSIGNED DEFAULT 0 NOT NULL,
			title VARCHAR(255) DEFAULT '' NOT NULL,
			content MEDIUMTEXT,
			is_read TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL,
			url VARCHAR(255) DEFAULT '' NOT NULL,
			PRIMARY KEY (id),
			INDEX created_date (created_date),
			INDEX worker_id (worker_id),
			INDEX is_read (is_read)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['stats'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS stats (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			current_hour INT UNSIGNED DEFAULT 0 NOT NULL,
			current_day INT UNSIGNED DEFAULT 0 NOT NULL,
			fax_current_hour INT UNSIGNED DEFAULT 0 NOT NULL,
			fax_last_hour INT UNSIGNED DEFAULT 0 NOT NULL,
			fax_sent_today INT UNSIGNED DEFAULT 0 NOT NULL,
			fax_sent_yesterday INT UNSIGNED DEFAULT 0 NOT NULL,
			email_current_hour INT UNSIGNED DEFAULT 0 NOT NULL,
			email_last_hour INT UNSIGNED DEFAULT 0 NOT NULL,
			email_sent_today INT UNSIGNED DEFAULT 0 NOT NULL,
			email_sent_yesterday INT UNSIGNED DEFAULT 0 NOT NULL,
			snpp_current_hour INT UNSIGNED DEFAULT 0 NOT NULL,
			snpp_last_hour INT UNSIGNED DEFAULT 0 NOT NULL,
			snpp_sent_today INT UNSIGNED DEFAULT 0 NOT NULL,
			snpp_sent_yesterday INT UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);
	
	$sql = "INSERT INTO stats (id) VALUES(0)";
	$db->Execute($sql);
}
if(!isset($tables['stats_counters'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS stats_counters (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			counter INT UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);
	
	$sql = "INSERT INTO stats_counters (id) VALUES(0)";
	$db->Execute($sql);
}

if(!isset($tables['customer_account'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS customer_account (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			account_number varchar(255) NOT NULL DEFAULT '',
			account_name varchar(255) NOT NULL DEFAULT '',
			import_source INT UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id),
			INDEX is_disabled (is_disabled),
			INDEX account_number (account_number),
			INDEX account_name (account_name)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['customer_recipient'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS customer_recipient (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			account_id INT UNSIGNED DEFAULT 0 NOT NULL,
			export_type INT UNSIGNED DEFAULT 0 NOT NULL,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			type TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			address varchar(255) NOT NULL DEFAULT '',
			address_to varchar(255) NOT NULL DEFAULT '',
			subject varchar(255) NOT NULL DEFAULT '',
			PRIMARY KEY (id),
			INDEX is_disabled (is_disabled),
			INDEX account_id (account_id),
			INDEX address (address)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['message'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS message (
			id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			account_id INT UNSIGNED DEFAULT 0 NOT NULL,
			created_date INT UNSIGNED DEFAULT 0 NOT NULL,
			updated_date INT UNSIGNED DEFAULT 0 NOT NULL,
			params_json longtext,
			message longtext,
			PRIMARY KEY (id),
			INDEX account_id (account_id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['message_recipient'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS message_recipient (
			id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			message_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			recipient_id INT UNSIGNED DEFAULT 0 NOT NULL,
			account_id INT UNSIGNED DEFAULT 0 NOT NULL,
			send_status TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			fax_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			created_date INT UNSIGNED DEFAULT 0 NOT NULL,
			updated_date INT UNSIGNED DEFAULT 0 NOT NULL,
			closed_date INT UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id),
			INDEX message_id (message_id),
			INDEX recipient_id (recipient_id),
			INDEX account_id (account_id),
			INDEX fax_id (account_id),
			INDEX send_status (send_status)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['import_source'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS import_source (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			name varchar(255) NOT NULL DEFAULT '',
			path varchar(255) NOT NULL DEFAULT '',
			type INT UNSIGNED DEFAULT 0 NOT NULL,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id),
			INDEX is_disabled (is_disabled)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['export_type'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS export_type (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			name varchar(255) NOT NULL DEFAULT '',
			recipient_type TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			params_json longtext,
			PRIMARY KEY (id),
			INDEX recipient_type (recipient_type),
			INDEX is_disabled (is_disabled)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

// `export_type_params` =============================
if(!isset($tables['export_type_params'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS export_type_params (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			recipient_type TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			name VARCHAR(255) DEFAULT '' NOT NULL,
			type VARCHAR(1) DEFAULT 'S' NOT NULL,
			pos SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
			options_json LONGTEXT,
			PRIMARY KEY (id),
			INDEX name (name),
			INDEX pos (pos)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
	
	// recipient_type 0 = Email, 1 = Fax, 2 = SNPP
	// type  1 = Yes/No, 2 = 255 Char input
	
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(1, 0, 'Email use sitewide subject', 1, 0, '{\"default\":\"1\"}')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(2, 1, 'Fax use sitewide subject', 1, 0, '{\"default\":\"1\"}')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(3, 2, 'SNPP add subject', 1, 0, '{\"default\":\"0\"}')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(4, 0, 'Email Strip Account Number', 1, 0, '{\"default\":\"0\"}')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(5, 1, 'Fax Strip Account Number', 1, 0, '{\"default\":\"0\"}')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(6, 2, 'SNPP Strip Account Number', 1, 0, '{\"default\":\"0\"}')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(7, 0, 'Email Overide From', 2, 0, '')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(8, 1, 'Fax Overide From', 2, 0, '')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(9, 2, 'SNPP Send to server FQN', 2, 0, '{\"default\":\"ann100sms01.answernet.com\"}')";
	$db->Execute($sql);
	$sql = "INSERT INTO export_type_params (id, recipient_type, name, type, pos, options_json) VALUES(10, 2, 'SNPP Max Number Char', 2, 0, '{\"default\":\"160\"}')";
	$db->Execute($sql);
}

return TRUE;
