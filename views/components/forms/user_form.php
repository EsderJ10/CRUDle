<?php
/*
 * Formulario para crear o editar un usuario.
 * Utiliza variables $user (opcional) para edición y $formData para repoblar datos en caso de error.
 * Autor: José Antonio Cortés Ferre
 */

require_once getPath('lib/helpers/utils.php');
require_once getPath('config/config.php');

$formData = $formData ?? [
    'nombre' => '',
    'email' => '',
    'rol' => ''
];

$isEdit = isset($user) && !empty($user) && isset($user['id']);
$action = $isEdit ? 'user_edit.php?id=' . urlencode($user['id']) : 'user_create.php';
$buttonText = $isEdit ? 'Actualizar Usuario' : 'Crear Usuario';
?>

<div class="card">
    <form method="post" action="<?php echo $action; ?>" class="page-transition" enctype="multipart/form-data">
        <?php echo CSRF::renderInput(); ?>
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
            <label for="password">Contraseña <?php echo $isEdit ? '<small class="text-neutral-600 font-normal">(Dejar en blanco para mantener la actual)</small>' : ''; ?></label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   placeholder="<?php echo $isEdit ? '••••••••' : 'Ingrese una contraseña'; ?>" 
                   <?php echo $isEdit ? '' : 'required'; ?>>
        </div>
        
        <div class="form-group">
            <label for="role">Rol del Usuario</label>
            <select id="role" name="role" required>
                <option value="">Seleccione un rol</option>
                <?php 
                $roles = getRoles();
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
                    <div class="avatar-container">
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                             alt="Avatar actual" 
                             class="avatar avatar-medium">
                    </div>
                    <div class="avatar-actions mt-3">
                        <label class="checkbox-container">
                            <input type="checkbox" 
                                   id="remove_avatar" 
                                   name="remove_avatar" 
                                   value="1">
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Eliminar avatar actual</span>
                        </label>
                        <div id="removeAvatarWarning" class="avatar-warning" style="display: none;">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                El avatar será eliminado permanentemente al guardar los cambios.
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="avatar-upload-section" id="avatarUploadSection">
                <label for="avatar" class="custom-file-upload" id="customFileUpload">
                    <span class="file-icon fas fa-upload"></span>
                    <span class="file-text">
                        <span class="file-text-main" id="fileTextMain">Seleccionar archivo</span>
                        <span class="file-text-sub" id="fileTextSub">o arrastra y suelta aquí</span>
                    </span>
                </label>
                <input type="file" 
                       id="avatar" 
                       name="avatar" 
                       accept="image/jpeg,image/jpg,image/png,image/gif">
                <div class="file-preview" id="filePreview">
                    <img src="" alt="Preview" class="file-preview-image" id="filePreviewImage">
                    <div class="file-preview-info">
                        <div class="file-preview-name" id="filePreviewName"></div>
                        <div class="file-preview-size" id="filePreviewSize"></div>
                    </div>
                    <button type="button" class="file-preview-remove" id="filePreviewRemove">
                        <i class="fas fa-times"></i> Quitar
                    </button>
                </div>
                <small class="text-neutral-600">
                    Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.
                    <?php if ($isEdit): ?>Subir una nueva imagen reemplazará la actual.<?php endif; ?>
                </small>
            </div>
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
                   class="disabled-input">
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

<?php if ($isEdit): ?>
    <script src="<?php echo getWebPath('assets/js/user-form.js'); ?>" defer></script>
<?php endif; ?>
