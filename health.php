<?php
header('Content-Type: application/json');
header('HTTP/1.1 200 OK');
echo json_encode(['status' => 'healthy', 'timestamp' => date('c')]);