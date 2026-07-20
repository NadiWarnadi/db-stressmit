# 🔧 Practical Integration Examples for DB-Stressmit

This document shows **working code examples** for integrating DB-Stressmit into different PHP frameworks.

---

## 📦 Installation (All Frameworks)

```bash
composer require warnadi/db-stressmit
```

---

## 🎯 Integration Examples by Framework

### 1️⃣ Laravel (Auto-Pilot) ⚡ **EASIEST**

No configuration needed! The package auto-loads via Service Provider discovery.

#### Option A: Auto-Pilot (Out of Box)
```php
// No code needed! Just install and it works.
// storage/logs/laravel.log will show alerts automatically
```

#### Option B: Manual Query Auditing
```php
<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use DbStressmit\Stressmit;
use App\Models\Product;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        // Get search term from request
        $term = $request->query('q');
        
        // Option 1: Use Stressmit middleware (automatic)
        // Input validation happens automatically via LaravelStressmitMiddleware
        
        // Option 2: Manual analysis
        $audit = Stressmit::analyze(
            "SELECT * FROM products WHERE name LIKE ?",
            ["%{$term}%"]
        );
        
        if (!$audit['security_report']['is_safe']) {
            return response()->json([
                'error' => 'Security Alert',
                'message' => 'Potential SQL injection detected',
                'issues' => $audit['security_report']['issues']
            ], 403);
        }
        
        return Product::where('name', 'like', "%{$term}%")->get();
    }
}
```

#### Option C: Custom Middleware (Advanced)
```php
<?php
// app/Http/Middleware/CustomStressmitMiddleware.php

namespace App\Http\Middleware;

use DbStressmit\Stressmit;
use Closure;

class CustomStressmitMiddleware
{
    public function handle($request, Closure $next)
    {
        // Audit all request inputs
        $allInputs = $request->all();
        
        foreach ($allInputs as $key => $value) {
            if (is_string($value)) {
                $result = Stressmit::analyze(
                    "SELECT * FROM users WHERE {$key} = '{$value}'"
                );
                
                if (!$result['security_report']['is_safe']) {
                    \Log::warning('DB-Stressmit Alert', [
                        'parameter' => $key,
                        'risk_score' => $result['security_report']['risk_score'],
                        'issues' => $result['security_report']['issues']
                    ]);
                }
            }
        }
        
        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ... existing middleware
    \App\Http\Middleware\CustomStressmitMiddleware::class,
];
```

---

### 2️⃣ Symfony Bundle Integration

#### Setup: Register Bundle
```php
// config/bundles.php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // ... other bundles
    DbStressmit\StressmitBundle::class => ['all' => true],
];
```

#### Option A: Inject in Controller
```php
<?php
// src/Controller/ProductController.php

namespace App\Controller;

use DbStressmit\Stressmit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products/search', name: 'product_search')]
    public function search(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        
        // Audit the query
        $result = Stressmit::analyze(
            "SELECT * FROM products WHERE category = ?",
            [$category]
        );
        
        if (!$result['security_report']['is_safe']) {
            return $this->json([
                'error' => 'Security Alert',
                'issues' => $result['security_report']['issues'],
                'risk_score' => $result['security_report']['risk_score']
            ], 403);
        }
        
        // Process safe query...
        return $this->json(['success' => true]);
    }
}
```

#### Option B: Event Listener (Auto-Audit All Queries)
```php
<?php
// src/EventListener/DbStressmitListener.php

namespace App\EventListener;

use DbStressmit\Stressmit;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Psr\Log\LoggerInterface;

class DbStressmitListener
{
    public function __construct(private LoggerInterface $logger) {}
    
    public function postFlush(PostFlushEventArgs $args): void
    {
        // This would hook into Doctrine to audit queries
        // Implementation depends on your Doctrine setup
    }
    
    public function auditQuery(string $sql, array $params = []): void
    {
        $result = Stressmit::analyze($sql, $params);
        
        if (!$result['security_report']['is_safe']) {
            $this->logger->warning('DB-Stressmit Alert', [
                'query' => $result['query'],
                'risk_score' => $result['security_report']['risk_score'] . '/100',
                'issues' => $result['security_report']['issues']
            ]);
        }
    }
}
```

Register in `config/services.yaml`:
```yaml
App\EventListener\DbStressmitListener:
    tags:
        - { name: doctrine.orm.entity_listener }
```

---

### 3️⃣ WordPress Integration

#### Option A: Create Custom Plugin
```php
<?php
// wp-content/plugins/db-stressmit-audit/db-stressmit-audit.php

/**
 * Plugin Name: DB-Stressmit Audit
 * Description: Database security auditor for WordPress
 * Version: 1.0
 * Author: Your Name
 */

require_once __DIR__ . '/vendor/autoload.php';

use DbStressmit\Stressmit;

// Hook into WordPress query execution
add_filter('query', function($query) {
    if (is_admin() || defined('WP_IMPORTING')) {
        return $query;
    }
    
    // Only audit SELECT/UPDATE/DELETE queries (not internal WordPress queries)
    if (!preg_match('/^(SELECT|UPDATE|DELETE|INSERT)/i', trim($query))) {
        return $query;
    }
    
    $result = Stressmit::analyze($query);
    
    if (!$result['security_report']['is_safe']) {
        error_log('[DB-Stressmit Alert] ' . json_encode([
            'query' => $result['query'],
            'risk_score' => $result['security_report']['risk_score'],
            'issues' => $result['security_report']['issues']
        ]));
    }
    
    return $query;
});

// Add admin dashboard widget
add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget(
        'db_stressmit_widget',
        'DB-Stressmit Security Status',
        function() {
            echo '<p>Database security monitoring is active.</p>';
            echo '<p>Check WordPress error log for alerts.</p>';
        }
    );
});
```

#### Option B: In Theme Functions
```php
<?php
// wp-content/themes/your-theme/functions.php

require_once get_template_directory() . '/vendor/autoload.php';

use DbStressmit\Stressmit;

function my_theme_audit_user_input() {
    global $wpdb;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $search = $_POST['s'] ?? '';
        
        if (!empty($search)) {
            $audit = Stressmit::analyze(
                $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_title LIKE %s", "%{$search}%")
            );
            
            if (!$audit['security_report']['is_safe']) {
                wp_die('Security alert: Potential SQL injection detected.');
            }
        }
    }
}

add_action('init', 'my_theme_audit_user_input');
```

---

### 4️⃣ CodeIgniter 4 Integration

#### Option A: In BaseController
```php
<?php
// app/Controllers/BaseController.php

namespace App\Controllers;

use CodeIgniter\Controller;
use DbStressmit\Stressmit;

class BaseController extends Controller
{
    protected $db;
    protected $helpers = [];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        
        $this->db = \Config\Database::connect();
    }

    protected function executeAndAudit(string $sql, array $params = [])
    {
        $result = Stressmit::executeAndAudit($this->db, $sql, $params);
        
        if (!$result['security_report']['is_safe']) {
            log_message('warning', 'DB-Stressmit Alert: ' . json_encode($result));
        }
        
        return $result;
    }
}
```

#### Option B: Custom Service
```php
<?php
// app/Libraries/DatabaseAuditor.php

namespace App\Libraries;

use DbStressmit\Stressmit;
use PDO;

class DatabaseAuditor
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function auditQuery(string $sql, array $params = [])
    {
        return Stressmit::analyze($sql, $params);
    }
    
    public function executeAndAudit(string $sql, array $params = [])
    {
        return Stressmit::executeAndAudit($this->db, $sql, $params);
    }
}
```

Usage in Controller:
```php
<?php
// app/Controllers/ProductController.php

namespace App\Controllers;

use App\Libraries\DatabaseAuditor;

class ProductController extends BaseController
{
    protected $auditor;
    
    public function __construct()
    {
        $this->auditor = new DatabaseAuditor();
    }
    
    public function index()
    {
        $result = $this->auditor->executeAndAudit(
            "SELECT * FROM products WHERE status = ?",
            ['active']
        );
        
        if (!$result['security_report']['is_safe']) {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Security Alert']);
        }
        
        return $this->response->setJSON(['data' => $result]);
    }
}
```

---

### 5️⃣ Vanilla PHP Application

#### Option A: Simple Analysis
```php
<?php
require 'vendor/autoload.php';

use DbStressmit\Stressmit;

// Get user input (dangerous way - for demo only)
$userId = $_GET['id'] ?? '';

// Analyze the query
$result = Stressmit::analyze(
    "SELECT * FROM users WHERE id = " . $userId
);

if (!$result['security_report']['is_safe']) {
    http_response_code(403);
    echo json_encode([
        'error' => 'Security Alert',
        'issues' => $result['security_report']['issues']
    ]);
    exit;
}

// Execute safe query
$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
```

#### Option B: Execute and Audit
```php
<?php
require 'vendor/autoload.php';

use DbStressmit\Stressmit;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');

// Execute and audit in one step
$result = Stressmit::executeAndAudit(
    $pdo,
    "SELECT * FROM products WHERE category = ?",
    [$_GET['category'] ?? '']
);

// Check results
if ($result['status'] === 'SUCCESS') {
    if (!$result['security_report']['is_safe']) {
        echo "⚠️ Warning: Potential security issue detected!";
        print_r($result['security_report']['issues']);
    } else {
        echo "✅ Query safe and executed successfully";
        echo "Execution time: " . $result['execution_time_ms'] . "ms";
    }
} else {
    echo "❌ Error: " . $result['database_error'];
}
```

---

### 6️⃣ CLI Tool Usage (All Frameworks)

#### Scan Single SQL File
```bash
php vendor/bin/db-stressmit --scan --file=queries.sql
```

#### Scan Project Directory
```bash
php vendor/bin/db-stressmit --scan --path=./src
```

#### Filter by Framework
```bash
# Scan Laravel project
php vendor/bin/db-stressmit --scan --path=./app --framework=laravel

# Scan WordPress
php vendor/bin/db-stressmit --scan --path=./wp-content --framework=wordpress
```

#### Output Formats
```bash
# Table format (default)
php vendor/bin/db-stressmit --scan --path=./src --format=table

# JSON output
php vendor/bin/db-stressmit --scan --path=./src --format=json > report.json

# HTML report
php vendor/bin/db-stressmit --scan --path=./src --format=html > report.html
```

---

## 📊 Testing Your Integration

### Laravel Test
```php
<?php
// tests/Feature/StressmitTest.php

namespace Tests\Feature;

use DbStressmit\Stressmit;
use Tests\TestCase;

class StressmitTest extends TestCase
{
    public function test_safe_query_passes()
    {
        $result = Stressmit::analyze(
            "SELECT * FROM users WHERE id = ?",
            [1]
        );
        
        $this->assertTrue($result['security_report']['is_safe']);
    }
    
    public function test_sqli_detected()
    {
        $result = Stressmit::analyze(
            "SELECT * FROM users WHERE id = 1 OR 1=1"
        );
        
        $this->assertFalse($result['security_report']['is_safe']);
        $this->assertStringContainsString('Logical Tautology', $result['security_report']['issues'][0]);
    }
}
```

### Symfony Test
```php
<?php
// tests/StressmitTest.php

namespace App\Tests;

use DbStressmit\Stressmit;
use PHPUnit\Framework\TestCase;

class StressmitTest extends TestCase
{
    public function testSafeQuery(): void
    {
        $result = Stressmit::analyze(
            "SELECT * FROM products WHERE price > ?",
            [100]
        );
        
        $this->assertTrue($result['security_report']['is_safe']);
    }
    
    public function testUnionInjection(): void
    {
        $result = Stressmit::analyze(
            "SELECT * FROM users WHERE email = ? UNION SELECT * FROM admins"
        );
        
        $this->assertFalse($result['security_report']['is_safe']);
    }
}
```

---

## 🚀 Production Deployment Checklist

- ✅ Set application environment to `production` (disables audit logging)
- ✅ Test with load testing tool to verify no performance degradation
- ✅ Set up log monitoring for development environment alerts
- ✅ Document alert procedures for your team
- ✅ Regular security audits using CLI tool
- ✅ Keep composer package updated: `composer update warnadi/db-stressmit`

---

**Ready to integrate? Start with your framework above! 🚀**
