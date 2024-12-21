<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already registered";
    }
    
    if (empty($errors)) {
        $user_id = generateUserId();
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "
            INSERT INTO users (user_id, name, email, password)
            VALUES ('$user_id', '$name', '$email', '$hashed_password')
        ";
        
        if ($conn->query($query)) {
            $_SESSION['user_id'] = $user_id;
            redirect('index.php');
        } else {
            $errors[] = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Chat Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Register</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Full Name
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                    Confirm Password
                </label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       required 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600">
                Register
            </button>
            
            <p class="mt-4 text-center text-gray-600">
                Already have an account? 
                <a href="login.php" class="text-blue-500 hover:underline">Login</a>
            </p>
        </form>
    </div>
</body>
</html>
