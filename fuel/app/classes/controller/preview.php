<?php

/**
 * Preview controller
 * display a basic overview on document
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Preview extends Controller
{

    /**
     * Preview index
     * 
     * @access  public
     * @return  Response
     */
    public function action_index()
    {
        // load Tapioca config
        Tapioca::base();
        
        $previewId = $this->param('id', false);

        try
        {
            $document = Preview::get( $previewId );            
        }
        catch( PreviewException $e )
        {
            $document = $e->getMessage();
        }


        return View::forge('templates/preview', array('document' => $document ) );
    }
}
