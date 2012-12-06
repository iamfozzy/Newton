<?php

namespace NewtonContent;

use Zend_Auth;
use SAuth_Adapter_Facebook;
use Newton\Controller\Action;
use NewtonContent\Model\Item;

class IndexController extends Action
{
	public function indexAction()
	{
        
	}


    /**
     * This method is called when the NewtonCore\Router\UrlRewriter matches a route 
     * from the UrlRewrites model (url_rewrites collection) and finds a content item to display.
     * 
     * @return [type] [description]
     */
    public function viewAction()
    {
        $params = $this->getRequest()->getParam('params');

        // content id
        $id = isset($params['id']) ? $params['id'] : null;

        // Should be an id, why not?
        if(null === $id) {
            exit("Something broke. Please alert the site administrator.");
        }

        $item = Item::find($id);
        $data = $item->getData();

        // View vars
        $this->view->item = $item;
        $this->view->data = $data;

        // View script - this should not be the action, but should relate to the variable inside the view called 'template'.
        // if 'template' does't exist or is empty, then use 'default'
        if(null != $data->getPageTemplate()) {
            $this->renderScript('page-templates' . DS . $data->getTemplate());
        } else {
            $this->renderScript('page-templates' . DS . 'default.phtml');
        }
    }
}