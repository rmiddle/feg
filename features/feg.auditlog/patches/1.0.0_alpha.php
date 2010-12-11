<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// `message_audit_log` ========================
if(!isset($tables['message_audit_log'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS ticket_audit_log (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			account_id INT UNSIGNED DEFAULT 0 NOT NULL,
			recipient_id INT UNSIGNED DEFAULT 0 NOT NULL,
			message_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			worker_id INT UNSIGNED DEFAULT 0 NOT NULL,
			change_date INT UNSIGNED DEFAULT 0 NOT NULL,
			change_field VARCHAR(64) DEFAULT '' NOT NULL,
			change_value VARCHAR(128) DEFAULT '' NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}

list($columns, $indexes) = $db->metaTable('message_audit_log');

if(!isset($indexes['account_id'])) {
	$db->Execute('ALTER TABLE message_audit_log ADD INDEX account_id (account_id)');
}

if(!isset($indexes['recipient_id'])) {
	$db->Execute('ALTER TABLE message_audit_log ADD INDEX recipient_id (recipient_id)');
}

if(!isset($indexes['message_id'])) {
	$db->Execute('ALTER TABLE message_audit_log ADD INDEX message_id (message_id)');
}

if(!isset($indexes['worker_id'])) {
	$db->Execute('ALTER TABLE message_audit_log ADD INDEX worker_id (worker_id)');
}

return TRUE;
