<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(url('/'));
        }

        $this->render('auth/login', [
            'pageTitle' => 'Giriş Yap - SocialNet'
        ], layout: 'layouts/auth');
    }

    public function processLogin(): void
    {
        $errors = $this->validate([
            'login'    => 'required',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            Session::setFlash('error', 'Lütfen tüm alanları doldurunuz.');
            $this->redirect(url('/login'));
            return;
        }

        $login = trim($this->request->input('login'));
        $password = (string)$this->request->input('password');

        $user = $this->userModel->findByLogin($login);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            Session::setFlash('error', 'Girdiğiniz kullanıcı adı/e-posta veya şifre hatalı.');
            $this->redirect(url('/login'));
            return;
        }

        if ((int)$user['is_banned'] === 1) {
            Session::setFlash('error', 'Hesabınız askıya alınmıştır. Sebep: ' . ($user['ban_reason'] ?? 'Topluluk kuralları ihlali.'));
            $this->redirect(url('/login'));
            return;
        }

        // Set Auth Session
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('role_id', $user['role_id']);

        // Update last active
        $this->userModel->update($user['id'], ['last_active_at' => date('Y-m-d H:i:s')]);

        Session::setFlash('success', "Tekrar hoş geldiniz, @{$user['username']}!");
        $this->redirect(url('/'));
    }

    public function showRegister(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(url('/'));
        }

        $this->render('auth/register', [
            'pageTitle' => 'Kayıt Ol - SocialNet'
        ], layout: 'layouts/auth');
    }

    public function processRegister(): void
    {
        $errors = $this->validate([
            'full_name'        => 'required|min:2|max:100',
            'username'         => 'required|min:3|max:30|alpha_num',
            'email'            => 'required|email|max:100',
            'password'         => 'required|min:6',
            'password_confirm' => 'required|matches:password'
        ]);

        $username = trim($this->request->input('username'));
        $email = trim($this->request->input('email'));

        if ($this->userModel->findByUsername($username)) {
            $errors['username'][] = 'Bu kullanıcı adı zaten alınmış.';
        }

        if ($this->userModel->findByEmail($email)) {
            $errors['email'][] = 'Bu e-posta adresi ile zaten kayıtlı bir hesap var.';
        }

        if (!empty($errors)) {
            // Flatten errors for flash
            $firstError = reset($errors)[0];
            Session::setFlash('error', $firstError);
            $this->redirect(url('/register'));
            return;
        }

        $userId = $this->userModel->register([
            'full_name' => trim($this->request->input('full_name')),
            'username'  => $username,
            'email'     => $email,
            'password'  => (string)$this->request->input('password'),
        ]);

        if (!$userId) {
            Session::setFlash('error', 'Kayıt işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.');
            $this->redirect(url('/register'));
            return;
        }

        // Auto login
        Session::set('user_id', $userId);
        Session::set('username', $username);
        Session::set('role_id', 3);

        Session::setFlash('success', "Hesabınız başarıyla oluşturuldu! Hoş geldiniz.");
        $this->redirect(url('/'));
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect(url('/login'));
    }
}
