<?php

/**
 * DokuWiki Plugin mobber (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Aaron <daya-mobber@crazydays.org>
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_mobber
  extends DokuWiki_Syntax_Plugin
{
  public function getType()
  {
    return 'substition';
  }

  public function getPType()
  {
    return 'block';
  }

  public function getSort()
  {
    return 98;
  }

  public function connectTo($mode)
  {
    $this->Lexer->addSpecialPattern('--mobber.*?mobber--',$mode,'plugin_mobber');
  }

  public function handle($match, $state, $pos, &$handler)
  {
    $match = substr($match,8,-8);
    return array(strtolower($match));
  }

  public function render($mode, &$renderer, $data)
  {
    if($mode != 'xhtml') return false;

    $mob = $this->decode($data[0]);
    if ($mob == null) {
      $renderer->doc .= '<div>Unable to parse: ' . $data[0] . '</div>';
    } else {
      $renderer->doc .=  '<div>' . var_dump($mob) . '</div>';
    }

    return true;
  }

  private function decode($json)
  {
    return json_decode($json, true);
  }
  
  private function render_mob($mob)
  {
    return '<div class="mobber">' . $mob->{'name'} . '</div>';
  }
  
  private function render_head($mob)
  {
    return  '<div class="row head">' .
            $this->render_head_name($mob) .
            $this->render_head_level_role($mob) .
            $this->render_head_size_origin_type_keywords($mob) .
            $this->render_head_xp($mob) . '</div>';
  }

  private function render_head_name($mob)
  {
    return '<div class="value half name">' . $mob{'name'} . '</div>';
  }
  
  private function render_head_level_role($mob)
  {
    return '<div class="value half level_role">Level ' .
           $mob{'level'} . 'Elite Soldier (' . $mob{'role'} . ')' . '</div>';
  }
  
  private function render_head_size_origin_type_keywords($mob)
  {
    return '<div class="value half size_origin_type_keywords">Huge shadow humanoid (undead)</div>';
  }
  
  private function render_head_xp($mob)
  {
    return '<div class="value half xp">' . $mob{'xp'} . '</div>';
  }
}
