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
      $renderer->doc .= $this->render_mob($mob);
    }

    return true;
  }

  private function decode($json)
  {
    return json_decode($json);
  }

  private function render_mob($mob)
  {
    return '<div class="mobber">' . $this->render_head($mob) . '</div>';
  }

  private function render_head($mob)
  {
    return
      '<div class="row head">' .
        $this->render_head_name($mob) .
        $this->render_head_level_role($mob) .
        $this->render_head_size_origin_type_keywords($mob) .
        $this->render_head_xp($mob) .
      '</div>';
  }

  private function render_head_name($mob)
  {
    return
      '<div class="value half name">' .
        $this->join_name($mob) .
      '</div>';
  }

  private function join_name($mob)
  {
    $name = '';
    
    if ($mob->{'name'}) {
      $name .= ucwords($mob->{'name'});
    }
    
    return $name;
  }

  private function render_head_level_role($mob)
  {
    return
      '<div class="value half level_role">' .
        $this->join_level($mob) .
        ' ' .
        $this->join_role($mob) .
      '</div>';
  }

  private function join_level($mob)
  {
    $level = '';
    
    if ($mob->{'level'}) {
      $level .= 'Level ' . $mob->{'level'};
    }
    
    return $level;
  }

  private function join_role($mob)
  {
    $role = '';

    if ($mob->{'elite'}) {
      $role .= ' Elite ';
    }

    if ($mob->{'solo'}) {
      $role .= ' Solo ';
    }
    
    if ($mob->{'role'}) {
      $role .= ' ' . ucwords($mob->{'role'}) . ' ';
    }
    
    if ($mob->{'leader'}) {
      $role .= ' (Leader) ';
    }
    
    return $role;
  }

  private function render_head_size_origin_type_keywords($mob)
  {
    return
      '<div class="value half size_origin_type_keywords">' .
        $this->join_size_origin_type_keywords($mob) .
      '</div>';
  }

  private function join_size_origin_type_keywords($mob)
  {
    $joined = '';
    
    if ($mob->{'size'}) {
      $joined .= ' ' . ucwords($mob->{'size'}) . ' ';
    }
    
    if ($mob->{'origin'}) {
      $joined .= ' ' . ucwords($mob->{'origin'}) . ' ';
    }

    if ($mob->{'type'}) {
      $joined .= ' ' . ucwords($mob->{'type'}) . ' ';
    }

    // if ($mob->{'keywords'} && count($mob->{'keywords'}) > 0) {
    //   $joined .= '(';
    //   for ($i = 0; $i < count($mob->{'keywords'}); $i++) {
    //     if ($i > 0) {
    //       $joined .= ', ';
    //     }
    //     $joined .= $mob->{'keywords'}[$i];
    //   }
    //   $joined .= ')';
    // }
    
    return $joined;
  }

  private function render_head_xp($mob)
  {
    return
      '<div class="value half xp">' .
        $this->join_xp($mob) .
      '</div>';
  }
  
  private function join_xp($mob)
  {
    $joined = '';
    
    if ($mob->{'xp'}) {
      $joined .= ' ' . $mob->{'xp'} . ' ';
    }
    
    return $joined;
  }
}
