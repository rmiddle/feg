<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// `message_audit_log` ========================
if(!isset($tables['audit_log_message'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS audit_log_message (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			account_id INT UNSIGNED DEFAULT 0 NOT NULL,
			recipient_id INT UNSIGNED DEFAULT 0 NOT NULL,
			message_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			message_recipient_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			worker_id INT UNSIGNED DEFAULT 0 NOT NULL,
			change_date INT UNSIGNED DEFAULT 0 NOT NULL,
			change_field VARCHAR(64) DEFAULT '' NOT NULL,
			change_value VARCHAR(128) DEFAULT '' NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

list($columns, $indexes) = $db->metaTable('audit_log_message');

if(!isset($indexes['account_id'])) {
	$db->Execute('ALTER TABLE audit_log_message ADD INDEX account_id (account_id)');
}

if(!isset($indexes['recipient_id'])) {
	$db->Execute('ALTER TABLE audit_log_message ADD INDEX recipient_id (recipient_id)');
}

if(!isset($indexes['message_id'])) {
	$db->Execute('ALTER TABLE audit_log_message ADD INDEX message_id (message_id)');
}

if(!isset($indexes['message_recipient_id'])) {
	$db->Execute('ALTER TABLE audit_log_message ADD INDEX message_recipient_id (message_recipient_id)');
}

if(!isset($indexes['worker_id'])) {
	$db->Execute('ALTER TABLE audit_log_message ADD INDEX worker_id (worker_id)');
}

return TRUE;
