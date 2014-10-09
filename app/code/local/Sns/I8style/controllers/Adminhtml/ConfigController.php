<?php
define('PHP_TAB', chr(9));
class Sns_I8style_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action{ 
    public function indexAction() {
        $this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/sns_i8style_cfg/"));
    }
    public function configAction() {
		//$this->createConfig();
		$this->createPages();
		$this->createBlocks();
        $this->getResponse()->setRedirect($this->getUrl("adminhtml/system_config/edit/section/sns_i8style_cfg/"));
    }
	public function createBlocks() {
    	$pathEtc = Mage::getModuleDir('etc', 'Sns_I8style');
    	$blockFile = $pathEtc.DS.'import'.DS.'blocks.xml';

		$blockCollection = Mage::getModel('cms/block')->getCollection()->addFieldToFilter('is_active', 1);
		
		$mess = '';
		$mess .= '<h2>Static Block</h2>';
		$mess .= '<ol>';
		
	    // create xml document 
	    $xmldoc = new DOMDocument(); 
	    $xmldoc->formatOutput = true; 
	     
	    // create root nodes 
	    $root = $xmldoc->createElement("root"); 
	    $xmldoc->appendChild( $root ); 
	    
	    $blocks = $xmldoc->createElement("blocks");
	    $root->appendChild( $blocks ); 
	    
	    // start block
	    foreach ($blockCollection as $cmsblock) :
	    	if (preg_match('/^i8style_/i', $cmsblock->getIdentifier())) :
	    	
	    		$mess .= '<li>';
	    		$mess .= $cmsblock->getIdentifier() . '   -   ' . $cmsblock->getTitle();
	    		$mess .= '</li>';
	    	
		    	$blockData = $cmsblock->getData();

			    $cms_block = $xmldoc->createElement("cms_block");
			    $title = $xmldoc->createElement("title"); 
			    $title->appendChild($xmldoc->createTextNode($cmsblock->getTitle())); 
			    $cms_block->appendChild( $title );

			    $identifier = $xmldoc->createElement("identifier"); 
			    $identifier->appendChild($xmldoc->createTextNode($cmsblock->getIdentifier())); 
			    $cms_block->appendChild( $identifier );
			    
			    $content = $xmldoc->createElement("content"); 
				$contentHTML = $xmldoc->createDocumentFragment();
				$contentHTML->appendXML('<![CDATA['.$blockData['content'].']]>');
				$content->appendChild($contentHTML);
			    $cms_block->appendChild( $content );
			    
			    $is_active = $xmldoc->createElement("is_active"); 
			    $is_active->appendChild($xmldoc->createTextNode($blockData['is_active'])); 
			    $cms_block->appendChild( $is_active );
				
			    $stores = $xmldoc->createElement("stores");
			    $item = $xmldoc->createElement("item");
			    $item->appendChild($xmldoc->createTextNode('0')); 
			    $stores->appendChild( $item );
			    $cms_block->appendChild( $stores );

			    $blocks->appendChild($cms_block);
		    endif;
		endforeach;
		
		$mess .= '</ol>';
		
	    $xmlData = $xmldoc->saveXML();
	    $xmlData = $this->xmlentities($xmlData);
	    @file_put_contents($blockFile, $xmlData);
	    
		Mage::getSingleton('adminhtml/session')->addSuccess($mess);
	}
	public function createPages() {
    	$pathEtc = Mage::getModuleDir('etc', 'Sns_I8style');
    	$pageFile = $pathEtc.DS.'import'.DS.'pages.xml';

		$pageCollection = Mage::getModel('cms/page')->getCollection(); 
		$pageCollection->getSelect()->where('is_active = 1');
		
		
		$mess = '';
		$mess .= '<h2>CMS Page</h2>';
		$mess .= '<ol>';
		
	    // create xml document 
	    $xmldoc = new DOMDocument(); 
	    $xmldoc->formatOutput = true; 
	     
	    // create root nodes 
	    $root = $xmldoc->createElement("root"); 
	    $xmldoc->appendChild( $root ); 
	    
	    $pages = $xmldoc->createElement("pages");
	    $root->appendChild( $pages ); 
	    
	    // start page
	    foreach ($pageCollection as $cmspage) :
	    	if (preg_match('/^i8style_/i', $cmspage->getIdentifier())) :
		    	$pageData = $cmspage->getData();

	    		$mess .= '<li>';
	    		$mess .= $cmspage->getIdentifier() . '   -   ' . $cmspage->getTitle();
	    		$mess .= '</li>';

			    $cms_block = $xmldoc->createElement("cms_block");
			    $title = $xmldoc->createElement("title"); 
			    $title->appendChild($xmldoc->createTextNode($cmspage->getTitle())); 
			    $cms_block->appendChild( $title );

			    $identifier = $xmldoc->createElement("identifier"); 
			    $identifier->appendChild($xmldoc->createTextNode($cmspage->getIdentifier())); 
			    $cms_block->appendChild( $identifier );
			    
			    $content = $xmldoc->createElement("content"); 
				$contentHTML = $xmldoc->createDocumentFragment();
				$contentHTML->appendXML('<![CDATA['.$pageData['content'].']]>');
				$content->appendChild($contentHTML);
			    $cms_block->appendChild( $content );
			    
			    $is_active = $xmldoc->createElement("is_active"); 
			    $is_active->appendChild($xmldoc->createTextNode($pageData['is_active'])); 
			    $cms_block->appendChild( $is_active );
				
			    $stores = $xmldoc->createElement("stores");
			    $item = $xmldoc->createElement("item");
			    $item->appendChild($xmldoc->createTextNode('0')); 
			    $stores->appendChild( $item );
			    $cms_block->appendChild( $stores );
				
			    $root_template = $xmldoc->createElement("root_template"); 
			    $root_template->appendChild($xmldoc->createTextNode($pageData['root_template'])); 
			    $cms_block->appendChild( $root_template );

			    $layout_update_xml = $xmldoc->createElement("layout_update_xml"); 
				$layout_update_xmlHTML = $xmldoc->createDocumentFragment();
				$layout_update_xmlHTML->appendXML('<![CDATA['.$pageData['layout_update_xml'].']]>');
				$layout_update_xml->appendChild($layout_update_xmlHTML);
			    $cms_block->appendChild( $layout_update_xml );

			    $pages->appendChild($cms_block);
		    endif;
		endforeach;

		$mess .= '</ol>';

	    $xmlData = $xmldoc->saveXML();
	    $xmlData = $this->xmlentities($xmlData);
	    @file_put_contents($pageFile, $xmlData);
	    
	    Mage::getSingleton('adminhtml/session')->addSuccess($mess);
	}
	public function createConfig() {
		$this->removeConfig();
    	$pathEtc = Mage::getModuleDir('etc', 'Sns_I8style');
		$xmlPath = $pathEtc.DS.'config.xml';
		$xmlObj = new Varien_Simplexml_Config($xmlPath);
		$section = Mage::getStoreConfig('sns_i8style_cfg');
		foreach($section as $group => $fields){
			foreach($fields as $field => $value){
				$xmlObj->setNode('default/sns_i8style_cfg/'.$group.'/'.$field, '<![CDATA['.$value.']]>');
			}
		}
		$xmlData = $xmlObj->getNode();
		//die(get_class($xmlData));
		if(is_writable($xmlPath)) {
			$xml_string = '<?xml version="1.0" ?>' . $this->get_text_of_element($xmlData);
		    @file_put_contents($xmlPath, $xml_string);
		}
	}
	function removeConfig () {
    	$pathEtc = Mage::getModuleDir('etc', 'Sns_I8style');
		$xmlPath = $pathEtc.DS.'config.xml';
		$dom = new DOMDocument();
		$dom->load($xmlPath);
		$configs = $dom->getElementsByTagName('sns_i8style_cfg'); //translate
		foreach ($configs as $node){
			if(!$node->getAttribute('translate')){
				for($i = 0; $i < $node->childNodes->length; $i++) {
					$node->removeChild($node->childNodes->item($i));
				}
			}
		}
	    $xmlData = $dom->saveXML();
	    @file_put_contents($xmlPath, $xmlData);
	}
	function get_text_of_element($element, $level=0, $indent=PHP_TAB){
		$text = str_repeat($indent, $level) . '<' . $element->getName();
		foreach ($element->attributes() as $name => $value) {
			if ($value == 'safehtml' && $name=='filter'){
				$value = 'raw';
			}
			$text .= " $name=\"" . htmlentities($value) . "\"";
		}
		if ($element->count()){
			$text .= '>' . PHP_EOL;
			foreach ($element->children() as $subElement){
			$text .= $this->get_text_of_element($subElement, $level+1, $indent);
		}
			$text .= str_repeat($indent, $level) . '</' . $element->getName() . '>' . PHP_EOL;
		} else {
			$textContent = (string)$element;
			if (empty($textContent)){
				$text .= ' />' . PHP_EOL;
			} else {
				$text .= '>' . $textContent . '</' . $element->getName() . '>' . PHP_EOL;
			}
		}
		return $text;
	}
    public function xmlentities($value = null) {
        if (is_null($value)) {
            $value = $this;
        }
        $value = (string)$value;

        $value = str_replace(
        	array('<?xml version="1.0"?>'),
            array(''),
            $value
        );

        return $value;
    }
}
?>