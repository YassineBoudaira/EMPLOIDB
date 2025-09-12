<?php
/**
 * Role-Based Access Control (RBAC) Class
 * Handles user roles, permissions, and access control for the admin system
 */
class RBAC {
    private $db;
    private $userRoles = [];
    private $permissions = [];
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get user roles and permissions
     */
    public function getUserRoles($userId) {
        if (isset($this->userRoles[$userId])) {
            return $this->userRoles[$userId];
        }
        
        try {
            $roles = $this->db->fetchAll(
                "SELECT r.* FROM admin_roles r 
                 JOIN admin_user_roles ur ON r.id = ur.role_id 
                 WHERE ur.user_id = ? AND ur.is_active = 1 AND r.is_active = 1",
                [$userId]
            );
            
            $this->userRoles[$userId] = $roles;
            return $roles;
        } catch (Exception $e) {
            error_log("Failed to get user roles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user has permission for specific action/page
     */
    public function hasPermission($userId, $permission) {
        try {
            $roles = $this->getUserRoles($userId);
            
            foreach ($roles as $role) {
                $rolePermissions = json_decode($role['permissions'], true);
                
                // Super admin has all permissions
                if (isset($rolePermissions['all']) && $rolePermissions['all'] === true) {
                    return true;
                }
                
                // Check specific permission
                if (isset($rolePermissions[$permission]) && $rolePermissions[$permission] === true) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Failed to check permission: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has access to specific page
     */
    public function hasPageAccess($userId, $pageName) {
        $pagePermissions = [
            'dashboard' => 'dashboard',
            'users' => 'users',
            'employers' => 'users',
            'jobs' => 'jobs',
            'applications' => 'applications',
            'manage_profiles' => 'users',
            'manage_demands' => 'jobs',
            'manage_contracts' => 'jobs',
            'manage_domains' => 'jobs',
            'manage_cities' => 'jobs',
            'offers' => 'jobs',
            'real_time_monitoring' => 'monitoring',
            'user_analytics' => 'reports',
            'device_analytics' => 'reports',
            'performance_analytics' => 'reports',
            'behavior_analytics' => 'reports',
            'geographic_analytics' => 'reports',
            'manage_advertisers' => 'advertising',
            'manage_campaigns' => 'advertising',
            'manage_ads' => 'advertising',
            'ads_analytics' => 'reports',
            'reports' => 'reports',
            'settings' => 'settings'
        ];
        
        $requiredPermission = $pagePermissions[$pageName] ?? 'dashboard';
        return $this->hasPermission($userId, $requiredPermission);
    }
    
    /**
     * Get user's role names
     */
    public function getUserRoleNames($userId) {
        $roles = $this->getUserRoles($userId);
        return array_column($roles, 'role_name');
    }
    
    /**
     * Check if user is super admin
     */
    public function isSuperAdmin($userId) {
        $roles = $this->getUserRoles($userId);
        
        foreach ($roles as $role) {
            if ($role['role_name'] === 'super_admin') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin($userId) {
        $roles = $this->getUserRoles($userId);
        
        foreach ($roles as $role) {
            if (in_array($role['role_name'], ['super_admin', 'admin'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all available roles
     */
    public function getAllRoles() {
        try {
            return $this->db->fetchAll("SELECT * FROM admin_roles WHERE is_active = 1 ORDER BY role_name");
        } catch (Exception $e) {
            error_log("Failed to get all roles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new role
     */
    public function createRole($roleName, $description, $permissions) {
        try {
            $roleId = $this->db->insert(
                "INSERT INTO admin_roles (role_name, role_description, permissions) VALUES (?, ?, ?)",
                [$roleName, $description, json_encode($permissions)]
            );
            
            return $roleId;
        } catch (Exception $e) {
            error_log("Failed to create role: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update role
     */
    public function updateRole($roleId, $roleName, $description, $permissions) {
        try {
            $this->db->update(
                "UPDATE admin_roles SET role_name = ?, role_description = ?, permissions = ?, updated_at = NOW() WHERE id = ?",
                [$roleName, $description, json_encode($permissions), $roleId]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to update role: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete role
     */
    public function deleteRole($roleId) {
        try {
            // Check if role is assigned to any users
            $assignedUsers = $this->db->fetch(
                "SELECT COUNT(*) as count FROM admin_user_roles WHERE role_id = ?",
                [$roleId]
            );
            
            if ($assignedUsers['count'] > 0) {
                throw new Exception("Cannot delete role: it is assigned to {$assignedUsers['count']} users");
            }
            
            $this->db->update(
                "UPDATE admin_roles SET is_active = 0, updated_at = NOW() WHERE id = ?",
                [$roleId]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to delete role: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Assign role to user
     */
    public function assignRoleToUser($userId, $roleId, $assignedBy = null) {
        try {
            $this->db->insert(
                "INSERT INTO admin_user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE is_active = 1, assigned_at = NOW()",
                [$userId, $roleId, $assignedBy]
            );
            
            // Clear cached user roles
            unset($this->userRoles[$userId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to assign role to user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove role from user
     */
    public function removeRoleFromUser($userId, $roleId) {
        try {
            $this->db->update(
                "UPDATE admin_user_roles SET is_active = 0 WHERE user_id = ? AND role_id = ?",
                [$userId, $roleId]
            );
            
            // Clear cached user roles
            unset($this->userRoles[$userId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to remove role from user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's assigned roles
     */
    public function getUserAssignedRoles($userId) {
        try {
            return $this->db->fetchAll(
                "SELECT r.*, ur.assigned_at, ur.assigned_by 
                 FROM admin_roles r 
                 JOIN admin_user_roles ur ON r.id = ur.role_id 
                 WHERE ur.user_id = ? AND ur.is_active = 1 
                 ORDER BY r.role_name",
                [$userId]
            );
        } catch (Exception $e) {
            error_log("Failed to get user assigned roles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all permissions
     */
    public function getAllPermissions() {
        return [
            'dashboard' => 'Access to dashboard',
            'users' => 'Manage users and employers',
            'jobs' => 'Manage jobs and applications',
            'applications' => 'View and manage applications',
            'monitoring' => 'Access to system monitoring',
            'reports' => 'Access to analytics and reports',
            'settings' => 'Access to system settings',
            'advertising' => 'Manage advertising campaigns',
            'security' => 'Access to security features',
            'system' => 'Access to system administration'
        ];
    }
    
    /**
     * Get default role permissions
     */
    public function getDefaultRolePermissions() {
        return [
            'super_admin' => ['all' => true],
            'admin' => [
                'dashboard' => true,
                'users' => true,
                'jobs' => true,
                'monitoring' => true,
                'reports' => true,
                'settings' => true,
                'advertising' => true,
                'security' => true,
                'system' => true
            ],
            'manager' => [
                'dashboard' => true,
                'users' => true,
                'jobs' => true,
                'reports' => true
            ],
            'recruiter' => [
                'dashboard' => true,
                'jobs' => true,
                'applications' => true,
                'reports' => true
            ],
            'viewer' => [
                'dashboard' => true,
                'reports' => true
            ]
        ];
    }
    
    /**
     * Initialize default roles if they don't exist
     */
    public function initializeDefaultRoles() {
        try {
            $existingRoles = $this->db->fetchAll("SELECT role_name FROM admin_roles");
            $existingRoleNames = array_column($existingRoles, 'role_name');
            
            $defaultPermissions = $this->getDefaultRolePermissions();
            
            foreach ($defaultPermissions as $roleName => $permissions) {
                if (!in_array($roleName, $existingRoleNames)) {
                    $this->createRole($roleName, ucfirst(str_replace('_', ' ', $roleName)), $permissions);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to initialize default roles: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get role statistics
     */
    public function getRoleStatistics() {
        try {
            $stats = $this->db->fetchAll(
                "SELECT r.role_name, COUNT(ur.user_id) as user_count 
                 FROM admin_roles r 
                 LEFT JOIN admin_user_roles ur ON r.id = ur.role_id AND ur.is_active = 1 
                 WHERE r.is_active = 1 
                 GROUP BY r.id, r.role_name 
                 ORDER BY user_count DESC"
            );
            
            return $stats;
        } catch (Exception $e) {
            error_log("Failed to get role statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user can perform action on specific resource
     */
    public function canPerformAction($userId, $action, $resourceType, $resourceId = null) {
        // Super admin can do everything
        if ($this->isSuperAdmin($userId)) {
            return true;
        }
        
        // Check basic permissions
        if (!$this->hasPermission($userId, $resourceType)) {
            return false;
        }
        
        // Check specific action permissions
        $actionPermissions = [
            'create' => ['users', 'jobs', 'applications', 'advertising'],
            'read' => ['users', 'jobs', 'applications', 'reports', 'monitoring'],
            'update' => ['users', 'jobs', 'applications', 'advertising'],
            'delete' => ['users', 'jobs', 'applications', 'advertising'],
            'export' => ['reports', 'monitoring'],
            'configure' => ['settings', 'monitoring']
        ];
        
        if (isset($actionPermissions[$action])) {
            return in_array($resourceType, $actionPermissions[$action]);
        }
        
        return false;
    }
    
    /**
     * Get accessible pages for user
     */
    public function getAccessiblePages($userId) {
        $allPages = [
            'dashboard' => 'Dashboard',
            'users' => 'Users',
            'employers' => 'Employers',
            'jobs' => 'Jobs',
            'applications' => 'Applications',
            'manage_profiles' => 'Profiles',
            'manage_demands' => 'Demands',
            'manage_contracts' => 'Contracts',
            'manage_domains' => 'Domains',
            'manage_cities' => 'Cities',
            'offers' => 'Offers',
            'real_time_monitoring' => 'System Monitoring',
            'user_analytics' => 'User Analytics',
            'device_analytics' => 'Device Analytics',
            'performance_analytics' => 'Performance Analytics',
            'behavior_analytics' => 'Behavior Analytics',
            'geographic_analytics' => 'Geographic Analytics',
            'manage_advertisers' => 'Advertisers',
            'manage_campaigns' => 'Campaigns',
            'manage_ads' => 'Ads',
            'ads_analytics' => 'Ads Analytics',
            'reports' => 'Reports',
            'settings' => 'Settings'
        ];
        
        $accessiblePages = [];
        
        foreach ($allPages as $pageName => $pageTitle) {
            if ($this->hasPageAccess($userId, $pageName)) {
                $accessiblePages[$pageName] = $pageTitle;
            }
        }
        
        return $accessiblePages;
    }
}
?>
