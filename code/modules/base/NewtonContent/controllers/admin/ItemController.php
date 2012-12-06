<?php

namespace NewtonContent;

use NewtonCore\Model\Lang;
use NewtonCore\Model\Site;
use NewtonCore\Model\UrlRewrites;
use NewtonContent\Form;
use Newton\Config;
use Newton\Controller\Action;
use Newton\Redirect;
use Newton\URL;
use Newton\Session;

class ItemController extends Action
{
    /**
     * Current Page Type
     * @var null|Newton\Form
     */
    protected $type = null;

    /**
     * Url Settings form
     * @var Newton\Form
     */    
    protected $urlRewriteForm = null;

    /**
     * init()
     * @return void
     */
    public function init()
    {
        // Initalizse the type manager
        Model\TypeManager::init();

        $this->view->title = 'Manage Content';

        // Set the URL Settings form
        $this->urlRewriteForm = new Form\Settings\Url();

        // Set the page setting form
        $this->pageSettingsForm = new Form\Settings\Page();

        // UrlSettings form action
        $this->urlRewriteForm->setAction(URL::route(array(
            'action' => 'saverewrites'
        )));

        // Page Settings Form action
        $this->pageSettingsForm->setAction(URL::route(array(
            'action' => 'savesettings'
        )));
    }

    /**
     * Default action
     *
     * Display all content
     * 
     * @return void
     */
    public function indexAction()
    {
        $isPage = $this->getRequest()->getParam('is_page');
        $type   = $this->getRequest()->getParam('type');

        $filter = array(
            'site'      => Site::current()
        );

        if($isPage) {
            $types = array();

            // Find all Types with isPage() set to true
            foreach(Model\TypeManager::getInstance()->getTypes() as $iType) {
                if($iType->getIsPage()) {
                    $types[] = $iType->getName();
                }
            }

            // Apply the types to the query
            $filter['type'] = array('$in' => $types);
        }

        // If isPage is not set and type is, filter just for that type
        if(!$isPage && $type) {
            $type   = Model\TypeManager::getTypeByName($type);
            $filter['type'] = $type->getName();
        }

        $this->view->type  = $type;
        $this->view->items = Model\Item::all($filter);

        if(null === $type) {
            $this->render('pages');
        }
    }


    /**
     * Add Action
     *
     * @return void
     */
    public function addAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Action. Also handes add
     * 
     * @return void
     */
    public function editAction()
    {
        // Get Request
        $request    = $this->getRequest();
        $data       = $request->getPost();
        $typeName   = $request->getParam('type');
        $id         = $request->getParam('id');
        $urlPrefix  = $request->getParam('urlPrefix');
        $urlSuffix  = $request->getParam('urlSuffix');
        $adding     = empty($id) ? true : false;

        // Retrieve the form from the Type Manager
        $type       = Model\TypeManager::getTypeByName($typeName);
        $form       = $type->getForm();

        if($adding) {
            $item = new Model\Item();
        } else {
            $item = Model\Item::find($id);
        }

        // Is the site the same as content?
        if($item->site != Site::current()) {
            Redirect::to(URL::route(array(
                'action'    => 'index',
                'id'        => null
            )));
        }

        // If is post and is va
        if($request->isPost()) {

            // Valid data?
            if($form->isValid($data)) {
                
                // Set the type
                $item->setProperty('type', $type->getName());

                // Set the page template
                $item->setProperty('template', $type->getTemplate());

                // Set the data
                $item->setData($data);
                $item->save();

                // Create automatic rewrites?
                if($type->getAutoCreateUrlRewrite() && $type->getUrlRewritesEnabled() && !$item->getRewrite()) {
                    $prefix = empty($urlPrefix) ? $type->getUrlPrefix() : $urlPrefix;
                    $suffix = empty($urlSuffix) ? $type->getUrlSuffix() : $urlSuffix;
                    $item->updateUrlRewrite(null, $prefix, $suffix);
                }

                Session::flashMessage('Successfully saved.', 'success');

                // Redirect, but back to edit
                Redirect::to(URL::route(array(
                    'action'    => 'edit',
                    'id'    => (string) $item->getId()
                )));

            } else {
                // Form is not valid
                // Show errorrrrrs
            }
        } elseif(!$adding) {
            $data = $item->getData()->toArray();
        }

        // Disable the UrlSettings form until this content has been saved
        if($adding) {
            $this->urlRewriteForm->disableForm(
                'You need to save the content item before you can edit the URL rewrites'
            );

            $this->pageSettingsForm->disableForm(
                'You need to save the content item before you can edit the page settings'
            );
        }

        // If: Editing
        if(!$adding) {

            // Poulate url settings
            $rewrite = $item->getRewrite();
            if($rewrite) {
                $this->urlRewriteForm->populate($rewrite->export());
            }

            // Populate 
            $this->pageSettingsForm->populate($item->export());
        }
        
        // Add the pageTitle (edit or adding page)
        if($adding) {
            $this->view->pageTitle = 'Adding Content Item';
        } else {
            $form->populate($data);

            // The page title, will be the first field inside $type->getDatatableMappings();
            $mappings = $type->getDatatableMappings();
            $this->view->pageTitle = isset($data[key($mappings)]) ? $data[key($mappings)] : '';
        }

        // Few view vars...
        $this->view->data               = $data;
        $this->view->form               = $form;
        $this->view->type               = $type;
        $this->view->urlForm            = $this->urlRewriteForm;
        $this->view->pageSettingsForm   = $this->pageSettingsForm;
    }

    /**
     * Delete Action
     *
     * @return void
     * @author Gravitywell Ltd
     */
    public function deleteAction()
    {
        $id   = $this->getRequest()->getParam('id');
        $doc  = Model\Item::find($id);

        if(!empty($doc)) {
            $doc->delete();
        }

        // Redirect back to index
        Redirect::to(URL::route(array(
            'controller'=> 'index',
            'action'    => 'index',
            'id'        => null
        )));
    }   


    /**
     * Saves the rewrites for a document
     * @return [type] [description]
     */
    public function saverewritesAction()
    {
        $docId = $this->getRequest()->getParam('id');
        $data  = $this->getRequest()->getPost();

        $doc = Model\Item::find($docId);
        $type = $doc->getType();
        $newRewrite = $doc->sanitise($data);

        // Check if url is empty, if it is, we reset to default
        if(empty($newRewrite['url'])) {

            // Also, remove the url rewrite
            $doc->removeUrlRewrite();
            $doc->updateUrlRewrite(null, $type->getUrlPrefix(), $type->getUrlSuffix());

        // Url isn't empty, try and save with this new url
        } else {

            // will be false if it was not usable
            $updated = $doc->updateUrlRewrite($newRewrite['url'], $type->getUrlPrefix(), $type->getUrlSuffix());

            if(false === $updated) {
                // Already exists
                // Add flash message
                ## FLASH MESSAGE SAGING AREADY EXISTS

                // Redirect back to edit, but the url tab
                Redirect::to(URL::route(array(
                    'action'            => 'edit'
                )) . '/#tab:tab-url');

            }
        }

        // Redirect back to edit
        Redirect::to(URL::route(array(
            'action'            => 'edit'
        )) . '/#tab:tab-url');
    } 


    /**
     * Saves the settings
     * @return [type] [description]
     */
    public function savesettingsAction()
    {
        $docId = $this->getRequest()->getParam('id');
        $data  = $this->getRequest()->getPost();

        $doc  = Model\Item::find($docId);
        $data = $doc->sanitise($data);

        foreach($data as $k => $v) {
            $doc->$k = $v;
        }
        $doc->save();

        // Redirect back to edit
        Redirect::to(URL::route(array(
            'action'            => 'edit',
            'type'              => $data['type']
        )) . '/#tab:tab-page-settings');
    }
}