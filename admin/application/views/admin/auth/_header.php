<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#6576ff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="<?php echo base_url('manifest.json'); ?>?v=<?php echo @filemtime(FCPATH . 'manifest.json') ?: time(); ?>">
    <link rel="apple-touch-icon" href="<?php echo base_url('assets/icons/admin-apple-touch-180.png'); ?>?v=<?php echo @filemtime(FCPATH . 'assets/icons/admin-apple-touch-180.png') ?: time(); ?>">
    <title><?php echo isset($title) ? html_escape($title) : 'Admin'; ?> - BODARE Pension House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1a2238;
            --secondary-color: #b2945b;
        }
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2a3a5a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2a3a5a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h3 {
            margin: 0;
            font-weight: 600;
        }
        .login-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(178, 148, 91, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2a3a5a 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 5px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #2a3a5a 0%, var(--primary-color) 100%);
            color: white;
        }
        .input-group-text {
            background: #f8f9fa;
            border-right: none;
        }
        .form-control.with-icon {
            border-left: none;
        }
        .auth-links {
            text-align: center;
            margin-top: 18px;
            font-size: 0.9rem;
        }
        .auth-links a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h3><i class="bi <?php echo isset($header_icon) ? $header_icon : 'bi-shield-lock'; ?>"></i> <?php echo isset($header_title) ? html_escape($header_title) : 'Admin'; ?></h3>
            <p class="mb-0 mt-2" style="opacity: 0.9;">BODARE Pension House</p>
        </div>
        <div class="login-body">
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $this->session->flashdata('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (function_exists('validation_errors') && validation_errors()): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo validation_errors('<div>', '</div>'); ?>
                </div>
            <?php endif; ?>
