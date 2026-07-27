<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\View;
use App\Models\AdminUser;

final class AuthController
{
    public function login(): string
    {
        if (isset($_GET['reset'])) {
            unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_email']);
        }
        if (is_admin()) {
            redirect('/admin');
        }
        if (!empty($_SESSION['pending_admin_id'])) {
            redirect('/admin/login/kod');
        }

        return View::render('admin/login', ['title' => 'Logowanie admina'], 'admin-auth');
    }

    public function authenticate(): string
    {
        verify_csrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $admin = AdminUser::findByEmail($email);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $code = (string) random_int(100000, 999999);
            AdminUser::createEmailCode((int) $admin['id'], $code);

            $sent = Mailer::send(
                (string) $admin['email'],
                'Kod logowania do CRM - Integracja Studencka',
                "Czesc {$admin['name']},\n\n"
                . "Twoj kod logowania do CRM: {$code}\n\n"
                . "Kod jest wazny przez 10 minut. Jesli to nie Ty probujesz sie zalogowac, zmien haslo administratora.\n\n"
                . "Integracja Studencka"
            );

            if (!$sent) {
                return View::render('admin/login', [
                    'title' => 'Logowanie admina',
                    'error' => 'Nie udało się wysłać kodu na email. Sprawdź konfigurację SMTP.',
                ], 'admin-auth');
            }

            $_SESSION['pending_admin_id'] = (int) $admin['id'];
            $_SESSION['pending_admin_email'] = (string) $admin['email'];
            redirect('/admin/login/kod');
        }

        return View::render('admin/login', [
            'title' => 'Logowanie admina',
            'error' => 'Nieprawidłowy email lub hasło.',
        ], 'admin-auth');
    }

    public function code(): string
    {
        if (is_admin()) {
            redirect('/admin');
        }
        if (empty($_SESSION['pending_admin_id'])) {
            redirect('/admin/login');
        }

        return View::render('admin/login-code', [
            'title' => 'Kod logowania',
            'email' => $_SESSION['pending_admin_email'] ?? '',
        ], 'admin-auth');
    }

    public function verifyCode(): string
    {
        verify_csrf();
        if (empty($_SESSION['pending_admin_id'])) {
            redirect('/admin/login');
        }

        $adminId = (int) $_SESSION['pending_admin_id'];
        $code = preg_replace('/\D+/', '', (string) ($_POST['code'] ?? ''));
        $admin = AdminUser::find($adminId);

        if ($admin && strlen($code) === 6 && AdminUser::verifyEmailCode($adminId, $code)) {
            unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_email']);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = (string) $admin['name'];
            redirect('/admin');
        }

        return View::render('admin/login-code', [
            'title' => 'Kod logowania',
            'email' => $_SESSION['pending_admin_email'] ?? '',
            'error' => 'Nieprawidłowy albo wygasły kod.',
        ], 'admin-auth');
    }

    public function logout(): string
    {
        verify_csrf();
        session_destroy();
        redirect('/admin/login');
    }
}
