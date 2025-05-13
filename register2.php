<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isBarber()) {
        redirect('barber/dashboard.php');
    } else {
        redirect('customer/dashboard.php');
    }
}

$errors = [];
$formData = [
    'user_type' => 'customer',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'referral_code' => '',
];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData['user_type'] = sanitize($_POST['user_type'] ?? 'customer');
    $formData['first_name'] = sanitize($_POST['first_name'] ?? '');
    $formData['last_name'] = sanitize($_POST['last_name'] ?? '');
    $formData['email'] = sanitize($_POST['email'] ?? '');
    $formData['phone'] = sanitize($_POST['phone'] ?? '');
    $formData['address'] = sanitize($_POST['address'] ?? '');
    $formData['referral_code'] = sanitize($_POST['referral_code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate user type
    if (!in_array($formData['user_type'], ['customer', 'barber'])) {
        $errors['user_type'] = 'Invalid user type';
    }
    
    // Validate name
    if (empty($formData['first_name'])) {
        $errors['first_name'] = 'First name is required';
    }
    
    if (empty($formData['last_name'])) {
        $errors['last_name'] = 'Last name is required';
    }
    
    // Validate email
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email already exists
        $table = ($formData['user_type'] === 'customer') ? 'Customers' : 'Barbers';
        $stmt = $conn->prepare("SELECT Email FROM {$table} WHERE Email = ?");
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors['email'] = 'Email already in use';
        }
    }
    
    // Validate phone
    if (empty($formData['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^\d{10,11}$/', $formData['phone'])) {
        $errors['phone'] = 'Phone must be 10-11 digits';
    }
    
    // Validate address (only for customers)
    if ($formData['user_type'] === 'customer' && empty($formData['address'])) {
        $errors['address'] = 'Address is required';
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Validate referral code
    if ($formData['user_type'] === 'customer' && !empty($formData['referral_code'])) {
        $stmt = $conn->prepare("SELECT r.*, c.UserID as ReferrerID 
                               FROM referrals r 
                               JOIN customers c ON c.UserID = r.ReferrerID 
                               WHERE r.ReferralCode = ? AND r.ExpiresAt >= CURDATE()");
        $stmt->bind_param("s", $formData['referral_code']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors['referral_code'] = 'Invalid or expired referral code';
        } else {
            $referral_data = $result->fetch_assoc();
            $referral_customer_id = $referral_data['ReferrerID'];
        }
    }

    // Process if no errors
    if (empty($errors)) {
        $hashed_password = hashPassword($password);
        
        if ($formData['user_type'] === 'customer') {
            $sql = "INSERT INTO Customers (FirstName, LastName, Email, Phone, PassHash, Address, UsedReferralCode) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", 
                $formData['first_name'], 
                $formData['last_name'], 
                $formData['email'], 
                $formData['phone'], 
                $hashed_password, 
                $formData['address'],
                $formData['referral_code']
            );
        } else {
            $sql = "INSERT INTO Barbers (FirstName, LastName, Email, Phone, PassHash, Salary, HireDate) 
                    VALUES (?, ?, ?, ?, ?, 0, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", 
                $formData['first_name'], 
                $formData['last_name'], 
                $formData['email'], 
                $formData['phone'], 
                $hashed_password
            );
        }
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $formData['user_type'];
            $_SESSION['user_name'] = $formData['first_name'] . ' ' . $formData['last_name'];
            $_SESSION['user_email'] = $formData['email'];
            
            // Redirect to appropriate dashboard
            setFlashMessage('Registration successful! Welcome to Barberbook.', 'success');
            
            if ($formData['user_type'] === 'barber') {
                redirect('barber/dashboard.php');
            } else {
                redirect('customer/dashboard.php');
            }
        } else {
            $errors['db'] = 'Registration failed: ' . $conn->error;
        }
    }
}

// Define page title
$page_title = "Register";
include 'includes/header.php';
?>

<main>
    <section class="auth-section">
        <div class="container">
            <div class="auth-container registration">
                <div class="auth-header">
                    <h1>Create an Account</h1>
                    <p>Fill in your details to get started</p>
                </div>
                
                <?php if (!empty($errors['db'])): ?>
                    <div class="alert alert-error">
                        <?php echo $errors['db']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form" data-validate="true">
                    <div class="form-group">
                        <label>I want to register as:</label>
                        <div class="user-type-select">
                            <label class="user-type-option">
                                <input type="radio" name="user_type" value="customer" <?php echo ($formData['user_type'] === 'customer') ? 'checked' : ''; ?>>
                                <span class="user-type-label">
                                    <i class="fas fa-user"></i>
                                    <span>Customer</span>
                                </span>
                            </label>
                            <label class="user-type-option">
                                <input type="radio" name="user_type" value="barber" <?php echo ($formData['user_type'] === 'barber') ? 'checked' : ''; ?>>
                                <span class="user-type-label">
                                    <i class="fas fa-cut"></i>
                                    <span>Barber</span>
                                </span>
                            </label>
                        </div>
                        <?php if (!empty($errors['user_type'])): ?>
                            <div class="error-message"><?php echo $errors['user_type']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($formData['first_name']); ?>" required data-label="First name">
                            <?php if (!empty($errors['first_name'])): ?>
                                <div class="error-message"><?php echo $errors['first_name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($formData['last_name']); ?>" required data-label="Last name">
                            <?php if (!empty($errors['last_name'])): ?>
                                <div class="error-message"><?php echo $errors['last_name']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required data-label="Email">
                        <?php if (!empty($errors['email'])): ?>
                            <div class="error-message"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" required data-label="Phone number">
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="error-message"><?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group address-group" id="address-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($formData['address']); ?>" <?php echo ($formData['user_type'] === 'customer') ? 'required' : ''; ?> data-label="Address">
                        <?php if (!empty($errors['address'])): ?>
                            <div class="error-message"><?php echo $errors['address']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group referral-group" id="referral-group" style="display: <?php echo ($formData['user_type'] === 'customer') ? 'block' : 'none'; ?>">
                        <label for="referral_code">Referral Code</label>
                        <input type="text" id="referral_code" name="referral_code" value="<?php echo htmlspecialchars($formData['referral_code']); ?>" placeholder="Enter referral code if you have one">
                        <?php if (!empty($errors['referral_code'])): ?>
                            <div class="error-message"><?php echo $errors['referral_code']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-field">
                                <input type="password" id="password" name="password" required data-label="Password">
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <?php if (!empty($errors['password'])): ?>
                                <div class="error-message"><?php echo $errors['password']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" name="confirm_password" required data-label="Confirm password">
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <?php if (!empty($errors['confirm_password'])): ?>
                                <div class="error-message"><?php echo $errors['confirm_password']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="terms-checkbox">
                            <input class="terms-checkbox-input" type="checkbox" id="terms" name="terms" required>
                            <label class="terms-label" for="terms">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include 'includes/footer.php';
?>

<style>
/* Registration Form Specific Styles */
.auth-container.registration {
    max-width: 550px;
}

.form-row {
    display: flex;
    gap: var(--space-2);
    margin-bottom: 0;
}

.form-row .form-group {
    flex: 1;
}

.terms-checkbox {
    margin-bottom: 1em;
    display: flex;
    align-items: center;
    gap: 8px;
}

.terms-checkbox input {
    margin: 0;
    height: 20px;
}

.terms-label {
    margin: 0;
}

.auth-section {
    padding: var(--space-6) 0;
    min-height: calc(100vh - 80px - 80px);
    display: flex;
    align-items: center;
    margin-top: 80px;
}

.auth-container {
    max-width: 450px;
    margin: 0 auto;
    background-color: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: var(--space-4);
}

.auth-header {
    text-align: center;
    margin-bottom: var(--space-4);
}

.auth-header h1 {
    margin-bottom: var(--space-1);
}

.auth-header p {
    color: var(--color-text-light);
}

.auth-form {
    margin-bottom: var(--space-3);
}

.form-group {
    margin-bottom: var(--space-3);
}

.form-group label {
    display: block;
    margin-bottom: var(--space-1);
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px var(--space-2);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    font-family: var(--font-body);
    font-size: 1.6rem;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(26, 54, 93, 0.1);
}

.form-group input.is-invalid {
    border-color: var(--color-error);
}

.error-message {
    color: var(--color-error);
    font-size: 1.4rem;
    margin-top: 4px;
}

.password-field {
    position: relative;
}

.toggle-password {
    position: absolute;
    top: 50%;
    right: var(--space-2);
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--color-text-light);
    cursor: pointer;
}

.btn-block {
    width: 100%;
    padding: var(--space-2);
    font-size: 1.6rem;
}

.auth-footer {
    text-align: center;
    margin-top: var(--space-3);
    padding-top: var(--space-3);
    border-top: 1px solid var(--color-border);
}

.user-type-select {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.user-type-option {
    flex: 1;
    cursor: pointer;
}

.user-type-option input[type="radio"] {
    display: none;
}

.user-type-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.3s ease;
    background-color: #fff;
}

.user-type-label i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #4a5568;
    display: block;
}

.user-type-label span {
    font-weight: 500;
    color: #4a5568;
    font-size: 1.1rem;
}

.user-type-option input[type="radio"]:checked + .user-type-label {
    border-color: #4299e1;
    background-color: #ebf8ff;
}

.user-type-option input[type="radio"]:checked + .user-type-label i,
.user-type-option input[type="radio"]:checked + .user-type-label span {
    color: #4299e1;
}

.user-type-option:hover .user-type-label {
    border-color: #4299e1;
    background-color: #f7fafc;
    transform: translateY(-2px);
}

@media (max-width: 576px) {
    .auth-container {
        padding: var(--space-3);
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .user-type-select {
        flex-direction: column;
    }
    
    .user-type-label {
        padding: 1rem;
    }
    
    .user-type-label i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const passwordInput = this.parentElement.querySelector('input');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });
});

// Toggle address and referral fields based on user type
const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
const addressInput = document.getElementById('address');
const addressGroup = document.getElementById('address-group');
const referralGroup = document.getElementById('referral-group');

userTypeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'customer') {
            addressInput.setAttribute('required', '');
            addressGroup.style.display = 'block';
            referralGroup.style.display = 'block';
        } else {
            addressInput.removeAttribute('required');
            addressGroup.style.display = 'none';
            referralGroup.style.display = 'none';
        }
    });
});

// Initial check
if (document.querySelector('input[name="user_type"]:checked').value !== 'customer') {
    addressInput.removeAttribute('required');
    addressGroup.style.display = 'none';
    referralGroup.style.display = 'none';
}
</script>