<div class="form-group">
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
</div>

<div class="form-group">
    <label for="email">Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
</div>

<div class="form-group">
    <label for="phone_num">Phone Number:</label>
    <input type="text" name="phone_num" value="<?= htmlspecialchars($user['phone_num']) ?>" required>
</div>

<div class="form-group">
    <label for="address_line">Address Line:</label>
    <input type="text" name="address_line" value="<?= htmlspecialchars($address_line) ?>" required>
</div>

<div class="form-group">
    <label for="city">City:</label>
    <select name="city" required>
        <option value="" disabled <?= $city === '' ? 'selected' : '' ?>>-- Select City --</option>
        <?php foreach ($states as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>" <?= $city === $s ? 'selected' : '' ?>>
                <?= htmlspecialchars($s) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="postcode">Postcode:</label>
    <input type="text" name="postcode" value="<?= htmlspecialchars($postcode) ?>" required>
</div>

<div class="form-group">
    <label for="password">New Password:</label>
    <input type="password" name="password" placeholder="New Password (optional)">
</div>

<div class="form-group">
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" placeholder="Confirm New Password">
</div>

<div class="form-group">
    <label class="upload">
        Click to Upload New Profile Image:
        <input type="file" name="profile_image" accept="image/*" hidden id="profile_image_input"><br><br>
        <img src="<?= htmlspecialchars($profileImageSrc ?? ($user['profile_image'] ?: '../assets/image/logo/default.png')) ?>" alt="Preview" width="150" id="profile_image_preview" data-profile-preview>
    </label>
</div>
