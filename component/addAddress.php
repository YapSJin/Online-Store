<link rel="stylesheet" href="../assets/css/address.css">

<div class="address-actions">
  <?php include "../component/backButton.php"; ?>
  <button class="btn-add">+ Add Address</button>
</div>


<div id="addForm" class="form-container">
  <h3 id="formTitle">Add New Address</h3>
        <form method="post" id="addressForm">
            <input type="hidden" name="address_id" id="address_id" value="">

            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" id="full_name" value="" required>

            <label for="address_line">Address Line:</label>
            <input type="text" name="address_line" id="address_line" value="" required>

            <label for="city">State:</label>
                <select name="city" id="city" required>
                    <option value="">Select State</option>
                        <?php
                            $states = ["Perlis", "Kedah", "Kelantan", "Terrengganu", "Pahang", "Johor", "Melaka", "Negeri Sembilan", "Putrajaya", "Selangor", "Perak", "Pulau Pinang", "Sarawak", "Sabah"];
                            foreach ($states as $s) {
                                $selected = (isset($_POST['city']) && $_POST['city'] === $s) ? "selected" : "";
                                echo "<option value=\"$s\" $selected>$s</option>";
                            }
                        ?>
                </select>

            <label for="postcode">Postcode:</label>
            <input type="text" name="postcode" id="postcode" value="" required inputmode="numeric" maxlength="5" pattern="[0-9]{5}" title="Postcode must be exactly 5 digits">

            <label for="phone">Phone:</label>
            <input type="tel" name="phone" id="phone" value="+60" required inputmode="tel" maxlength="13" placeholder="+60123456789">

            <button type="submit" id="formSubmit" name="add_address">Add Address</button>
        </form>
    </div>

