<?php
// Quick database setup script
chdir(dirname(__FILE__) . '/../');

// Load config
require_once('config.php');

// Initialize database
global $DB, $CFG;

echo "Initializing Moodle database... Please wait.\n";

// Set up the database
require_once($CFG->libdir . '/db/upgradelib.php');
require_once($CFG->libdir . '/db/install.php');

// Run the main installation
try {
    install_core_databases();
    echo "Database tables created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Create admin user
$adminuser = (object)[
    'username' => 'admin',
    'password' => generate_password_hash('admin123'),
    'firstname' => 'Admin',
    'lastname' => 'User',
    'email' => 'admin@localhost.local',
    'auth' => 'manual',
    'confirmed' => 1,
    'mnethostid' => $CFG->mnethost,
];

try {
    $admin = user_create_user($adminuser);
    assign_capability('moodle/role:manage', CAP_ALLOW, 3, 1);
    echo "Admin user created successfully!\n";
} catch (Exception $e) {
    echo "Warning: " . $e->getMessage() . "\n";
}

echo "Setup complete!\n";
?>
