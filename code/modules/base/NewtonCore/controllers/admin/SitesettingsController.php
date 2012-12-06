<?php

namespace NewtonCore;

use URL;
use NewtonCore\Model\Lang;
use NewtonCore\Model\Site;
use Redirect;
use Newton\Form\Filter;
use Newton\Controller\Action;

class SitesettingsController extends Action
{
    protected $form = null;
    protected $siteSettings = null;

    /**
     * Init
     * @return void
     */
    public function init()
    {
        $this->form            = new Form\SiteSettings();
        $this->siteSettings    = Model\Setting::get('core/site', Lang::current(), Site::current());

        if(null === $this->siteSettings) {
            $this->siteSettings = array();
        }
    }


    /**
     * Displays the settings
     * @return [type] [description]
     */
    public function indexAction()
    {
        // Set the action
        $this->form->setAction(URL::route(array(
            'action' => 'save'
        )));

        // Populate the form
        $this->form->populate($this->siteSettings);

        $this->view->form = $this->form;
    }

    public function saveAction()
    {
        // Get the data and sanitise the input
        $data = $this->getRequest()->getPost();
        $data = Filter::sanitiseForm($data);

        // Is valid?
        $isValid = $this->form->isValid($data);

        if($this->getRequest()->isPost() && $isValid) {

            foreach($data as $k => $v) {
                $this->siteSettings[$k] = $v;
            }

            // Save the updated settings
            Model\Setting::set('core/site', $this->siteSettings, Lang::current(), Site::current());

            // Redirect back
            Redirect::to(URL::route(array(
                'action' => 'index'
            )));

        }

        $this->view->form = $this->form;

        // Render index
        $this->render('index');

    }
}