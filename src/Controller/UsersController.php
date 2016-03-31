<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Mailer\Mailer;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        if ($this->request->action === 'register') {
            $this->loadComponent('Recaptcha.Recaptcha');
        }
        $this->Auth->allow([
            'forgotPassword',
            'login',
            'logout',
            'register',
            'resetPassword'
        ]);
    }

    public function register()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            if ($this->Recaptcha->verify()) {
                $this->request->data['password'] = $this->request->data('new_password');
                $this->request->data['role'] = 'user';
                $user = $this->Users->patchEntity($user, $this->request->data(), [
                    'fieldList' => ['name', 'email', 'password', 'role']
                ]);
                $errors = $user->errors();
                if (empty($errors)) {
                    $user = $this->Users->save($user);
                    $this->Flash->success('Your account has been registered. You may now log in.');
                    return $this->redirect(['action' => 'login']);
                } else {
                    $this->Flash->error('There was an error registering your account. Please try again.');
                }
            } else {
                $this->Flash->error('There was an error verifying your reCAPTCHA response. Please try again.');
            }
        }

        /* So the password fields aren't filled out automatically when the user
         * is bounced back to the page by a validation error */
        $this->request->data['new_password'] = null;
        $this->request->data['confirm_password'] = null;

        $this->set([
            'pageTitle' => 'Register an Account',
            'user' => $user
        ]);
    }

    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Flash->success('You have been successfully logged in');
                $this->Auth->setUser($user);

                // Remember login information
                if ($this->request->data('auto_login')) {
                    $this->Cookie->configKey('CookieAuth', [
                        'expires' => '+1 year',
                        'httpOnly' => true
                    ]);
                    $this->Cookie->write('CookieAuth', [
                        'email' => $this->request->data('email'),
                        'password' => $this->request->data('password')
                    ]);
                }

                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error('Email or password is incorrect');
            }
        } else {
            $this->request->data['auto_login'] = true;
        }
        $this->set([
            'pageTitle' => 'Log in',
            'user' => $this->Users->newEntity()
        ]);
    }

    public function logout()
    {
        $this->Cookie->delete('CookieAuth');
        return $this->redirect($this->Auth->logout());
    }

    public function forgotPassword()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $email = $this->request->data('email');
            $adminEmail = Configure::read('adminEmail');
            if (empty($email)) {
                $msg = 'Please enter the email address you registered with to have your password reset. ';
                $msg .= 'Email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.';
                $this->Flash->error($msg);
            } else {
                $userId = $this->Users->getIdWithEmail($email);
                if ($userId) {
                    if (Mailer::sendPasswordResetEmail($userId)) {
                        $this->Flash->success('Success! You should be shortly receiving an email with a link to reset your password.');
                        $this->request->data = [];
                    } else {
                        $msg = 'There was an error sending your password-resetting email. ';
                        $msg .= 'Please try again, or email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.';
                        $this->Flash->error($msg);
                    }
                } else {
                    $msg = 'We couldn\'t find an account registered with the email address <strong>'.$email.'</strong>. ';
                    $msg .= 'Please make sure you spelled it correctly, and email ';
                    $msg .= '<a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> if you need assistance.';
                    $this->Flash->error($msg);
                }
            }
        }
        $this->set([
            'pageTitle' => 'Forgot Password',
            'user' => $user
        ]);
    }

    public function resetPassword($userId = null, $timestamp = null, $hash = null)
    {
        if (! $userId || ! $timestamp && ! $hash) {
            throw new NotFoundException('Incomplete URL for password-resetting. Did you leave out part of the URL when you copied and pasted it?');
        }

        if (time() - $timestamp > 60 * 60 * 24) {
            throw new ForbiddenException('Sorry, that link has expired.');
        }

        $expectedHash = Mailer::getPasswordResetHash($userId, $timestamp);
        if ($hash != $expectedHash) {
            throw new ForbiddenException('Invalid security key');
        }

        $user = $this->Users->get($userId);
        $email = $user->email;

        if ($this->request->is(['post', 'put'])) {
            $this->request->data['password'] = $this->request->data('new_password');
            $user = $this->Users->patchEntity($user, $this->request->data(), [
                'fieldList' => ['password']
            ]);
            if ($this->Users->save($user)) {
                $this->Flash->success('Your password has been updated.');
                return $this->redirect(['action' => 'login']);
            }
        }
        $this->request->data = [];

        $this->set([
            'email' => $email,
            'pageTitle' => 'Reset Password',
            'user' => $this->Users->newEntity()
        ]);
    }

    public function changePassword()
    {
        $userId = $this->Auth->user('id');
        $user = $this->Users->get($userId);
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['password'] = $this->request->data('new_password');
            $user = $this->Users->patchEntity($user, $this->request->data(), [
                'fieldList' => ['password']
            ]);
            if ($this->Users->save($user)) {
                $this->Flash->success('Your password has been updated');

                // If user logs in via cookie, update cookie login credentials
                if ($this->Cookie->read('CookieAuth')) {
                    $this->Cookie->write('CookieAuth.password', $this->request->data('new_password'));
                }
            }
        }
        $this->request->data = [];
        $this->set([
            'pageTitle' => 'Change Password',
            'user' => $user
        ]);
    }
}
