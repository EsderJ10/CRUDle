<?php
/*
 * Formulario para crear o editar un usuario.
 * Utiliza variables $user (opcional) para edición y $formData para repoblar datos en caso de error.
 * Autor: José Antonio Cortés Ferre
 */

require_once getPath('lib/helpers/utils.php');
require_once getPath('config/config.php');

$formData = $formData ?? [
    'name' => '',
    'email' => '',
    'role' => ''
];

$isEdit = isset($user) && !empty($user) && isset($user['id']);
$action = $isEdit ? 'user_edit.php?id=' . urlencode($user['id']) : 'user_create.php';
$buttonText = $isEdit ? 'Actualizar Usuario' : 'Invitar Usuario';
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
                   value="<?php echo htmlspecialchars($isEdit ? $user['name'] : $formData['name']); ?>" 
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
                <option value="">Seleccione un role</option>
                <?php 
                $roles = getRoles();
                $currentRole = $isEdit ? $user['role'] : $formData['role'];
                foreach ($roles as $role): 
                ?>
                    <option value="<?php echo htmlspecialchars($role); ?>" 
                            <?php echo ($currentRole === $role) ? 'selected' : ''; ?>>
                        <?php echo ucfirst(htmlspecialchars($role)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
