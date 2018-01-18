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
                        'align'     => 'center',
						'chof' => 'png',
                       );

        // prepare input
        $lines = explode("\n",$match);
        $conf = array_shift($lines);
        array_pop($lines);

        // match config options
        if(preg_match('/\b(left|center|right)\b/i',$conf,$match)) $return['align'] = $match[1];
        if(preg_match('/\b(dot|neato|twopi|circo|fdp|sfdp|markdown:\w+|ditaa|markdown)\b/i',$conf,$match)){
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
		$id = $this->getGUID();
		$cht = $this->_cht($data);
		$tpl='<div style="display:none" class="zxsq_mindmap_form">' .
			'<form accept-charset="utf-8" name="' . $id . '" id="' . $id . 
			'" method="post" action="' . $this->getConf('api') . '" enctype="application/x-www-form-urlencoded">'.
			'<input type="hidden" name="cht" value="' . $cht . '" id="cht_' . $id . '">' .
			'<input type="hidden" name="chof" value="' . $data['chof'] . '" id="chof_' . $id . '">' .
			'<textarea name="chl" id="chl_' . $id . '">' . $data['input'] . '</textarea></form></div>' .
			'<img id="img_' . $id . '" src="' . DOKU_URL . 
			'lib/plugins/plot/images/loading.gif" alt="mindmap" title="mindmap"';

        if($format == 'xhtml'){
			$R->doc .= $tpl . ' class="media' . $data['align'] . '"';
            if($data['width'])  $R->doc .= ' width="'.$data['width'].'"';
            if($data['height']) $R->doc .= ' height="'.$data['height'].'"';
            if($data['align'] == 'right') $R->doc .= ' align="right"';
            if($data['align'] == 'left')  $R->doc .= ' align="left"';
            $R->doc .= '/>';
            return true;
        }
        return false;
    }

	function getGUID(){  
		$charid = strtoupper(md5(uniqid(rand(), true)));  
		$hyphen = chr(45);// "-"  
		$uuid = "zxsq_mindmap_form-"  
			.substr($charid, 0, 8).$hyphen  
			.substr($charid, 8, 4).$hyphen  
			.substr($charid,12, 4).$hyphen  
			.substr($charid,16, 4).$hyphen  
			.substr($charid,20,12); 
		return $uuid;  
	} 
	
    /**
     * Render the output remotely at plot API
     */

	function _cht($data) {
		$notGv = array("markdown", "ditaa");
		if(in_array(explode(":", $data['layout'])[0], $notGv)) {
			$engine = $data['layout'];
		} else {
			$engine = "gv:" . $data['layout'];
		}
		return $engine;	
	}
}
