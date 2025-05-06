<?php
/**
 * DokuWiki Plugin headerfooter (Action Component)
 *
 * @license GPL 3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @author  the-shoemaker <contact@dan-space.ch>
 */

if(!defined('DOKU_INC')) die();

class action_plugin_headerfooter extends DokuWiki_Action_Plugin {
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('PARSER_WIKITEXT_PREPROCESS', 'AFTER', $this, 'handle_parser_wikitext_preprocess');
   
    }
    public function handle_parser_wikitext_preprocess(Doku_Event &$event, $param) {
        global $INFO;
        if ($INFO['id'] != '') return;
    
        $inf = pageinfo();
        $nsPath = explode(':', $inf['namespace']);
        $header = '';
        $footer = '';
    
        // Traverse namespaces upwards
        $nsPath = explode(':', $inf['namespace']);
        $currentNS = implode('/', $nsPath);
        $header = '';
        $footer = '';
        
        // First: check only the current namespace for _header/footer.txt
        $baseCurrent = str_replace('\\', '/', DOKU_INC) . 'data/pages/' . ($currentNS ? $currentNS . '/' : '');
        
        if (file_exists($baseCurrent . '_header.txt')) {
            $header = file_get_contents($baseCurrent . '_header.txt');
        }
        if (file_exists($baseCurrent . '_footer.txt')) {
            $footer = file_get_contents($baseCurrent . '_footer.txt');
        }
        
        // Then: walk up to check for __header/footer.txt only if not already found
        for ($i = count($nsPath); $i >= 0; $i--) {
            $parentNS = implode('/', array_slice($nsPath, 0, $i));
            if ($parentNS !== '') $parentNS .= '/';
            $base = str_replace('\\', '/', DOKU_INC) . 'data/pages/' . $parentNS;
        
            if ($header === '' && file_exists($base . '__header.txt')) {
                $header = file_get_contents($base . '__header.txt');
            }
        
            if ($footer === '' && file_exists($base . '__footer.txt')) {
                $footer = file_get_contents($base . '__footer.txt');
            }
        
            if ($header !== '' && $footer !== '') break;
        }
    
        if ($this->getConf('separation') == 'paragraph') {
            if ($header !== '') $header = rtrim($header, " \r\n\\") . "\n\n";
            if ($footer !== '') $footer = "\n\n" . ltrim($footer, " \r\n\\");
        }
    
        $event->data = $header . $event->data . $footer;
    }
}
