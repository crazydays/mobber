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
    return 'normal';
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
    return
      '<div class="mobber">' .
        $this->render_head($mob) .
        $this->render_body($mob) .
      '</div>';
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
    return $this->join($mob, 'name');
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
    return 'Level' . $this->join($mob, 'level');
  }

  private function join_role($mob)
  {
    $role = '';

    if ($mob->{'elite'}) {
      $role .= 'Elite';
    }

    if ($mob->{'solo'}) {
      $role .= 'Solo';
    }
    
    if ($mob->{'role'}) {
      $role .= $this->join($mob, 'role');
    }
    
    if ($mob->{'leader'}) {
      $role .= '(Leader)';
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
    $joined = $this->join($mob, 'size') . $this->join($mob, 'origin') . $this->join($mob, 'type');
    
    if ($mob->{'keywords'} && count($mob->{'keywords'}) > 0) {
      $joined .= '(';
      for ($i = 0; $i < count($mob->{'keywords'}); $i++) {
        if ($i > 0) {
          $joined .= ', ';
        }
        $joined .= ucwords($mob->{'keywords'}[$i]);
      }
      $joined .= ')';
    }
    
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
    return $this->join($mob, 'xp', true, true);
  }
  
  private function render_body($mob)
  {
    return
      $this->render_initiative_senses($mob) .
      $this->render_auras($mob) .
      $this->render_hitpoints_bloodied($mob) .
      $this->render_defenses($mob) .
      $this->render_immune($mob);
  }

  private function render_initiative_senses($mob)
  {
    return
     '<div class="row">' .
       $this->render_initiative($mob) .
       $this->render_senses($mob) .
     '</div>';
  }

  private function render_initiative($mob)
  {
    if ($mob->{'initiative'}) {
      return
        '<div class="group initiative">' .
          '<div class="label">Initiative</div>' .
          '<div class="value">' .
            $this->join_initiative($mob) .
          '</div>' .
        '</div>';
    } else {
      return '';
    }
  }

  private function join_initiative($mob)
  {
    return $this->join($mob, 'initiative', true, false);
  }
  
  private function render_senses($mob)
  {
    $senses = $mob->{'senses'};
    
    if ($senses) {
      
      return
        '<div class="group senses">' .
          '<div class="label"> Senses </div>' .
          $this->render_perception($senses) .
          $this->render_senses_special($senses) .
        '</div>';
    } else {
      return '';
    }
  }
  
  private function render_perception($senses)
  {
    if ($senses->{'perception'}) {
      return
        '<div class="value">Perception' .
          $this->join_perception($senses) .
        '</div>';
    } else {
      return '';
    }
  }

  private function join_perception($senses)
  {
    return $this->join($senses, 'perception', true, false);
  }

  private function render_senses_special($senses)
  {
    if ($senses->{'special'} && count($senses->{'special'}) > 0) {
      return
        '<div class="value">' .
          $this->join_array($senses, 'special', true) .
        '</div>';
    } else {
      return '';
    }
  }
  
  private function render_auras($mob)
  {
    $auras = $mob->{'auras'};
    
    if ($auras && count($auras) > 0) {
      $joined = '';

      foreach ($auras as $aura) {
        $joined .= $this->render_aura($aura);
      }

      return $joined;
    } else {
      return '';
    }
  }

  private function render_aura($aura)
  {
    $joined = '';
    $joined .= '<div class="row">';
    $joined .= '<div class="group aura">';
    
    if ($aura->{'name'}) {
      $joined .= '<div class="label">' . $this->join($aura, 'name') . '</div>';
    }

    if ($aura->{'keywords'}) {
      $joined .= '<div class="value"> (' . $this->join_array($aura, 'keywords') . ')</div>';
    }

    if ($aura->{'range'}) {
      $joined .= '<div class="value"> Aura' . $this->join($aura, 'range', true, false) . '</div>';
    }

    if ($aura->{'description'}) {
      $joined .= '<div class="value">' . $this->join($aura, 'description', true, false, false) . '</div>';
    }
    
    $joined .= '</div>';
    $joined .= '</div>';
    
    return $joined;
  }
  
  private function render_hitpoints_bloodied($mob)
  {
    if ($mob->{'hitpoints'} || $mob->{'bloodied'}) {
      return
        '<div class="row">' .
          $this->render_hitpoints($mob) .
          $this->render_bloodied($mob) .
        '</div>';
    } else {
      return '';
    }
  }

  private function render_hitpoints($mob)
  {
    return $this->render_value($mob, 'hitpoints', 'HP');
  }

  private function render_bloodied($mob)
  {
    return $this->render_value($mob, 'bloodied', 'Bloodied');
  }
  
  private function render_defenses($mob)
  {
    $defenses = $mob->{'defenses'};
    if ($defenses) {
      return
        '<div class="row">' .
          $this->render_armorclass($defenses) .
          $this->render_fortitude($defenses) .
          $this->render_reflex($defenses) .
          $this->render_will($defenses) .
        '</div>';
    } else {
      return '';
    }
  }

  private function render_armorclass($defenses)
  {
    return $this->render_value($defenses, 'armorclass', 'AC');
  }

  private function render_fortitude($defenses)
  {
    return $this->render_value($defenses, 'fortitude', 'Fortitude');
  }

  private function render_reflex($defenses)
  {
    return $this->render_value($defenses, 'reflex', 'Reflex');
  }

  private function render_will($defenses)
  {
    return $this->render_value($defenses, 'will', 'Will');
  }

  private function render_value($json, $key, $label)
  {
    if ($json->{$key}) {
      return
        '<div class="group ' . $key . '">' .
          '<div class="label"> ' . $label . ' </div>' .
          '<div class="value">' . $this->join($json, $key, true, false) . '</div>' .
        '</div>';
    } else {
      return '';
    }
  }

  private function render_immune($mob)
  {
    if ($mob->{'immune'} && count($mob->{'immune'})) {
      return
        '<div class="row">' .
          '<div class="group immune">' .
            '<div class="label">Immune</div>' .
            '<div class="value"> ' . $this->join_array($mob, 'immune') . '</div>' . 
          '</div>' .
        '</div>';
    } else {
      return '';
    }
  }

  private function join($json, $key, $padleft = true, $padright = true, $ucwords = true)
  {
    if ($json->{$key}) {
      return
        ($padleft ? ' ' : '') .
        ($ucwords ? ucwords($json->{$key}) : $json->{$key}) .
        ($padright ? ' ' : '');
    } else {
      return '';
    }
  }
  
  private function join_array($json, $key, $padleft = false, $padright = false, $ucwords = true)
  {
    if ($json->{$key} && count($json->{$key}) > 0) {
      $joined = ($padleft ? ' ' : '');

      for ($i = 0; $i < count($json->{$key}); $i++) {
        if ($i > 0) {
          $joined .= ', ';
        }
        
        $joined .= ($ucwords ? ucwords($json->{$key}[$i]) : $json->{$key}[$i]);
      }

      $joined .= ($padright ? ' ' : '');
      return $joined;
    } else {
      return '';
    }
  }
}
