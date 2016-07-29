<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Bands Controller
 *
 * @property \App\Model\Table\BandsTable $Bands
 */
class BandsController extends AppController
{

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->deny();
    }

    /**
     * Method for /admin/bands
     *
     * @return void
     */
    public function index()
    {
        $this->set([
            'bands' => [
                'Bands Applied' => $this->Bands
                    ->find('list')
                    ->find('applied')
                    ->order(['Bands.name' => 'ASC'])
                    ->toArray(),
                'Bands Not Done Applying' => $this->Bands
                    ->find('list')
                    ->find('applicationIncomplete')
                    ->order(['Bands.name' => 'ASC'])
                    ->toArray()
            ],
            'pageTitle' => 'Bands'
        ]);
    }

    /**
     * Method for /admin/bands/view/:id
     *
     * @param int|null $id
     * @return void
     */
    public function view($id = null)
    {
        $band = $this->Bands->get($id, [
            'contain' => ['Songs', 'Pictures']
        ]);
        $playlist = [];
        foreach ($band->songs as $song) {
            $playlist[] = [
                'title' => $song->title,
                'artist' => $band->name,
                'mp3' => '/music/' . $song->filename
            ];
        }
        $bands = $this->Bands->find('list')->order(['Bands.name' => 'ASC']);
        $back = $this->request->query('back');
        if (!$back) {
            $back = [
                'prefix' => 'admin',
                'controller' => 'Bands',
                'action' => 'index'
            ];
        }
        $this->set([
            'back' => $back,
            'band' => $band,
            'bands' => $bands,
            'fields' => [
                'tier',
                'genre',
                'hometown',
                'minimum_fee',
                'check_name',
                'website',
                'social_networking',
                'rep_name',
                'email',
                'phone',
                'address',
                'member_count',
                'member_names',
                'description',
                'message',
                'stage_setup'
            ],
            'pageTitle' => $band->name,
            'playlist' => $playlist
        ]);
    }

    /**
     * Method for /admin/bands/emails, which shows lists of all of the band email addresses in various categories
     *
     * @return void
     */
    public function emails()
    {
        $lists = [];

        $lists['All bands'] = $this->Bands->find('list', [
                'keyField' => 'id',
                'valueField' => 'email'
            ])
            ->order(['email' => 'ASC'])
            ->toArray();

        $lists['Bands with complete applications'] = $this->Bands->find('list', [
                'keyField' => 'id',
                'valueField' => 'email'
            ])
            ->where(['application_step' => 'done'])
            ->order(['email' => 'ASC'])
            ->toArray();

        $lists['Bands with incomplete applications'] = $this->Bands->find('list', [
                'keyField' => 'id',
                'valueField' => 'email'
            ])
            ->where(['application_step <>' => 'done'])
            ->order(['email' => 'ASC'])
            ->toArray();

        $this->set([
            'pageTitle' => 'Band email lists',
            'lists' => $lists
        ]);
    }

    public function basicInfo()
    {
        $bands = $this->Bands->find('all')
            ->select(['id', 'name', 'genre', 'hometown', 'minimum_fee', 'application_step'])
            ->order(['name' => 'ASC']);
        $this->set([
            'pageTitle' => 'Bands - Basic Info',
            'bands' => $bands
        ]);
    }
}
