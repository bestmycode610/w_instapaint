<?php
defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Custom_Component_Block_Panel
 */
class Custom_Component_Block_Panel extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('custom.component_block_panel_clean')) ? eval($sPlugin) : false);
    }
}
