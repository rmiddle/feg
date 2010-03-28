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

if(!isset($tables['customer_account'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS customer_account (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			account_number varchar(255) NOT NULL DEFAULT '',
			account_name varchar(255) NOT NULL DEFAULT '',
			import_filter INT UNSIGNED DEFAULT 0 NOT NULL,
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
			export_filter INT UNSIGNED DEFAULT 0 NOT NULL,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			type TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL,
			address varchar(255) NOT NULL DEFAULT '',
			PRIMARY KEY (id),
			INDEX is_disabled (is_disabled),
			INDEX account_id (account_id),
			INDEX address (address)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['export_filter'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS export_filter (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			filter_name varchar(255) NOT NULL DEFAULT '',
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			filter longtext,
			PRIMARY KEY (id),
			INDEX is_disabled (is_disabled)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

if(!isset($tables['import_filter'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS import_filter (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			filter_name varchar(255) NOT NULL DEFAULT '',
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			filter longtext,
			PRIMARY KEY (id),
			INDEX is_disabled (is_disabled)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

return TRUE;
