<?php
/*
 * Form to create or edit a user.
 * Uses $user variable (optional) for editing and $formData to repopulate data in case of error.
 * Author: José Antonio Cortés Ferre
 */

require_once getPath('config/init.php');

$formData = $formData ?? [
    'name' => '',
    'email' => '',
    'role' => ''
];

$isEdit = isset($user) && !empty($user) && isset($user['id']);
$action = $isEdit ? 'user_edit.php?id=' . urlencode($user['id']) : 'user_create.php';
$buttonText = $isEdit ? 'Update User' : 'Invite User';
?>

<div class="card">
    <form method="post" action="<?php echo $action; ?>" class="page-transition" enctype="multipart/form-data">
        <?php echo CSRF::renderInput(); ?>
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   placeholder="Enter full name" 
                   value="<?php echo htmlspecialchars($isEdit ? $user['name'] : $formData['name']); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   placeholder="user@example.com" 
                   value="<?php echo htmlspecialchars($isEdit ? $user['email'] : $formData['email']); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required 
                    data-current-role="<?php echo htmlspecialchars($isEdit ? $user['role'] : ''); ?>"
                    data-is-self="<?php echo ($isEdit && $user['id'] == Session::get('user_id')) ? 'true' : 'false'; ?>"
                    <?php echo ($isEdit && $user['id'] == Session::get('user_id')) ? 'disabled' : ''; ?>>
                <option value="">Select a role</option>
                <?php 
                // Use availableRoles if provided (from controller), otherwise get all roles
                if (isset($availableRoles)) {
                    $rolesToDisplay = $availableRoles;
                } else {
                    $rolesToDisplay = [];
                    foreach (Role::cases() as $role) {
                        $rolesToDisplay[$role->value] = $role->label();
                    }
                }
                
                $currentRole = $isEdit ? $user['role'] : $formData['role'];
                
                foreach ($rolesToDisplay as $roleValue => $roleLabel): ?>
                    <option value="<?php echo htmlspecialchars($roleValue); ?>" <?php echo ($currentRole === $roleValue) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($roleLabel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($isEdit && $user['id'] == Session::get('user_id')): ?>
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                <small class="text-muted"><i class="fas fa-lock"></i> For security reasons, you cannot change your own role.</small>
            <?php endif; ?>
        </div>
        
        <?php if ($isEdit): ?>
            <div class="form-group">
                <label for="avatar">Avatar</label>
                <?php if ($isEdit && !empty($user['avatar'])): ?>
                    <div class="current-avatar mb-3">
                        <div class="avatar-container">
                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                             alt="Current avatar" 
                             class="avatar avatar-medium">
                        </div>
                            <div class="avatar-actions mt-3">
                            <label class="checkbox-container">
                                <input type="checkbox" 
                                       id="remove_avatar" 
                                       name="remove_avatar" 
                                       value="1">
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Remove current avatar</span>
                        </label>
                        <div id="removeAvatarWarning" class="avatar-warning" style="display: none;">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                The avatar will be permanently deleted when saving the changes.
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="avatar-upload-section" id="avatarUploadSection">
                <label for="avatar" class="custom-file-upload" id="customFileUpload">
                    <span class="file-icon fas fa-upload"></span>
                    <span class="file-text">
                        <span class="file-text-main" id="fileTextMain">Select file</span>
                        <span class="file-text-sub" id="fileTextSub">or drag and drop here</span>
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
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <small class="text-neutral-600">
                    Allowed formats: JPG, PNG, GIF. Maximum size: 2MB.
                    <?php if ($isEdit): ?>Uploading a new image will replace the current one.<?php endif; ?>
                </small>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($isEdit): ?>
        <div class="form-group">
            <label for="fecha_alta">Date of Joining</label>
            <input type="text" 
                   id="fecha_alta" 
                   name="fecha_alta_display" 
                   value="<?php echo htmlspecialchars($user['fecha_alta']); ?>" 
                   disabled 
                   title="Field not editable"
                   class="disabled-input">
            <input type="hidden" 
                   name="fecha_alta" 
                   value="<?php echo htmlspecialchars($user['fecha_alta']); ?>">
        </div>
        <?php endif; ?>
        
        <div class="actions mt-6">
            <button type="submit" class="btn btn-primary"><?php echo $buttonText; ?></button>
            <a href="user_index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php if ($isEdit): ?>
    <script src="<?php echo getWebPath('assets/js/user-form.js'); ?>" defer></script>
<?php endif; ?>
