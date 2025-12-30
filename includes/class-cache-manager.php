<?php
/**
 * Cache Manager
 *
 * Handles caching with support for Redis, Memcached, and WordPress object cache.
 * Provides consistent caching interface with automatic fallback.
 *
 * @package FormFlow
 * @since 2.1.0
 */

namespace FFFL;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CacheManager
 */
class CacheManager {

    /**
     * Singleton instance
     */
    private static ?CacheManager $instance = null;

    /**
     * Cache group name
     */
    public const GROUP = 'fffl_cache';

    /**
     * Cache key prefix
     */
    public const PREFIX = 'fffl_';

    /**
     * Default TTL in seconds (1 hour)
     */
    public const DEFAULT_TTL = 3600;

    /**
     * Cache statistics
     */
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0,
    ];

    /**
     * Cache backend type
     */
    private string $backend = 'transient';

    /**
     * Redis connection (if available)
     */
    private ?\Redis $redis = null;

    /**
     * Whether persistent cache is available
     */
    private bool $persistent_available = false;

    /**
     * Get singleton instance
     *
     * @return CacheManager
     */
    public static function instance(): CacheManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->detect_backend();
    }

    /**
     * Detect available cache backend
     */
    private function detect_backend(): void {
        // Check for Redis
        if ($this->init_redis()) {
            $this->backend = 'redis';
            $this->persistent_available = true;
            return;
        }

        // Check for persistent object cache (Redis Object Cache, Memcached, etc.)
        if (wp_using_ext_object_cache()) {
            $this->backend = 'object_cache';
            $this->persistent_available = true;
            return;
        }

        // Fall back to transients (database-backed)
        $this->backend = 'transient';
        $this->persistent_available = false;
    }

    /**
     * Initialize Redis connection
     *
     * @return bool
     */
    private function init_redis(): bool {
        // Check if Redis extension is available
        if (!class_exists('Redis')) {
            return false;
        }

        // Get Redis configuration
        $host = defined('FFFL_REDIS_HOST') ? FFFL_REDIS_HOST : '127.0.0.1';
        $port = defined('FFFL_REDIS_PORT') ? FFFL_REDIS_PORT : 6379;
        $password = defined('FFFL_REDIS_PASSWORD') ? FFFL_REDIS_PASSWORD : null;
        $database = defined('FFFL_REDIS_DATABASE') ? FFFL_REDIS_DATABASE : 0;

        // Check if Redis is enabled for this plugin
        if (defined('FFFL_DISABLE_REDIS') && FFFL_DISABLE_REDIS) {
            return false;
        }

        try {
            $this->redis = new \Redis();

            // Connect with timeout
            if (!$this->redis->connect($host, $port, 2.0)) {
                $this->redis = null;
                return false;
            }

            // Authenticate if password set
            if ($password) {
                if (!$this->redis->auth($password)) {
                    $this->redis = null;
                    return false;
                }
            }

            // Select database
            $this->redis->select($database);

            // Set prefix
            $this->redis->setOption(\Redis::OPT_PREFIX, self::PREFIX);

            return true;

        } catch (\Exception $e) {
            $this->redis = null;
            return false;
        }
    }

    /**
     * Get a cached value
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null) {
        $full_key = $this->make_key($key);
        $value = null;
        $found = false;

        switch ($this->backend) {
            case 'redis':
                $value = $this->redis_get($full_key, $found);
                break;

            case 'object_cache':
                $value = wp_cache_get($full_key, self::GROUP, false, $found);
                break;

            case 'transient':
            default:
                $value = get_transient($full_key);
                $found = $value !== false;
                break;
        }

        if ($found && $value !== null) {
            $this->stats['hits']++;
            return $value;
        }

        $this->stats['misses']++;
        return $default;
    }

    /**
     * Set a cached value
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function set(string $key, $value, int $ttl = self::DEFAULT_TTL): bool {
        $full_key = $this->make_key($key);
        $result = false;

        switch ($this->backend) {
            case 'redis':
                $result = $this->redis_set($full_key, $value, $ttl);
                break;

            case 'object_cache':
                $result = wp_cache_set($full_key, $value, self::GROUP, $ttl);
                break;

            case 'transient':
            default:
                $result = set_transient($full_key, $value, $ttl);
                break;
        }

        if ($result) {
            $this->stats['writes']++;
        }

        return $result;
    }

    /**
     * Delete a cached value
     *
     * @param string $key Cache key
     * @return bool
     */
    public function delete(string $key): bool {
        $full_key = $this->make_key($key);
        $result = false;

        switch ($this->backend) {
            case 'redis':
                $result = $this->redis->del($full_key) > 0;
                break;

            case 'object_cache':
                $result = wp_cache_delete($full_key, self::GROUP);
                break;

            case 'transient':
            default:
                $result = delete_transient($full_key);
                break;
        }

        if ($result) {
            $this->stats['deletes']++;
        }

        return $result;
    }

    /**
     * Check if a key exists
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool {
        $full_key = $this->make_key($key);

        switch ($this->backend) {
            case 'redis':
                return $this->redis->exists($full_key) > 0;

            case 'object_cache':
                wp_cache_get($full_key, self::GROUP, false, $found);
                return $found;

            case 'transient':
            default:
                return get_transient($full_key) !== false;
        }
    }

    /**
     * Get or set a cached value
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int $ttl Time to live in seconds
     * @return mixed
     */
    public function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL) {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();

        if ($value !== null) {
            $this->set($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * Increment a counter
     *
     * @param string $key Cache key
     * @param int $amount Amount to increment
     * @return int|false New value or false on failure
     */
    public function increment(string $key, int $amount = 1): int|false {
        $full_key = $this->make_key($key);

        switch ($this->backend) {
            case 'redis':
                return $this->redis->incrBy($full_key, $amount);

            case 'object_cache':
                return wp_cache_incr($full_key, $amount, self::GROUP);

            case 'transient':
            default:
                $value = (int) $this->get($key, 0) + $amount;
                $this->set($key, $value);
                return $value;
        }
    }

    /**
     * Decrement a counter
     *
     * @param string $key Cache key
     * @param int $amount Amount to decrement
     * @return int|false New value or false on failure
     */
    public function decrement(string $key, int $amount = 1): int|false {
        $full_key = $this->make_key($key);

        switch ($this->backend) {
            case 'redis':
                return $this->redis->decrBy($full_key, $amount);

            case 'object_cache':
                return wp_cache_decr($full_key, $amount, self::GROUP);

            case 'transient':
            default:
                $value = max(0, (int) $this->get($key, 0) - $amount);
                $this->set($key, $value);
                return $value;
        }
    }

    /**
     * Flush all plugin cache
     *
     * @return bool
     */
    public function flush(): bool {
        switch ($this->backend) {
            case 'redis':
                // Delete all keys with our prefix
                $keys = $this->redis->keys('*');
                if (!empty($keys)) {
                    return $this->redis->del($keys) > 0;
                }
                return true;

            case 'object_cache':
                return wp_cache_flush_group(self::GROUP);

            case 'transient':
            default:
                global $wpdb;
                // Delete all transients with our prefix
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->options}
                         WHERE option_name LIKE %s
                         OR option_name LIKE %s",
                        '_transient_' . self::PREFIX . '%',
                        '_transient_timeout_' . self::PREFIX . '%'
                    )
                );
                return true;
        }
    }

    /**
     * Flush cache for a specific instance
     *
     * @param int $instance_id
     * @return bool
     */
    public function flush_instance(int $instance_id): bool {
        $patterns = [
            "instance_{$instance_id}_*",
            "config_{$instance_id}",
            "slots_{$instance_id}_*",
            "features_{$instance_id}",
        ];

        foreach ($patterns as $pattern) {
            $this->delete_pattern($pattern);
        }

        return true;
    }

    /**
     * Delete keys matching a pattern
     *
     * @param string $pattern Pattern with * wildcard
     * @return int Number of keys deleted
     */
    public function delete_pattern(string $pattern): int {
        $deleted = 0;

        switch ($this->backend) {
            case 'redis':
                $keys = $this->redis->keys($pattern);
                if (!empty($keys)) {
                    $deleted = $this->redis->del($keys);
                }
                break;

            case 'object_cache':
            case 'transient':
            default:
                global $wpdb;

                // Convert pattern to SQL LIKE
                $sql_pattern = self::PREFIX . str_replace('*', '%', $pattern);

                $keys = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT option_name FROM {$wpdb->options}
                         WHERE option_name LIKE %s",
                        '_transient_' . $sql_pattern
                    )
                );

                foreach ($keys as $key) {
                    $key = str_replace('_transient_', '', $key);
                    if (delete_transient($key)) {
                        $deleted++;
                    }
                }
                break;
        }

        $this->stats['deletes'] += $deleted;
        return $deleted;
    }

    /**
     * Get cache key with prefix
     *
     * @param string $key
     * @return string
     */
    private function make_key(string $key): string {
        // For Redis, prefix is set on connection
        if ($this->backend === 'redis') {
            return $key;
        }

        return self::PREFIX . $key;
    }

    /**
     * Redis-specific get with found flag
     *
     * @param string $key
     * @param bool $found
     * @return mixed
     */
    private function redis_get(string $key, bool &$found) {
        $value = $this->redis->get($key);

        if ($value === false) {
            // Check if key exists (false could be a cached false value)
            $found = $this->redis->exists($key) > 0;
            return $found ? false : null;
        }

        $found = true;

        // Attempt to unserialize
        $unserialized = @unserialize($value);
        return $unserialized !== false ? $unserialized : $value;
    }

    /**
     * Redis-specific set
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    private function redis_set(string $key, $value, int $ttl): bool {
        // Serialize complex values
        if (!is_scalar($value)) {
            $value = serialize($value);
        }

        if ($ttl > 0) {
            return $this->redis->setex($key, $ttl, $value);
        }

        return $this->redis->set($key, $value);
    }

    // =========================================================================
    // CONVENIENCE METHODS FOR COMMON CACHE OPERATIONS
    // =========================================================================

    /**
     * Cache instance configuration
     *
     * @param int $instance_id
     * @param array $config
     * @param int $ttl
     * @return bool
     */
    public function cache_instance_config(int $instance_id, array $config, int $ttl = 3600): bool {
        return $this->set("config_{$instance_id}", $config, $ttl);
    }

    /**
     * Get cached instance configuration
     *
     * @param int $instance_id
     * @return array|null
     */
    public function get_instance_config(int $instance_id): ?array {
        return $this->get("config_{$instance_id}");
    }

    /**
     * Cache schedule slots
     *
     * @param int $instance_id
     * @param string $date
     * @param array $slots
     * @param int $ttl
     * @return bool
     */
    public function cache_schedule_slots(int $instance_id, string $date, array $slots, int $ttl = 300): bool {
        return $this->set("slots_{$instance_id}_{$date}", $slots, $ttl);
    }

    /**
     * Get cached schedule slots
     *
     * @param int $instance_id
     * @param string $date
     * @return array|null
     */
    public function get_schedule_slots(int $instance_id, string $date): ?array {
        return $this->get("slots_{$instance_id}_{$date}");
    }

    /**
     * Cache feature settings
     *
     * @param int $instance_id
     * @param array $features
     * @param int $ttl
     * @return bool
     */
    public function cache_features(int $instance_id, array $features, int $ttl = 3600): bool {
        return $this->set("features_{$instance_id}", $features, $ttl);
    }

    /**
     * Get cached feature settings
     *
     * @param int $instance_id
     * @return array|null
     */
    public function get_features(int $instance_id): ?array {
        return $this->get("features_{$instance_id}");
    }

    /**
     * Rate limiting: check and increment
     *
     * @param string $identifier IP address or user identifier
     * @param string $action Action being rate limited
     * @param int $limit Max requests
     * @param int $window Time window in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int]
     */
    public function rate_limit(
        string $identifier,
        string $action,
        int $limit,
        int $window = 60
    ): array {
        $key = "rate_{$action}_" . md5($identifier);

        // Get current count
        $current = (int) $this->get($key, 0);

        if ($current >= $limit) {
            // Get TTL to determine retry time
            $ttl = $this->get_ttl($key);

            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => $ttl > 0 ? $ttl : $window,
            ];
        }

        // Increment counter
        if ($current === 0) {
            $this->set($key, 1, $window);
        } else {
            $this->increment($key);
        }

        return [
            'allowed' => true,
            'remaining' => $limit - $current - 1,
            'retry_after' => 0,
        ];
    }

    /**
     * Get TTL for a key (Redis only, approximate for others)
     *
     * @param string $key
     * @return int TTL in seconds, -1 if no expiry, -2 if not exists
     */
    public function get_ttl(string $key): int {
        $full_key = $this->make_key($key);

        if ($this->backend === 'redis') {
            return $this->redis->ttl($full_key);
        }

        // For transients, we can't get exact TTL
        return -1;
    }

    // =========================================================================
    // DIAGNOSTICS AND STATS
    // =========================================================================

    /**
     * Get cache backend type
     *
     * @return string
     */
    public function get_backend(): string {
        return $this->backend;
    }

    /**
     * Check if persistent cache is available
     *
     * @return bool
     */
    public function is_persistent(): bool {
        return $this->persistent_available;
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function get_stats(): array {
        $stats = $this->stats;
        $stats['backend'] = $this->backend;
        $stats['persistent'] = $this->persistent_available;
        $stats['hit_rate'] = $stats['hits'] + $stats['misses'] > 0
            ? round($stats['hits'] / ($stats['hits'] + $stats['misses']) * 100, 2)
            : 0;

        if ($this->backend === 'redis') {
            try {
                $info = $this->redis->info();
                $stats['redis'] = [
                    'version' => $info['redis_version'] ?? 'unknown',
                    'memory_used' => $info['used_memory_human'] ?? 'unknown',
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'total_keys' => $this->redis->dbSize(),
                ];
            } catch (\Exception $e) {
                $stats['redis_error'] = $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * Health check
     *
     * @return array
     */
    public function health_check(): array {
        $result = [
            'status' => 'healthy',
            'backend' => $this->backend,
            'persistent' => $this->persistent_available,
            'tests' => [],
        ];

        // Test write
        $test_key = 'health_check_' . time();
        $test_value = 'test_' . uniqid();

        $write_ok = $this->set($test_key, $test_value, 60);
        $result['tests']['write'] = $write_ok ? 'pass' : 'fail';

        // Test read
        $read_value = $this->get($test_key);
        $result['tests']['read'] = $read_value === $test_value ? 'pass' : 'fail';

        // Test delete
        $delete_ok = $this->delete($test_key);
        $result['tests']['delete'] = $delete_ok ? 'pass' : 'fail';

        // Determine overall status
        if (in_array('fail', $result['tests'], true)) {
            $result['status'] = 'degraded';
        }

        return $result;
    }
}

/**
 * Helper function to get cache manager instance
 *
 * @return CacheManager
 */
function fffl_cache(): CacheManager {
    return CacheManager::instance();
}
