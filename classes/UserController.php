<?php
class UserController {
    private $userModel;
    private $db;
    private $config;

    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
        $this->userModel = new User($db);
    }

    public function index() {
        $users = $this->userModel->getAllUsers();
        $appName = $this->config['name'];
        include 'views/users/list.php';
    }

    public function create() {
        $appName = $this->config['name'];
        include 'views/users/create.php';
    }

    public function store() {
        $data = $_POST;
        
        // Validate input
        $errors = $this->validateUserData($data);
        if (!empty($errors)) {
            $error = implode(', ', $errors);
            $appName = $this->config['name'];
            include 'views/users/create.php';
            return;
        }

        try {
            $userId = $this->userModel->createUser($data);
            header('Location: index.php?page=users&message=User created successfully');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $appName = $this->config['name'];
            include 'views/users/create.php';
        }
    }

    public function edit($id) {
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            throw new Exception("User not found");
        }
        $appName = $this->config['name'];
        include 'views/users/edit.php';
    }

    public function update($id) {
        $data = $_POST;
        
        // Validate input
        $errors = $this->validateUserData($data, $id);
        if (!empty($errors)) {
            $error = implode(', ', $errors);
            $user = $this->userModel->getUserById($id);
            $appName = $this->config['name'];
            include 'views/users/edit.php';
            return;
        }

        try {
            $this->userModel->updateUser($id, $data);
            header('Location: index.php?page=users&message=User updated successfully');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $user = $this->userModel->getUserById($id);
            $appName = $this->config['name'];
            include 'views/users/edit.php';
        }
    }

    public function delete($id) {
        try {
            $this->userModel->deleteUser($id);
            header('Location: index.php?page=users&message=User deleted successfully');
            exit;
        } catch (Exception $e) {
            header('Location: index.php?page=users&error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    private function validateUserData($data, $userId = null) {
        $errors = [];

        if (empty($data['USER_KEY'])) {
            $errors[] = 'User key is required';
        } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $data['USER_KEY'])) {
            $errors[] = 'User key can only contain letters, numbers, dots, underscores, and hyphens';
        } elseif ($this->userModel->userKeyExists($data['USER_KEY'], $userId)) {
            $errors[] = 'User key already exists';
        }

        if (empty($data['DISPLAY_NAME'])) {
            $errors[] = 'Display name is required';
        }

        if (!empty($data['email_address']) && !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        return $errors;
    }
}