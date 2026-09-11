<?php
$query = http_build_query(array_merge($_GET, ['tab' => 'utilidades']));
header("Location: reportes.php?$query");
exit;
