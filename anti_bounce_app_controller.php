<?php

class AntiBounceAppController extends AppController
{
    public $uses = array();

    /**
     * AntiBounce instance
     */
    public $AntiBounce;

    /**
     * {@inheritDoc}
     */
    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->autoRender = false;
        $this->autoLayout = false;
    }
}

?>
