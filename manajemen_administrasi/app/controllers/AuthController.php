<?php

class AuthController extends Controller
{
    private function ensureCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function validateCsrf($token)
    {
        return isset($token) && $token === ($_SESSION['csrf_token'] ?? '');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';

            if (!$this->validateCsrf($csrf)) {
                $this->ensureCsrfToken();
                return $this->view('auth/login', [
                    'error' => 'Token tidak valid.',
                    'layout' => 'login-page',
                    'title' => 'Login Sistem'
                ]);
            }

            if (empty($username) || empty($password)) {
                $this->ensureCsrfToken();
                return $this->view('auth/login', [
                    'error' => 'Username dan password wajib diisi.',
                    'layout' => 'login-page',
                    'title' => 'Login Sistem'
                ]);
            }

            $user = $this->model('UserModel')->findFullByUsername($username);

            if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
                if (empty($user['hak_akses'])) {
                    $this->ensureCsrfToken();
                    return $this->view('auth/login', [
                        'error' => 'Akun belum memiliki hak akses.',
                        'layout' => 'login-page',
                        'title' => 'Login Sistem'
                    ]);
                }

                $pegawai = $this->model('PegawaiModel')->findByNIP($user['nomor_induk_pegawai']);

                $_SESSION['user'] = [
                    'id'                    => $user['id'],
                    'username'              => $user['username'],
                    'nama_lengkap'          => $pegawai['nama_lengkap'] ?? 'Pegawai',
                    'jabatan_id'            => $user['jabatan_id'],
                    'nama_jabatan'          => $user['nama_jabatan'] ?? '',
                    'nomor_induk_pegawai'   => $user['nomor_induk_pegawai'],
                    'hak_akses'             => $user['hak_akses'],
                    'unit_id'               => $pegawai['unit_id'] ?? null,
                    'pegawai_id'            => $pegawai['id'] ?? null
                ];

                return $this->redirect('dashboard');
            }

            $this->ensureCsrfToken();
            return $this->view('auth/login', [
                'error' => 'Username atau password salah.',
                'layout' => 'login-page',
                'title' => 'Login Sistem'
            ]);
        }

        $this->ensureCsrfToken();
        return $this->view('auth/login', [
            'layout' => 'login-page',
            'title' => 'Login Sistem'
        ]);
    }

    public function register()
    {
        $jabatan = $this->model('JabatanModel')->getAll();
        $pegawaiList = $this->model('PegawaiModel')->getAllWithoutUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $nip = $_POST['nomor_induk_pegawai'] ?? '';
            $jabatan_id = $_POST['jabatan_id'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';

            if (!$this->validateCsrf($csrf)) {
                $this->ensureCsrfToken();
                return $this->view('auth/register', [
                    'error' => 'Token tidak valid.',
                    'jabatan' => $jabatan,
                    'pegawaiList' => $pegawaiList,
                    'layout' => 'login-page',
                    'title' => 'Registrasi Pengguna'
                ]);
            }

            if (empty($username) || empty($password) || empty($nip) || empty($jabatan_id)) {
                $this->ensureCsrfToken();
                return $this->view('auth/register', [
                    'error' => 'Semua field wajib diisi.',
                    'jabatan' => $jabatan,
                    'pegawaiList' => $pegawaiList,
                    'layout' => 'login-page',
                    'title' => 'Registrasi Pengguna'
                ]);
            }

            $pegawai = $this->model('PegawaiModel')->findByNIP($nip);
            if (!$pegawai) {
                $this->ensureCsrfToken();
                return $this->view('auth/register', [
                    'error' => 'Pegawai dengan NIP tersebut belum terdaftar.',
                    'jabatan' => $jabatan,
                    'pegawaiList' => $pegawaiList,
                    'layout' => 'login-page',
                    'title' => 'Registrasi Pengguna'
                ]);
            }

            $userModel = $this->model('UserModel');
            if ($userModel->findByNIP($nip)) {
                $this->ensureCsrfToken();
                return $this->view('auth/register', [
                    'error' => 'Pegawai ini sudah memiliki akun.',
                    'jabatan' => $jabatan,
                    'pegawaiList' => $pegawaiList,
                    'layout' => 'login-page',
                    'title' => 'Registrasi Pengguna'
                ]);
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userId = $userModel->create([
                'username' => $username,
                'password' => $hashedPassword,
                'nomor_induk_pegawai' => $nip,
                'jabatan_id' => $jabatan_id
            ]);

            if ($userId) {
                $this->model('HakAksesModel')->insertAuto($username, $nip, $jabatan_id);
                return $this->redirect('auth/login');
            }

            $this->ensureCsrfToken();
            return $this->view('auth/register', [
                'error' => 'Gagal membuat akun. Username mungkin sudah digunakan.',
                'jabatan' => $jabatan,
                'pegawaiList' => $pegawaiList,
                'layout' => 'login-page',
                'title' => 'Registrasi Pengguna'
            ]);
        }

        $this->ensureCsrfToken();
        return $this->view('auth/register', [
            'jabatan' => $jabatan,
            'pegawaiList' => $pegawaiList,
            'layout' => 'login-page',
            'title' => 'Registrasi Pengguna'
        ]);
    }

    public function forgot_password()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $csrf = $_POST['csrf_token'] ?? '';

            if (!$this->validateCsrf($csrf)) {
                $this->ensureCsrfToken();
                return $this->view('auth/forgot_password', [
                    'error' => 'Token tidak valid.',
                    'layout' => 'login-page',
                    'title' => 'Reset Password'
                ]);
            }

            if (empty($username) || empty($newPassword)) {
                $this->ensureCsrfToken();
                return $this->view('auth/forgot_password', [
                    'error' => 'Username dan password baru wajib diisi.',
                    'layout' => 'login-page',
                    'title' => 'Reset Password'
                ]);
            }

            if (strtolower($username) === 'admin') {
                $this->ensureCsrfToken();
                return $this->view('auth/forgot_password', [
                    'error' => 'Reset password untuk akun admin tidak diperbolehkan.',
                    'layout' => 'login-page',
                    'title' => 'Reset Password'
                ]);
            }

            $userModel = $this->model('UserModel');
            $user = $userModel->findByUsername($username);

            if (!$user) {
                $this->ensureCsrfToken();
                return $this->view('auth/forgot_password', [
                    'error' => 'Username tidak ditemukan.',
                    'layout' => 'login-page',
                    'title' => 'Reset Password'
                ]);
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            if ($userModel->updatePassword($user['id'], $hashedPassword)) {
                return $this->redirect('auth/login');
            }

            $this->ensureCsrfToken();
            return $this->view('auth/forgot_password', [
                'error' => 'Gagal mengubah password.',
                'layout' => 'login-page',
                'title' => 'Reset Password'
            ]);
        }

        $this->ensureCsrfToken();
        return $this->view('auth/forgot_password', [
            'layout' => 'login-page',
            'title' => 'Reset Password'
        ]);
    }

    public function logout()
    {
        session_destroy();
        return $this->redirect('auth/login');
    }
}