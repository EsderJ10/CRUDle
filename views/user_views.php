<?php
// Presentation logic for user views

function renderUserTable($users) {
    if (empty($users)) {
        return '<div class="card text-center">
                    <h3>No users found</h3>
                    <p class="mb-4">Start by creating your first system user.</p>
                    ' . (Permissions::checkCurrent(Permissions::USER_CREATE) ? '<a href="user_create.php" class="btn btn-primary">Create First User</a>' : '') . '
                </div>';
    }
    
    $html = '<div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
                
    foreach ($users as $user) {
        $avatarSrc = !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : getDefaultAvatar();
        $status = $user['status'] ?? 'active';
        $statusBadge = '';
        
        if ($status === 'active') {
            $statusBadge = '<span class="badge badge-success">Active</span>';
        } elseif ($status === 'pending') {
            $statusBadge = '<span class="badge badge-warning">Pending</span>';
        } else {
            $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
        }

        $actions = '<a href="user_info.php?id=' . urlencode($user['id']) . '" class="action-view" title="View"><i class="fas fa-eye"></i></a>';
        
        if (Permissions::checkCurrent(Permissions::USER_UPDATE)) {
            $actions .= ' <a href="user_edit.php?id=' . urlencode($user['id']) . '" class="action-edit" title="Edit"><i class="fas fa-edit"></i></a>';
        }
        
        if (Permissions::checkCurrent(Permissions::USER_DELETE)) {
            $actions .= ' <a href="user_delete.php?id=' . urlencode($user['id']) . '" class="action-delete" title="Delete"><i class="fas fa-trash"></i></a>';
        }

        if ($status === 'pending' && Permissions::checkCurrent(Permissions::USER_UPDATE)) {
            $actions .= ' <a href="user_resend_invite.php?id=' . urlencode($user['id']) . '" class="action-resend" title="Resend Invitation"><i class="fas fa-paper-plane"></i></a>';
        }

        $html .= '<tr>
                    <td data-label="Avatar">
                        <img src="' . $avatarSrc . '" 
                             alt="Avatar of ' . htmlspecialchars($user['name']) . '" 
                             class="avatar avatar-small"
                             onerror="this.src=\'' . getDefaultAvatar() . '\'">
                    </td>
                    <td data-label="ID"><span class="font-medium">#' . htmlspecialchars($user['id']) . '</span></td>
                    <td data-label="Name"><span class="font-semibold">' . htmlspecialchars($user['name']) . '</span></td>
                    <td data-label="Email">' . htmlspecialchars($user['email']) . '</td>
                    <td data-label="Role"><span class="font-medium">' . ucfirst(htmlspecialchars($user['role'])) . '</span></td>
                    <td data-label="Status">' . $statusBadge . '</td>
                    <td data-label="Date">' . htmlspecialchars($user['fecha_alta']) . '</td>
                    <td data-label="Actions">
                        <div class="actions">
                            ' . $actions . '
                        </div>
                    </td>
                  </tr>';
    }
    
    $html .= '</tbody>
              </table>
              </div>';
              
    if (Permissions::checkCurrent(Permissions::USER_CREATE)) {
        $html .= '<div class="card-footer">
                      <a href="user_create.php" class="btn btn-primary">
                          <i class="fas fa-plus"></i> Add User
                      </a>
                  </div>';
    }
    
    $html .= '</div>';
    return $html;
}

function renderUserInfo($user) {
    $avatarSrc = !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : getDefaultAvatar();
    
    // Prepare buttons logic outside the string to keep syntax clean
    $editButton = '';
    if (Permissions::checkCurrent(Permissions::USER_UPDATE)) {
        $editButton = '<a href="user_edit.php?id=' . urlencode($user['id']) . '" class="btn btn-primary">Edit User</a>';
    }

    $deleteButton = '';
    if (Permissions::checkCurrent(Permissions::USER_DELETE)) {
        $deleteButton = '<a href="user_delete.php?id=' . urlencode($user['id']) . '" class="btn btn-danger">Delete User</a>';
    }

    return '<div class="card page-transition">
                <h2>User Information</h2>
                <div class="user-info-layout">
                    <div>
                        <img src="' . $avatarSrc . '" 
                             alt="Avatar of ' . htmlspecialchars($user['name']) . '" 
                             class="avatar avatar-large"
                             onerror="this.src=\'' . getDefaultAvatar() . '\'">
                    </div>
                    <div class="user-info-content">
                        <div class="table-container">
                            <table>
                                <tbody>
                                    <tr><th width="150">ID</th><td><span class="font-medium">#' . htmlspecialchars($user['id']) . '</span></td></tr>
                                    <tr><th>Name</th><td><span class="font-semibold">' . htmlspecialchars($user['name']) . '</span></td></tr>
                                    <tr><th>Email</th><td>' . htmlspecialchars($user['email']) . '</td></tr>
                                    <tr><th>Role</th><td><span class="font-medium">' . ucfirst(htmlspecialchars($user['role'])) . '</span></td></tr>
                                    <tr><th>Date Added</th><td>' . htmlspecialchars($user['fecha_alta']) . '</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="actions mt-6">
                    ' . $editButton . '
                    ' . $deleteButton . '
                    <a href="user_index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>';
}

function renderEditForm($user) {
    // Handle the logic for the hidden input regarding permissions outside the return string
    $roleHiddenInput = '';
    if (!Permissions::checkCurrent(Permissions::USER_DELETE)) {
        $roleHiddenInput = '<input type="hidden" name="role" value="' . htmlspecialchars($user['role']) . '">';
    }

    return '<div class="card page-transition">
                <h2>Edit User</h2>
                <form action="user_edit.php?id=' . urlencode($user['id']) . '" method="post">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="' . htmlspecialchars($user['name']) . '" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="' . htmlspecialchars($user['email']) . '" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">User Role</label>
                        <select id="role" name="role" required ' . (!Permissions::checkCurrent(Permissions::USER_DELETE) ? 'disabled' : '') . '>
                            <option value="admin"' . ($user['role'] === 'admin' ? ' selected' : '') . '>Administrator</option>
                            <option value="editor"' . ($user['role'] === 'editor' ? ' selected' : '') . '>Editor</option>
                            <option value="viewer"' . ($user['role'] === 'viewer' ? ' selected' : '') . '>Viewer</option>
                        </select>
                        ' . $roleHiddenInput . '
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_alta">Date Added</label>
                        <input type="text" 
                               id="fecha_alta" 
                               name="fecha_alta_display" 
                               value="' . htmlspecialchars($user['fecha_alta']) . '" 
                               disabled 
                               title="Field not editable"
                               class="disabled-input">
                        <input type="hidden" name="fecha_alta" value="' . htmlspecialchars($user['fecha_alta']) . '">
                    </div>
                    
                    <div class="actions mt-6">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="user_index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>';
}

function renderDeleteConfirmation($userId, $csrfToken) {
    return '<div class="card page-transition text-center">
                <h2>Confirm Deletion</h2>
                <p class="mb-6">Are you sure you want to delete user ID <strong>#' . htmlspecialchars($userId) . '</strong>?</p>
                <p class="mb-6 text-center warning-text">This action cannot be undone.</p>
                <form method="POST" action="./user_delete.php">
                    <input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">
                    <input type="hidden" name="id" value="' . htmlspecialchars($userId) . '">
                    <div class="actions">
                        <button type="submit" name="confirm" value="yes" class="btn btn-danger">Yes, Delete</button>
                        <button type="submit" name="confirm" value="no" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>';
}

function renderDashboardStats($stats) {
    $html = '<div class="card page-transition">
                <h2>General Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number">' . $stats['userCount'] . '</span>
                        <span class="stat-label">Total Users</span>
                    </div>';
                    
    $i = 0;
    foreach ($stats['usersByRole'] as $role => $count) {
        $html .= '<div class="stat-card">
                    <span class="stat-number">' . $count . '</span>
                    <span class="stat-label">' . ucfirst($role) . '</span>
                  </div>';
    }
    
    $html .= '</div>
             </div>';
    return $html;
}

function renderRecentUsers($recentUsers) {
    if (empty($recentUsers)) {
        return '<div class="card text-center">
                    <h2>Recent Users</h2>
                    <p class="mb-4">No users registered in the system.</p>
                    <a href="pages/users/user_create.php" class="btn btn-primary">Create First User</a>
                </div>';
    }
    
    $html = '<div class="card">
                <h2>Recent Users</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
                 
    foreach ($recentUsers as $user) {
        $html .= '<tr>
                    <td data-label="ID"><span class="font-medium">#' . htmlspecialchars($user['id']) . '</span></td>
                    <td data-label="Name"><span class="font-semibold">' . htmlspecialchars($user['name']) . '</span></td>
                    <td data-label="Email">' . htmlspecialchars($user['email']) . '</td>
                    <td data-label="Role"><span class="font-medium">' . ucfirst(htmlspecialchars($user['role'])) . '</span></td>
                    <td data-label="Date">' . htmlspecialchars($user['fecha_alta']) . '</td>
                    <td data-label="Actions"><a href="pages/users/user_info.php?id=' . urlencode($user['id']) . '" class="action-view">View Details</a></td>
                  </tr>';
    }
    
    $html .= '</tbody>
              </table>
              </div>
             </div>';
    return $html;
}

function renderMessage($message, $type = 'info') {
    $class = 'message message-' . $type;
    return '<div class="' . $class . '">' . htmlspecialchars($message) . '</div>';
}
?>