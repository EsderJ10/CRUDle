<?php
/*
 * Formulario para invitar a un nuevo usuario.
 * Solo solicita Nombre, Email y Rol.
 */

require_once getPath('lib/helpers/utils.php');
require_once getPath('config/config.php');

$formData = $formData ?? [
    'nombre' => '',
    'email' => '',
    'rol' => ''
];
?>

<div class="card">
    <form method="post" action="user_create.php" class="page-transition">
        <?php echo CSRF::renderInput(); ?>
        
        <div class="form-group">
            <label for="name">Nombre Completo</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   placeholder="Ingrese el nombre completo" 
                   value="<?php echo htmlspecialchars($formData['nombre']); ?>" 
                   required>
            <small class="text-neutral-600">El nombre se usará para personalizar el correo de invitación.</small>
        </div>
        
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   placeholder="usuario@ejemplo.com" 
                   value="<?php echo htmlspecialchars($formData['email']); ?>" 
                   required>
            <small class="text-neutral-600">Se enviará un enlace de invitación a esta dirección.</small>
        </div>
        
        <div class="form-group">
            <label for="role">Rol del Usuario</label>
            <select id="role" name="role" required>
                <option value="">Seleccione un rol</option>
                <?php 
                $roles = getRoles();
                $currentRole = $formData['rol'];
                foreach ($roles as $role): 
                ?>
                    <option value="<?php echo htmlspecialchars($role); ?>" 
                            <?php echo ($currentRole === $role) ? 'selected' : ''; ?>>
                        <?php echo ucfirst(htmlspecialchars($role)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="actions mt-6">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Enviar Invitación
            </button>
            <a href="user_index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
