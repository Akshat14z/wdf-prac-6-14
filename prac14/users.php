<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config.php';
    
    // Require admin access
    $userAuth->requireAdmin();
    
    $currentUser = $userAuth->getCurrentUser();
    $error = '';
    $success = '';
    
    // Handle AJAX requests
    if (isset($_POST['action']) && $_POST['action'] === 'ajax') {
        header('Content-Type: application/json');
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !SecurityUtil::verifyCSRFToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid security token']);
            exit;
        }
        
        $userId = (int)($_POST['user_id'] ?? 0);
        $adminId = $userAuth->getCurrentUserId();
        
        switch ($_POST['ajax_action'] ?? '') {
            case 'update_status':
                $newStatus = $_POST['new_status'] ?? '';
                if (in_array($newStatus, ['active', 'inactive', 'suspended'])) {
                    $result = $adminManager->updateUserStatus($userId, $newStatus, $adminId);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid status']);
                }
                break;
                
            case 'update_role':
                $newRole = $_POST['new_role'] ?? '';
                if (in_array($newRole, ['super_admin', 'admin', 'moderator', 'user'])) {
                    $result = $adminManager->updateUserRole($userId, $newRole, $adminId);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid role']);
                }
                break;
                
            case 'delete_user':
                $result = $adminManager->deleteUser($userId, $adminId);
                echo json_encode($result);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        exit;
    }
    
    // Handle form submissions for regular requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !SecurityUtil::verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid security token. Please try again.';
        } else {
            // Handle non-AJAX form submissions here if needed
        }
    }
    
    // Handle success messages from redirects
    if (isset($_GET['success'])) {
        $success = 'Operation completed successfully.';
    }
    
    // Get filter parameters
    $page = max(1, (int)($_GET['page'] ?? 1));
    $search = SecurityUtil::sanitizeInput($_GET['search'] ?? '', 'string');
    $roleFilter = SecurityUtil::sanitizeInput($_GET['role'] ?? '', 'string');
    $statusFilter = SecurityUtil::sanitizeInput($_GET['status'] ?? '', 'string');
    $limit = 10;
    
    // Get users data
    $usersData = $adminManager->getAllUsers($page, $limit, $search, $roleFilter, $statusFilter);
    
    // Generate CSRF token
    $csrfToken = SecurityUtil::generateCSRFToken();
    ?>

    <div class="container">
        <!-- Header Section -->
        <header class="header fade-in">
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-users"></i> User Management</h1>
                    <p>Manage user accounts, roles, and permissions</p>
                </div>
                <nav class="nav-menu">
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="users.php" class="nav-item active">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="profile.php" class="nav-item">
                        <i class="fas fa-user-cog"></i> Profile
                    </a>
                    <a href="logout.php" class="nav-item" onclick="return confirm('Are you sure you want to log out?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
        </header>

        <!-- Status Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error fade-in">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Search and Filters -->
        <div class="filters fade-in">
            <form method="GET" action="users.php" class="filter-form">
                <div class="filter-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <input type="text" 
                               name="search" 
                               placeholder="Search by username, email, or name..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               style="margin: 0;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <select name="role" style="margin: 0;">
                            <option value="">All Roles</option>
                            <option value="super_admin" <?php echo $roleFilter === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="moderator" <?php echo $roleFilter === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                            <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <select name="status" style="margin: 0;">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?php echo $statusFilter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card fade-in">
            <div class="card-header">
                <h3>
                    <i class="fas fa-list"></i> User List 
                    <span style="font-size: 0.8rem; color: #CDC9C3;">(<?php echo $usersData['total']; ?> total users)</span>
                </h3>
                <p>Showing page <?php echo $page; ?> of <?php echo $usersData['pages']; ?></p>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usersData['users'] as $user): ?>
                            <tr data-user-id="<?php echo $user['id']; ?>">
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong><br>
                                        <small style="color: #CDC9C3;">
                                            @<?php echo htmlspecialchars($user['username']); ?> • <?php echo htmlspecialchars($user['email']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <?php echo date('M j, Y g:i A', strtotime($user['last_login'])); ?>
                                    <?php else: ?>
                                        <span style="color: #CDC9C3;">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Status Toggle Dropdown -->
                                        <div class="dropdown" style="position: relative; display: inline-block;">
                                            <button class="btn btn-sm btn-secondary dropdown-btn" onclick="toggleDropdown(this)">
                                                <i class="fas fa-toggle-on"></i> Status
                                            </button>
                                            <div class="dropdown-content" style="display: none; position: absolute; background: #FBF7F0; min-width: 120px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); z-index: 1; border-radius: 4px; border: 1px solid #CDC9C3;">
                                                <button onclick="updateUserStatus(<?php echo $user['id']; ?>, 'active')" style="display: block; width: 100%; padding: 8px 12px; background: none; border: none; text-align: left; cursor: pointer; color: #555555;">
                                                    <i class="fas fa-check-circle" style="color: #2d5a2d;"></i> Active
                                                </button>
                                                <button onclick="updateUserStatus(<?php echo $user['id']; ?>, 'inactive')" style="display: block; width: 100%; padding: 8px 12px; background: none; border: none; text-align: left; cursor: pointer; color: #555555;">
                                                    <i class="fas fa-pause-circle" style="color: #CDC9C3;"></i> Inactive
                                                </button>
                                                <button onclick="updateUserStatus(<?php echo $user['id']; ?>, 'suspended')" style="display: block; width: 100%; padding: 8px 12px; background: none; border: none; text-align: left; cursor: pointer; color: #555555;">
                                                    <i class="fas fa-ban" style="color: #8b0000;"></i> Suspended
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Role Change Dropdown (Super Admin only) -->
                                        <?php if ($userAuth->hasRole('super_admin')): ?>
                                            <div class="dropdown" style="position: relative; display: inline-block;">
                                                <button class="btn btn-sm btn-warning dropdown-btn" onclick="toggleDropdown(this)">
                                                    <i class="fas fa-user-cog"></i> Role
                                                </button>
                                                <div class="dropdown-content" style="display: none; position: absolute; background: #FBF7F0; min-width: 140px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); z-index: 1; border-radius: 4px; border: 1px solid #CDC9C3;">
                                                    <button onclick="updateUserRole(<?php echo $user['id']; ?>, 'super_admin')" style="display: block; width: 100%; padding: 8px 12px; background: none; border: none; text-align: left; cursor: pointer; color: #555555;">
                                                        <i class="fas fa-crown"></i> Super Admin
                                                    </button>
                                                    <button onclick="updateUserRole(<?php echo $user['id']; ?>, 'admin')" style="display: block; width: 100%; padding: 8px 12px; background: none; border: none; text-align: left; cursor: pointer; color: #555555;">
                                                        <i class="fas fa-user-shield"></i> Admin
                                                    </button>
                                                    <button onclick="updateUserRole(<?php echo $user['id']; ?>, 'moderator')" style="display: block; width: 100%; padding: 8px 12px; background: none; border: none; text-align: left; cursor: pointer; color: #555555;">
                                                        <i class="fas fa-user-cog"></i> Moderator
                                                    </button>
                                                    <button onclick="updateUserRole(<?php echo $user['id']; ?>, 'user')" style="display: block; width: 100%; padding: 8px 12px; background: none; border: none; text-align: left; cursor: pointer; color: #555555;">
                                                        <i class="fas fa-user"></i> User
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Delete Button -->
                                        <?php if ($user['id'] != $currentUser['id']): ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($usersData['users'])): ?>
                    <div style="text-align: center; padding: 3rem; color: #CDC9C3;">
                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No users found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($usersData['pages'] > 1): ?>
                <div class="pagination">
                    <?php
                    $baseUrl = 'users.php?' . http_build_query(array_filter([
                        'search' => $search,
                        'role' => $roleFilter,
                        'status' => $statusFilter
                    ]));
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseUrl; ?>&page=<?php echo $page - 1; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <i class="fas fa-chevron-left"></i> Previous
                        </span>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($usersData['pages'], $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $baseUrl; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $usersData['pages']): ?>
                        <a href="<?php echo $baseUrl; ?>&page=<?php echo $page + 1; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            Next <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Summary Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> User Statistics</h3>
                <p>Current user distribution and status overview</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <?php
                $stats = [
                    ['label' => 'Total Users', 'value' => $usersData['total'], 'icon' => 'fas fa-users', 'color' => '#555555'],
                    ['label' => 'Active Users', 'value' => count(array_filter($usersData['users'], fn($u) => $u['status'] === 'active')), 'icon' => 'fas fa-user-check', 'color' => '#2d5a2d'],
                    ['label' => 'Admin Roles', 'value' => count(array_filter($usersData['users'], fn($u) => in_array($u['role'], ['super_admin', 'admin']))), 'icon' => 'fas fa-user-shield', 'color' => '#CDC9C3'],
                    ['label' => 'This Page', 'value' => count($usersData['users']), 'icon' => 'fas fa-list', 'color' => '#D9E4DD']
                ];
                
                foreach ($stats as $stat):
                ?>
                    <div style="text-align: center; padding: 1.5rem; background: rgba(217, 228, 221, 0.2); border-radius: 8px;">
                        <div style="font-size: 2rem; color: <?php echo $stat['color']; ?>; margin-bottom: 1rem;">
                            <i class="<?php echo $stat['icon']; ?>"></i>
                        </div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #555555; margin-bottom: 0.5rem;">
                            <?php echo $stat['value']; ?>
                        </div>
                        <div style="color: #CDC9C3; font-weight: 600; font-size: 0.9rem;">
                            <?php echo $stat['label']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Hidden CSRF Token for AJAX -->
    <input type="hidden" id="csrf_token" value="<?php echo $csrfToken; ?>">

    <script>
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.dropdown-btn')) {
                const dropdowns = document.querySelectorAll('.dropdown-content');
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = 'none';
                });
            }
        });

        function toggleDropdown(button) {
            const dropdown = button.nextElementSibling;
            const isVisible = dropdown.style.display === 'block';
            
            // Close all dropdowns first
            document.querySelectorAll('.dropdown-content').forEach(d => {
                d.style.display = 'none';
            });
            
            // Toggle current dropdown
            dropdown.style.display = isVisible ? 'none' : 'block';
        }

        function updateUserStatus(userId, newStatus) {
            if (!confirm(`Are you sure you want to change this user's status to ${newStatus}?`)) {
                return;
            }
            
            makeAjaxRequest('update_status', userId, {new_status: newStatus});
        }

        function updateUserRole(userId, newRole) {
            if (!confirm(`Are you sure you want to change this user's role to ${newRole.replace('_', ' ')}?`)) {
                return;
            }
            
            makeAjaxRequest('update_role', userId, {new_role: newRole});
        }

        function deleteUser(userId, username) {
            if (!confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                return;
            }
            
            if (!confirm(`This will permanently delete the user account for "${username}". Are you absolutely sure?`)) {
                return;
            }
            
            makeAjaxRequest('delete_user', userId);
        }

        function makeAjaxRequest(ajaxAction, userId, extraData = {}) {
            const formData = new FormData();
            formData.append('action', 'ajax');
            formData.append('ajax_action', ajaxAction);
            formData.append('user_id', userId);
            formData.append('csrf_token', document.getElementById('csrf_token').value);
            
            // Add extra data
            Object.keys(extraData).forEach(key => {
                formData.append(key, extraData[key]);
            });
            
            fetch('users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Operation completed successfully.', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(data.error || 'An error occurred.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('A network error occurred.', 'error');
            });
        }

        function showAlert(message, type) {
            // Remove existing alerts
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                ${message}
            `;
            
            const container = document.querySelector('.container');
            const header = container.querySelector('.header');
            container.insertBefore(alert, header.nextSibling);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }

        // Auto-hide existing alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }, 8000);
            });
        });
    </script>
</body>
</html>