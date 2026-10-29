<?php
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';

$products = get_posts(["post_type" => "product", "posts_per_page" => -1]);
$data = [];
foreach ($products as $p) {
    $data[] = ["id" => $p->ID, "title" => $p->post_title];
}
file_put_contents(__DIR__ . "/products_dump.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Exported " . count($data) . " products to " . __DIR__ . "/products_dump.json\n";
