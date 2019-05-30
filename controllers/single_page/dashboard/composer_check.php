<?php
namespace Concrete\Package\ComposerCheck\Controller\SinglePage\Dashboard;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\PageList;
use PageTemplate;
use PageType;
use Page;
use Loader;
use Concrete\Core\Page\Type\Composer\FormLayoutSetControl;
use Concrete\Block\CorePageTypeComposerControlOutput;
use \Concrete\Core\Page\Type\Composer\OutputControl as PageTypeComposerOutputControl;
use Concrete\Core\Page\Type\Composer\FormLayoutSetControl as PageTypeComposerFormLayoutSetControl;
use Database;
class ComposerCheck extends DashboardPageController
{

    public function view($page = 0)
    {

        if($this->getRequest()->isPost()):
            $parent = $this->post('page');
            $parent =  Page::getByID($parent);
            $path = $parent->getCollectionPath();
            $list = new \Concrete\Core\Page\PageList();
            $list->filterByPath($path,true);
            $pages = $list->getResults();
            $return = array();
            if($pages):
                foreach($pages as $p):
                    $return[] = $this->check($p);
                endforeach;
            endif;
            $this->set('checks', $return);
            $this->set('page',$this->post('page'));
        endif;

    }


    public function next(){
        $action = $this->post('submit');

        if($action == 'reset'):
            $db = Database::connection();
            $parent = $this->post('page');
            $parent =  Page::getByID($parent);
            $path = $parent->getCollectionPath();
            $list = new \Concrete\Core\Page\PageList();
            $list->filterByPath($path,true);
            $pages = $list->getResults();
            $return = array();
            if($pages):
                foreach($pages as $p):
                    $db->executeQuery('DELETE FROM PageTypeComposerOutputBlocks WHERE cID = ?', array(
                            $p->getCollectionID()
                        )
                    );
                    $return[] = $this->check($p);
                endforeach;
            endif;
            $this->set('checks', $return);
            $this->set('page',$this->post('page'));
        endif;

        if($action == 'fix'):
            $parent = $this->post('page');
            $parent =  Page::getByID($parent);
            $path = $parent->getCollectionPath();
            $list = new \Concrete\Core\Page\PageList();
            $list->filterByPath($path,true);
            $pages = $list->getResults();
            $return = array();
            if($pages):
                foreach($pages as $p):
                    $this->fix($p);
                endforeach;
                foreach($pages as $p):
                    $return[] = $this->check($p);
                endforeach;
            endif;
            $this->set('checks', $return);
            $this->set('page',$this->post('page'));
        endif;

    }


    public function fix($page, $area = 'Main'){
        $db = Database::connection();
        $return  = array();

        $return['pageID'] = $page->getCollectionID();
        $return['pageName'] = $page->getCollectionName();

        $pt = PageTemplate::getByID($page->getPageTemplateID());
        $ptt = PageType::getByID($page->getPageTypeID());
        $controls = PageTypeComposerOutputControl::getList($ptt, $pt);

        $cvID = $page->getVersionID();
        $cID = $page->getCollectionID();


        $defaultBlockHandles = array();
        $defaultBlockNames = array();
        foreach($controls as $control)
        {
            $fls = PageTypeComposerFormLayoutSetControl::getByID($control->getPageTypeComposerFormLayoutSetControlID());
            $TCCO = $fls->getPageTypeComposerControlObject();
            $bId = $TCCO->getPageTypeComposerControlIdentifier();
            $lock = BlockType::getById($bId);
            array_push($defaultBlockHandles, $lock->getBlockTypeHandle());
            array_push($defaultBlockNames, $lock->getBlockTypeName());
        }

        $return['defaultBlocksInComposers'] = $defaultBlockNames;

        $return['blocksInAreas'] = array();
        $blocks = $page->getBlocks();
        if($blocks):
            foreach($blocks as $b):
                $position = array_search($b->getBlockTypeHandle(), $defaultBlockHandles);
                if($position !== false):
                    $i = 0;
                    foreach ($controls as $control) {
                        if($i == $position):
                            $ptComposerFormLayoutSetControlID = $control->getPageTypeComposerFormLayoutSetControlID();
                            $db->executeQuery('DELETE FROM PageTypeComposerOutputBlocks WHERE cID = ? and cvID = ? and bID = ? and arHandle = ?', array(
                                    $cID, $cvID,   $b->bID, $b->getAreaHandle()
                                )
                            );

                            $db->executeQuery('INSERT INTO PageTypeComposerOutputBlocks (cID, cvID, arHandle, cbDisplayOrder, ptComposerFormLayoutSetControlID, bID) values (?, ?, ?, ?, ?, ?)', array(
                                    $cID,
                                    $cvID,
                                    $b->getAreaHandle(),
                                    $position,
                                    $ptComposerFormLayoutSetControlID,
                                    $b->bID
                                )
                            );

                        endif;
                        $i++;
                    }
                endif;
            endforeach;
        endif;

        return $return;
    }

    public function check($page, $area = 'Main'){
        $return  = array();

        $return['pageID'] = $page->getCollectionID();
        $return['pageName'] = $page->getCollectionName();

        $pt = PageTemplate::getByID($page->getPageTemplateID());
        $ptt = PageType::getByID($page->getPageTypeID());
        if(is_object($pt) && is_object($ptt)):
        $controls = PageTypeComposerOutputControl::getList($ptt, $pt);

        $defaultBlockHandles = array();
        $defaultBlockNames = array();
        foreach($controls as $control)
        {
            $fls = PageTypeComposerFormLayoutSetControl::getByID($control->getPageTypeComposerFormLayoutSetControlID());
            $TCCO = $fls->getPageTypeComposerControlObject();
            $bId = $TCCO->getPageTypeComposerControlIdentifier();
            $lock = BlockType::getById($bId);
            array_push($defaultBlockHandles, $lock->getBlockTypeHandle());
            array_push($defaultBlockNames, $lock->getBlockTypeName());
        }

        $return['defaultBlocksInComposers'] = $defaultBlockNames;

        $return['blocksInAreas'] = array();
        $blocks = $page->getBlocks();
        if($blocks):
            foreach($blocks as $b):
                $blocksData = array();
                $bl = BlockType::getByHandle($b->getBlockTypeHandle());
                $blocksData['id'] = $b->getBlockTypeID();
                $blocksData['name'] = $bl->getBlockTypeName();
                $blocksData['handle'] = $b->getBlockTypeHandle();

                if(in_array($b->getBlockTypeHandle(),$defaultBlockHandles)):
                    $blocksData['inComposer'] = true;
                else:
                    $blocksData['inComposer'] = false;
                endif;
                $blocksData['isMapped'] = $this->blockMapped($page, $b->bID);
                $return['blocksInAreas'][] = $blocksData;
            endforeach;
        endif;
        else:
            echo $page->getCollectionID();
            echo $page->getCollectionName();
        endif;
        return $return;
    }

    public function blockMapped($page, $bID, $area = 'Main'){
        $db = Database::connection();
        $ocID = $page->getCollectionID();
        $ovID = $page->getVersionID();

        $row = $db->fetchAssoc(
            'select cID, cvID, arHandle, cbDisplayOrder, ptComposerFormLayoutSetControlID from PageTypeComposerOutputBlocks where cID = ? and cvID = ? and bID = ?',
            array($ocID, $ovID, $bID)
        );

        if($row)
            return true;
        else
            return false;
    }

}