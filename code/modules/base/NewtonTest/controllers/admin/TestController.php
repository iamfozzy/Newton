<?php

namespace NewtonTest;

use Zend_Pdf;
use Newton\Controller\Action;
use Newton\Module\Manager;

class TestController extends Action
{
    public function indexAction()
    { 
        $path = Manager::getModulePath('NewtonTest') . DS . 'etc' . DS . 'test.pdf';

        $pdf = Zend_Pdf::load($path);
        $page = $pdf->pages[0];
        $font = \Zend_Pdf_Font::fontWithName('Times-Roman');
        $page->setFont($font, 12);
        $page->drawText('Hello world!', 72, 720);

        $pdf->save($path);
    }
}