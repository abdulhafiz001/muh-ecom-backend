<?php

// Simple test script to verify the Al-Anwar e-commerce backend API
// Run this from the backend directory: php test_api.php

echo "🧪 Testing Al-Anwar E-commerce Backend API\n";
echo "==========================================\n\n";

$baseUrl = 'http://localhost:8000/api';

// Test 1: Get categories
echo "1. Testing GET /api/categories\n";
$response = file_get_contents($baseUrl . '/categories');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Success! Found " . count($data['data']) . " categories\n";
        foreach ($data['data'] as $category) {
            echo "      - " . $category['name'] . "\n";
        }
    } else {
        echo "   ❌ Failed: " . $data['message'] . "\n";
    }
} else {
    echo "   ❌ Failed to connect to API\n";
}
echo "\n";

// Test 2: Get products
echo "2. Testing GET /api/products\n";
$response = file_get_contents($baseUrl . '/products');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Success! Found " . count($data['data']['data']) . " products\n";
        foreach ($data['data']['data'] as $product) {
            echo "      - " . $product['name'] . " ($" . $product['price'] . ")\n";
        }
    } else {
        echo "   ❌ Failed: " . $data['message'] . "\n";
    }
} else {
    echo "   ❌ Failed to connect to API\n";
}
echo "\n";

// Test 3: Get featured products
echo "3. Testing GET /api/products/featured\n";
$response = file_get_contents($baseUrl . '/products/featured');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Success! Found " . count($data['data']) . " featured products\n";
        foreach ($data['data'] as $product) {
            echo "      - " . $product['name'] . " (Featured: " . ($product['is_featured'] ? 'Yes' : 'No') . ")\n";
        }
    } else {
        echo "   ❌ Failed: " . $data['message'] . "\n";
    }
} else {
    echo "   ❌ Failed to connect to API\n";
}
echo "\n";

// Test 4: Get products by category
echo "4. Testing GET /api/products/category/1\n";
$response = file_get_contents($baseUrl . '/products/category/1');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Success! Found " . count($data['data']['data']) . " products in category\n";
    } else {
        echo "   ❌ Failed: " . $data['message'] . "\n";
    }
} else {
    echo "   ❌ Failed to connect to API\n";
}
echo "\n";

// Test 5: Admin products endpoint
echo "5. Testing GET /api/admin/products\n";
$response = file_get_contents($baseUrl . '/admin/products');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Success! Admin products endpoint working\n";
        echo "      Found " . count($data['data']['data']) . " products\n";
    } else {
        echo "   ❌ Failed: " . $data['message'] . "\n";
    }
} else {
    echo "   ❌ Failed to connect to API\n";
}
echo "\n";

echo "🎯 API Testing Complete!\n";
echo "If all tests passed, your backend is working correctly.\n";
echo "You can now integrate this with your React frontend.\n\n";

echo "📋 Available API Endpoints:\n";
echo "   - GET  /api/categories\n";
echo "   - GET  /api/products\n";
echo "   - GET  /api/products/featured\n";
echo "   - GET  /api/products/{id}\n";
echo "   - GET  /api/products/category/{id}\n";
echo "   - POST /api/cart/add\n";
echo "   - GET  /api/cart\n";
echo "   - POST /api/orders\n";
echo "   - GET  /api/orders\n";
echo "   - GET  /api/admin/products\n";
echo "   - POST /api/admin/products\n";
echo "   - PUT  /api/admin/products/{id}\n";
echo "   - DELETE /api/admin/products/{id}\n\n";

echo "🔑 Sample Credentials:\n";
echo "   Admin: admin@alanwar.com / password123\n";
echo "   User:  john.doe@example.com / password123\n";
echo "   User:  jane.smith@example.com / password123\n";
