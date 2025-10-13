<?php
// Presentation logic for user views

function renderUserTable($users) {
    if (empty($users)) {
        return '<div class="card text-center">
                    <h3>No hay usuarios registrados</h3>
                    <p class="mb-4">Comienza creando tu primer usuario del sistema.</p>
                    <a href="user_create.php" class="btn btn-primary">Crear Primer Usuario</a>
                </div>';
    }
    
    $html = '<div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de Alta</th>
                                <th>Operaciones</th>
                            </tr>
                        </thead>
                        <tbody>';
                
    foreach ($users as $user) {
        $html .= '<tr>
                    <td><span class="font-medium">#' . htmlspecialchars($user['id']) . '</span></td>
                    <td><span class="font-semibold">' . htmlspecialchars($user['nombre']) . '</span></td>
                    <td>' . htmlspecialchars($user['email']) . '</td>
                    <td><span class="font-medium">' . ucfirst(htmlspecialchars($user['rol'])) . '</span></td>
                    <td>' . htmlspecialchars($user['fecha_alta']) . '</td>
                    <td>
                        <div class="actions">
                            <a href="user_info.php?id=' . urlencode($user['id']) . '" class="action-view">Ver</a>
                            <a href="user_edit.php?id=' . urlencode($user['id']) . '" class="action-edit">Editar</a>
                            <a href="user_delete.php?id=' . urlencode($user['id']) . '" class="action-delete">Eliminar</a>
                        </div>
                    </td>
                  </tr>';
    }
    
    $html .= '</tbody>
              </table>
              </div>
              <div class="card-footer">
                  <a href="user_create.php" class="btn btn-primary">
                      <i class="fas fa-plus"></i> Añadir usuario
                  </a>
              </div>
              </div>';
    return $html;
}

function renderUserInfo($user) {
    return '<div class="card page-transition">
                <h2>Información del Usuario</h2>
                <div class="table-container">
                    <table>
                        <tbody>
                            <tr><th width="150">ID</th><td><span class="font-medium">#' . htmlspecialchars($user['id']) . '</span></td></tr>
                            <tr><th>Nombre</th><td><span class="font-semibold">' . htmlspecialchars($user['nombre']) . '</span></td></tr>
                            <tr><th>Email</th><td>' . htmlspecialchars($user['email']) . '</td></tr>
                            <tr><th>Rol</th><td><span class="font-medium">' . ucfirst(htmlspecialchars($user['rol'])) . '</span></td></tr>
                            <tr><th>Fecha de Alta</th><td>' . htmlspecialchars($user['fecha_alta']) . '</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="actions mt-6">
                    <a href="user_edit.php?id=' . urlencode($user['id']) . '" class="btn btn-primary">Editar Usuario</a>
                    <a href="user_delete.php?id=' . urlencode($user['id']) . '" class="btn btn-danger">Eliminar Usuario</a>
                    <a href="user_index.php" class="btn btn-secondary">Volver a la Lista</a>
                </div>
            </div>';
}

function renderEditForm($user) {
    return '<div class="card page-transition">
                <h2>Editar Usuario</h2>
                <form action="user_edit.php?id=' . urlencode($user['id']) . '" method="post">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" value="' . htmlspecialchars($user['nombre']) . '" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" value="' . htmlspecialchars($user['email']) . '" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol del Usuario</label>
                        <select id="rol" name="rol" required>
                            <option value="admin"' . ($user['rol'] === 'admin' ? ' selected' : '') . '>Administrador</option>
                            <option value="editor"' . ($user['rol'] === 'editor' ? ' selected' : '') . '>Editor</option>
                            <option value="viewer"' . ($user['rol'] === 'viewer' ? ' selected' : '') . '>Visualizador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_alta">Fecha de Alta</label>
                        <input type="text" 
                               id="fecha_alta" 
                               name="fecha_alta_display" 
                               value="' . htmlspecialchars($user['fecha_alta']) . '" 
                               disabled 
                               title="Campo no editable"
                               style="background: var(--neutral-100); color: var(--neutral-500);">
                        <input type="hidden" name="fecha_alta" value="' . htmlspecialchars($user['fecha_alta']) . '">
                    </div>
                    
                    <div class="actions mt-6">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="user_index.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>';
}

function renderDeleteConfirmation($userId) {
    return '<div class="card page-transition text-center">
                <h2>Confirmar Eliminación</h2>
                <p class="mb-6">¿Estás seguro de que deseas eliminar el usuario con ID <strong>#' . htmlspecialchars($userId) . '</strong>?</p>
                <p class="mb-6 text-center" style="color: var(--error); font-weight: 500;">Esta acción no se puede deshacer.</p>
                <form method="POST" action="./user_delete.php">
                    <input type="hidden" name="id" value="' . htmlspecialchars($userId) . '">
                    <div class="actions">
                        <button type="submit" name="confirm" value="yes" class="btn btn-danger">Sí, Eliminar</button>
                        <button type="submit" name="confirm" value="no" class="btn btn-secondary">Cancelar</button>
                    </div>
                </form>
            </div>';
}

function renderDashboardStats($stats) {
    $html = '<div class="card page-transition">
                <h2>Estadísticas Generales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number">' . $stats['userCount'] . '</span>
                        <span class="stat-label">Total Usuarios</span>
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
                    <h2>Usuarios Recientes</h2>
                    <p class="mb-4">No hay usuarios registrados en el sistema.</p>
                    <a href="pages/users/user_create.php" class="btn btn-primary">Crear el Primer Usuario</a>
                </div>';
    }
    
    $html = '<div class="card">
                <h2>Usuarios Recientes</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de Alta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>';
                 
    foreach ($recentUsers as $user) {
        $html .= '<tr>
                    <td><span class="font-medium">#' . htmlspecialchars($user['id']) . '</span></td>
                    <td><span class="font-semibold">' . htmlspecialchars($user['nombre']) . '</span></td>
                    <td>' . htmlspecialchars($user['email']) . '</td>
                    <td><span class="font-medium">' . ucfirst(htmlspecialchars($user['rol'])) . '</span></td>
                    <td>' . htmlspecialchars($user['fecha_alta']) . '</td>
                    <td><a href="pages/users/user_info.php?id=' . urlencode($user['id']) . '" class="action-view">Ver Detalles</a></td>
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
