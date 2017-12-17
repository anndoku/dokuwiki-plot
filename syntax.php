<?php
/**
 * plot-Plugin: Parses plot-blocks
 *
 * @license    MIT
 * @author     Ann He <me@annhe.net>
 */


if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_plot extends DokuWiki_Syntax_Plugin {
    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'normal';
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 100;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<plot.*?>\n.*?\n</plot>',$mode,'plugin_plot');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        $info = $this->getInfo();

        // prepare default data
        $return = array(
                        'layout'    => 'dot',
                        'align'     => '',
						'chof' => 'png',
                       );

        // prepare input
        $lines = explode("\n",$match);
        $conf = array_shift($lines);
        array_pop($lines);

        // match config options
        if(preg_match('/\b(left|center|right)\b/i',$conf,$match)) $return['align'] = $match[1];
        if(preg_match('/\b(dot|neato|twopi|circo|fdp|sfdp|markdown:\w+|ditaa)\b/i',$conf,$match)){
            $return['layout'] = strtolower($match[1]);
        }
		
		$return['input'] = urlencode(join("\n", $lines));
        // store input for later use
        return $return;
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $R, $data) {
        if($format == 'xhtml'){
            $img = $this->_remote($data);;
            $R->doc .= '<img src="'.$img.'" class="media'.$data['align'].'" alt=""';
            if($data['width'])  $R->doc .= ' width="'.$data['width'].'"';
            if($data['height']) $R->doc .= ' height="'.$data['height'].'"';
            if($data['align'] == 'right') $R->doc .= ' align="right"';
            if($data['align'] == 'left')  $R->doc .= ' align="left"';
            $R->doc .= '/>';
            return true;
        }
        return false;
    }

    /**
     * Render the output remotely at plot API
     */
    function _remote($data){
		$api = $this->getConf('api');
		$notGv = array("markdown", "ditaa");
		if(in_array(explode(":", $data['layout'])[0], $notGv)) {
			$engine = $data['layout'];
		} else {
			$engine = "gv:" . $data['layout'];
		}
		$img = $api . "?cht=" . $engine . "&chl=" . $data['input'] . "&chof=" . $data['chof'];
        return $img;
    }
}
