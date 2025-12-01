<?php
/**
 * Reusable password fields component.
 * 
 * Variables expected:
 * - $password (optional): Value for password field
 * - $confirm_password (optional): Value for confirm password field
 */

$password = $password ?? '';
$confirm_password = $confirm_password ?? '';
?>
<div class="form-group">
    <label for="password">New Password</label>
    <div class="password-input-wrapper">
        <input type="password" id="password" name="password" required 
               minlength="8" placeholder="Minimum 8 characters"
               value="<?php echo htmlspecialchars($password); ?>">
        <button type="button" class="toggle-password" tabindex="-1" aria-label="Show password">
            <i class="far fa-eye"></i>
        </button>
    </div>
</div>

<div class="form-group">
    <label for="confirm_password">Confirm Password</label>
    <div class="password-input-wrapper">
        <input type="password" id="confirm_password" name="confirm_password" required 
               minlength="8" placeholder="Repeat password"
               value="<?php echo htmlspecialchars($confirm_password); ?>">
    </div>
</div>
