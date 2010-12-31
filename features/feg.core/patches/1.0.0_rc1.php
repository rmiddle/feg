<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// ===========================================================================
// Messages change

if(!isset($tables['message']))
	return FALSE;
	
list($columns, $indexes) = $db->metaTable('message');

// Add import status
if(!isset($columns['import_status'])) {
	$db->Execute("ALTER TABLE message ADD COLUMN import_status TINYINT UNSIGNED DEFAULT 0 NOT NULL");
	$db->Execute("ALTER TABLE message ADD INDEX import_status (import_status)");
}

return TRUE;
