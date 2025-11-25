<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/utils.php';

ensureLoggedIn();
$id = (int) getParam('id', 0);
if ($id <= 0) {
	redirect('/index.php?page=tournaments');
}
$db = getDb();
$stmt = $db->prepare('DELETE FROM tournaments WHERE id = ? AND created_by = ?');
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();
redirect('index.php?page=tournaments');


