<?php
/**
 * Import Sales Records from CSV
 * Usage: php import_sales_csv.php <file.csv>
 */

require_once 'app/public/wp-load.php';

$file = $argv[1] ?? 'sales_record_template.csv';
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

$fp = fopen($file, 'r');
if (!$fp) {
    echo "Cannot open file: $file\n";
    exit(1);
}

// Remove BOM if present
$bom = fread($fp, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($fp);
}

$header = fgetcsv($fp);
if (!$header) {
    echo "Empty CSV file.\n";
    exit(1);
}

echo "=== Processing Import from $file ===\n";

$created_count = 0;
$updated_count = 0;
$row_idx = 1;

while (($row = fgetcsv($fp)) !== false) {
    $row_idx++;
    if (empty(array_filter($row))) continue; // skip completely empty rows

    $order_no       = trim($row[0] ?? '');
    $sale_date      = trim($row[1] ?? '') ?: current_time('Y-m-d');
    $customer_val   = trim($row[2] ?? '');
    $products_val   = trim($row[3] ?? '');
    $cost_price     = trim($row[4] ?? '');
    $selling_price  = trim($row[5] ?? '');
    $bargained_price= trim($row[6] ?? '');
    $shipping_paid  = trim($row[7] ?? '');
    $shipping_actual= trim($row[8] ?? '');
    $status         = strtolower(trim($row[9] ?? '')) ?: 'shipped';
    $delivery_method= strtolower(trim($row[10] ?? '')) ?: 'ems';
    $lazada_order   = trim($row[11] ?? '');
    $notes          = trim($row[12] ?? '');

    // 1. Resolve Customer ID
    $customer_id = 0;
    if (is_numeric($customer_val)) {
        $customer_id = (int)$customer_val;
    } elseif (!empty($customer_val)) {
        $c_post = get_page_by_title($customer_val, OBJECT, 'customer');
        if ($c_post) {
            $customer_id = $c_post->ID;
        } else {
            // Try searching customer by meta wsr_social_name
            $found = get_posts([
                'post_type' => 'customer',
                'meta_key' => 'wsr_social_name',
                'meta_value' => $customer_val,
                'posts_per_page' => 1
            ]);
            if (!empty($found)) {
                $customer_id = $found[0]->ID;
            } else {
                // Auto create customer
                $new_c_id = wp_insert_post([
                    'post_title' => $customer_val,
                    'post_type' => 'customer',
                    'post_status' => 'publish'
                ]);
                if (!is_wp_error($new_c_id)) {
                    $customer_id = $new_c_id;
                    update_post_meta($customer_id, 'wsr_social_name', $customer_val);
                    update_post_meta($customer_id, 'wsr_sales_channel', 'facebook');
                    echo "  [Line $row_idx] Created new customer: '$customer_val' (ID: $customer_id)\n";
                }
            }
        }
    }

    // 2. Resolve Product IDs & default costs/prices
    $product_ids = [];
    $sum_default_cost = 0;
    $sum_default_price = 0;
    $lazada_orders = [];

    if (!empty($products_val)) {
        $p_items = array_map('trim', explode(',', $products_val));
        foreach ($p_items as $p_item) {
            $pid = 0;
            if (is_numeric($p_item)) {
                $pid = (int)$p_item;
            } else {
                $p_post = get_page_by_title($p_item, OBJECT, 'product');
                if ($p_post) {
                    $pid = $p_post->ID;
                } else {
                    // Try searching by title like
                    $search = get_posts([
                        'post_type' => 'product',
                        's' => $p_item,
                        'posts_per_page' => 1
                    ]);
                    if (!empty($search)) {
                        $pid = $search[0]->ID;
                    }
                }
            }

            if ($pid > 0) {
                $product_ids[] = $pid;
                $p_cost = (float)get_post_meta($pid, 'wsr_default_cost', true);
                $p_price = (float)get_post_meta($pid, 'wsr_default_price', true);
                $p_lazada = get_post_meta($pid, 'wsr_lazada_order', true);

                $sum_default_cost += $p_cost;
                $sum_default_price += $p_price;
                if (!empty($p_lazada)) {
                    $lazada_orders[] = $p_lazada;
                }
            }
        }
    }

    // If cost_price / selling_price empty, fallback to calculated sums
    if ($cost_price === '') {
        $cost_price = $sum_default_cost;
    } else {
        $cost_price = (float)$cost_price;
    }

    if ($selling_price === '') {
        $selling_price = $sum_default_price;
    } else {
        $selling_price = (float)$selling_price;
    }

    if ($bargained_price === '') {
        $bargained_price = $selling_price;
    } else {
        $bargained_price = (float)$bargained_price;
    }

    if (empty($lazada_order) && !empty($lazada_orders)) {
        $lazada_order = implode(', ', array_unique($lazada_orders));
    }

    // 3. Resolve or Generate Order Number
    if (empty($order_no)) {
        $date_suffix = str_replace('-', '', substr($sale_date, 2));
        $rand_num = rand(1000, 9999);
        $order_no = "ORD-{$rand_num}-{$date_suffix}";
    }

    // 4. Create or Update sales_record
    $existing = get_page_by_title($order_no, OBJECT, 'sales_record');
    if ($existing) {
        $post_id = $existing->ID;
        wp_update_post([
            'ID' => $post_id,
            'post_date' => $sale_date . ' 12:00:00',
        ]);
        $updated_count++;
        echo "  [Line $row_idx] Updated Sales Record #$order_no (ID: $post_id)\n";
    } else {
        $post_id = wp_insert_post([
            'post_title' => $order_no,
            'post_type' => 'sales_record',
            'post_status' => 'publish',
            'post_date' => $sale_date . ' 12:00:00',
        ]);
        if (is_wp_error($post_id)) {
            echo "  [Line $row_idx ERROR] Failed to create $order_no: " . $post_id->get_error_message() . "\n";
            continue;
        }
        $created_count++;
        echo "  [Line $row_idx] Created Sales Record #$order_no (ID: $post_id)\n";
    }

    // 5. Update Meta Fields
    update_post_meta($post_id, 'wsr_sale_date', $sale_date);
    update_post_meta($post_id, 'wsr_status', $status);
    update_post_meta($post_id, 'wsr_delivery_method', $delivery_method);
    update_post_meta($post_id, 'wsr_customer_id', $customer_id);
    update_post_meta($post_id, 'wsr_product_ids', $product_ids);
    update_post_meta($post_id, 'wsr_cost_price', (float)$cost_price);
    update_post_meta($post_id, 'wsr_selling_price', (float)$selling_price);
    update_post_meta($post_id, 'wsr_bargained_price', (float)$bargained_price);
    update_post_meta($post_id, 'wsr_shipping_paid', (float)$shipping_paid);
    update_post_meta($post_id, 'wsr_shipping_actual', (float)$shipping_actual);
    update_post_meta($post_id, 'wsr_lazada_order', $lazada_order);
    update_post_meta($post_id, 'wsr_notes', $notes);
}

fclose($fp);
echo "=== Import Finished! Created: $created_count, Updated: $updated_count ===\n";

