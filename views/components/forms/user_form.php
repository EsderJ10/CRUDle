<?php
// User form component

require_once getPath('lib/helpers/utils.php');
require_once getPath('config/config.php');

// Get form data if it exists (for repopulating on error)
$formData = $formData ?? [
    'nombre' => '',
    'email' => '',
    'rol' => ''
];

$isEdit = isset($user) && !empty($user);
$action = $isEdit ? 'user_edit.php?id=' . urlencode($user['id']) : 'user_create.php';
$buttonText = $isEdit ? 'Actualizar Usuario' : 'Crear Usuario';
?>

<div class="card">
    <form method="post" action="<?php echo $action; ?>" class="page-transition" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nombre Completo</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   placeholder="Ingrese el nombre completo" 
                   value="<?php echo htmlspecialchars($isEdit ? $user['nombre'] : $formData['nombre']); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   placeholder="usuario@ejemplo.com" 
                   value="<?php echo htmlspecialchars($isEdit ? $user['email'] : $formData['email']); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="role">Rol del Usuario</label>
            <select id="role" name="role" required>
                <option value="">Seleccione un rol</option>
                <?php 
                $roles = ROLES;
                $currentRole = $isEdit ? $user['rol'] : $formData['rol'];
                foreach ($roles as $role): 
                ?>
                    <option value="<?php echo htmlspecialchars($role); ?>" 
                            <?php echo ($currentRole === $role) ? 'selected' : ''; ?>>
                        <?php echo ucfirst(htmlspecialchars($role)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="avatar">Avatar del Usuario</label>
            <?php if ($isEdit && !empty($user['avatar'])): ?>
                <div class="current-avatar mb-3">
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                         alt="Avatar actual" 
                         class="avatar" style="width: 80px; height: 80px; border: 2px solid var(--neutral-200);">
                    <p class="text-small text-neutral-600 mt-1">Avatar actual</p>
                </div>
            <?php endif; ?>
            <input type="file" 
                   id="avatar" 
                   name="avatar" 
                   accept="image/jpeg,image/jpg,image/png,image/gif">
            <small class="text-neutral-600">
                Opcional. Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.
                <?php if ($isEdit): ?>Subir una nueva imagen reemplazará la actual.<?php endif; ?>
            </small>
        </div>
        
        <?php if ($isEdit): ?>
        <div class="form-group">
            <label for="fecha_alta">Fecha de Alta</label>
            <input type="text" 
                   id="fecha_alta" 
                   name="fecha_alta_display" 
                   value="<?php echo htmlspecialchars($user['fecha_alta']); ?>" 
                   disabled 
                   title="Campo no editable"
                   style="background: var(--neutral-100); color: var(--neutral-500);">
            <input type="hidden" 
                   name="fecha_alta" 
                   value="<?php echo htmlspecialchars($user['fecha_alta']); ?>">
        </div>
        <?php endif; ?>
        
        <div class="actions mt-6">
            <button type="submit" class="btn btn-primary"><?php echo $buttonText; ?></button>
            <a href="user_index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
