<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Mailer\Mailer;
use Cake\Network\Exception\ForbiddenException;

/**
 * Volunteers Controller
 *
 * @property \App\Model\Table\VolunteersTable $Volunteers
 */
class VolunteersController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow();
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $volunteer = $this->Volunteers->newEntity();
        if ($this->request->is('post')) {
            $volunteer = $this->Volunteers->patchEntity($volunteer, $this->request->data);
            $saved = $this->Volunteers->save($volunteer);
            if ($saved) {
                $this->Flash->success(__('Signup complete. Thanks! We\'ll be in touch before the festival.'));
                Mailer::sendVolunteerSignupEmail($saved->id);
                return $this->redirect([
                    'controller' => 'Pages',
                    'action' => 'home'
                ]);
            } else {
                $msg = 'There was a problem submitting your information. Please check for error messages below';
                $msg .= ' and contact a site administrator if you need assistance.';
                $this->Flash->error($msg);
            }
        }
        $jobs = $this->Volunteers->Jobs->find('list', ['limit' => 200])->toArray();
        sort($jobs);
        $this->set(compact('volunteer', 'jobs'));
        $this->set([
            '_serialize' => ['volunteer'],
            'pageTitle' => 'Volunteer Signup'
        ]);
    }

    /**
     * Edit method
     *
     * @param string|null $id Volunteer id.
     * @param string|null $key Security key.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null, $key = null)
    {
        $volunteer = $this->Volunteers->get($id, [
            'contain' => ['Jobs']
        ]);
        $correctKey = $this->Volunteers->getSecurityHash($volunteer->email);
        if ($key != $correctKey) {
            $msg = 'Security key mismatch. Make sure you\'re using the full URL sent to you. ' . $correctKey;
            throw new ForbiddenException($msg);
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $volunteer = $this->Volunteers->patchEntity($volunteer, $this->request->data);
            $emailChanged = $volunteer->dirty('email');
            $saved = $this->Volunteers->save($volunteer);
            if ($saved) {
                if ($emailChanged) {
                    Mailer::sendVolunteerSignupEmail($saved->id);
                    $msg = 'Information updated. Oh, and <strong>since your email address was updated</strong>,';
                    $msg .= ' we\'ve sent another email to you with a new link for updating your volunteer info.';
                    $this->Flash->success($msg);
                } else {
                    $this->Flash->success('Information updated.');
                }
                return $this->redirect([
                    'controller' => 'Pages',
                    'action' => 'home'
                ]);
            } else {
                $msg = 'There was a problem updating your information. Please check for error messages below';
                $msg .= ' and contact a site administrator if you need assistance.';
                $this->Flash->error($msg);
            }
        }
        $jobs = $this->Volunteers->Jobs->find('list', ['limit' => 200]);
        $this->set(compact('volunteer', 'jobs'));
        $this->set([
            '_serialize' => ['volunteer'],
            'pageTitle' => 'Update Volunteer Info'
        ]);
    }
}
