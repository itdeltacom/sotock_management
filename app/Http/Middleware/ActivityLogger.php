<?php

namespace App\Http\Middleware;

use App\Models\Activity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    /**
     * Routes that should not be logged
     *
     * @var array
     */
    protected $excludedRoutes = [
        'admin.activities.*', // Prevent recursive logging
        'admin.dashboard.chart-data', // High frequency API calls
    ];

    /**
     * Actions that should be logged
     *
     * @var array
     */
    protected $logActions = [
        'store' => 'create',
        'update' => 'update',
        'destroy' => 'delete',
        'login' => 'login',
        'logout' => 'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request first
        $response = $next($request);
        
        // Skip logging for excluded routes
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }
        
        // Only log specific HTTP methods or admin actions
        if ($this->shouldLogRequest($request)) {
            $this->logActivity($request, $response);
        }
        
        return $response;
    }
    
    /**
     * Determine if the request should be logged
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldLogRequest(Request $request): bool
    {
        // Always log POST, PUT, PATCH, DELETE requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }
        
        // Log specific admin actions
        $routeName = $request->route()->getName() ?? '';
        $routeAction = $request->route()->getActionMethod() ?? '';
        
        // Check for login/logout actions
        if (strpos($routeName, 'login') !== false || strpos($routeAction, 'login') !== false) {
            return true;
        }
        
        if (strpos($routeName, 'logout') !== false || strpos($routeAction, 'logout') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine if logging should be skipped for this request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        $routeName = $request->route()->getName() ?? '';
        
        // Skip excluded routes
        foreach ($this->excludedRoutes as $excludedRoute) {
            if (Str::is($excludedRoute, $routeName)) {
                return true;
            }
        }
        
        // Skip assets, API calls, etc.
        if ($request->is('*/assets/*', '*/api/*', '*/json/*', '*/chart-data*')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Log the activity
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    protected function logActivity(Request $request, $response): void
    {
        // Get route information
        $routeName = $request->route()->getName() ?? '';
        $routeAction = $request->route()->getActionMethod() ?? '';
        
        // Determine activity type based on route or method
        $type = $this->getActivityType($routeName, $routeAction, $request->method());
        
        // Get user if authenticated
        $user = auth()->guard('admin')->user();
        
        // Build title and description
        $title = $this->getActivityTitle($type, $routeName, $routeAction);
        $description = $this->getActivityDescription($type, $request, $routeName, $routeAction);
        
        // Get subject if available
        $subject = $this->getActivitySubject($request);
        
        // Additional properties
        $properties = [
            'route' => $routeName,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->header('User-Agent'),
            'referer' => $request->header('referer'),
        ];
        
        // Log the activity
        Activity::log(
            $type,
            $title,
            $description,
            $user,
            $subject,
            $properties
        );
    }
    
    /**
     * Get the activity type
     *
     * @param  string  $routeName
     * @param  string  $routeAction
     * @param  string  $method
     * @return string
     */
    protected function getActivityType(string $routeName, string $routeAction, string $method): string
    {
        // Check for known actions
        foreach ($this->logActions as $action => $type) {
            if (strpos($routeAction, $action) !== false || strpos($routeName, $action) !== false) {
                return $type;
            }
        }
        
        // Default based on HTTP method
        switch ($method) {
            case 'POST':
                return 'create';
            case 'PUT':
            case 'PATCH':
                return 'update';
            case 'DELETE':
                return 'delete';
            default:
                return 'access';
        }
    }
    
    /**
     * Get the activity title
     *
     * @param  string  $type
     * @param  string  $routeName
     * @param  string  $routeAction
     * @return string
     */
    protected function getActivityTitle(string $type, string $routeName, string $routeAction): string
    {
        // For login/logout actions
        if ($type === 'login') {
            return 'User Login';
        }
        
        if ($type === 'logout') {
            return 'User Logout';
        }
        
        // Extract resource name from route name
        $resource = $this->extractResourceName($routeName, $routeAction);
        
        // Format title based on type and resource
        switch ($type) {
            case 'create':
                return "Created {$resource}";
            case 'update':
                return "Updated {$resource}";
            case 'delete':
                return "Deleted {$resource}";
            default:
                return "Accessed {$resource}";
        }
    }
    
    /**
     * Get the activity description
     *
     * @param  string  $type
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $routeName
     * @param  string  $routeAction
     * @return string
     */
    protected function getActivityDescription(string $type, Request $request, string $routeName, string $routeAction): string
    {
        // For login/logout actions
        if ($type === 'login') {
            return 'User logged in successfully';
        }
        
        if ($type === 'logout') {
            return 'User logged out successfully';
        }
        
        // Extract resource name from route name
        $resource = $this->extractResourceName($routeName, $routeAction);
        
        // Get subject ID if available
        $id = $this->getSubjectId($request);
        $idStr = $id ? " (ID: {$id})" : '';
        
        // Format description based on type and resource
        switch ($type) {
            case 'create':
                return "Created a new {$resource}{$idStr}";
            case 'update':
                return "Updated {$resource}{$idStr}";
            case 'delete':
                return "Deleted {$resource}{$idStr}";
            default:
                return "Accessed {$resource}";
        }
    }
    
    /**
     * Extract the resource name from the route name
     *
     * @param  string  $routeName
     * @param  string  $routeAction
     * @return string
     */
    protected function extractResourceName(string $routeName, string $routeAction): string
    {
        // Try to extract from route name first
        $parts = explode('.', $routeName);
        
        // Remove the prefix and action parts
        if (count($parts) >= 3) {
            // Remove the first part (admin) and last part (action)
            array_shift($parts); // Remove 'admin'
            array_pop($parts); // Remove action (store, update, etc.)
            $resource = implode(' ', $parts);
        } else {
            // Fallback to the second part if available
            $resource = $parts[1] ?? '';
        }
        
        // Clean up resource name to be more readable
        $resource = str_replace(['-', '.'], ' ', $resource);
        $resource = trim($resource);
        
        // If we couldn't extract a meaningful resource name, try from controller action
        if (empty($resource) || in_array($resource, ['index', 'store', 'update', 'destroy'])) {
            // Try to guess from the controller name
            if ($request->route()->getController()) {
                $controller = class_basename($request->route()->getController());
                $controller = str_replace('Controller', '', $controller);
                
                // Make it more readable
                $resource = Str::snake($controller, ' ');
            }
        }
        
        // If still empty, use a generic name
        if (empty($resource)) {
            $resource = 'resource';
        }
        
        // Singularize the resource name if it ends with 's'
        if (Str::endsWith($resource, 's')) {
            $resource = Str::singular($resource);
        }
        
        return Str::title($resource);
    }
    
    /**
     * Get the subject model from the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed|null
     */
    protected function getActivitySubject(Request $request)
    {
        // Try to get the subject from route parameters
        $parameters = $request->route()->parameters();
        
        // Return the first model instance found in the parameters
        foreach ($parameters as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getKey')) {
                return $parameter;
            }
        }
        
        return null;
    }
    
    /**
     * Get the subject ID from the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed|null
     */
    protected function getSubjectId(Request $request)
    {
        // Try to get from route parameters
        $parameters = $request->route()->parameters();
        
        foreach ($parameters as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getKey')) {
                return $parameter->getKey();
            } elseif (is_numeric($parameter)) {
                return $parameter;
            }
        }
        
        // Check common ID fields in request data
        foreach (['id', 'record_id', 'item_id'] as $idField) {
            if ($request->has($idField)) {
                return $request->input($idField);
            }
        }
        
        return null;
    }
}