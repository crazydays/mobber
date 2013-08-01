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
        $this->render_powers($mob) .
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
    return $this->join($mob, 'name', false, false);
  }

  private function render_head_level_role($mob)
  {
    return
      '<div class="value half level_role">' .
        $this->join_level($mob) .
        $this->join_role($mob) .
      '</div>';
  }

  private function join_level($mob)
  {
    return 'Level' . $this->join($mob, 'level', false);
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
      $role .= $this->join($mob, 'role', true, true);
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
    $joined =
      $this->join($mob, 'size', false, false) .
      $this->join($mob, 'origin', true, false) .
      $this->join($mob, 'type');
    
    if ($mob->{'keywords'} && count($mob->{'keywords'}) > 0) {
      $joined .= '(' . $this->join_array($mob, 'keywords') . ')';
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
    return $this->join($mob, 'xp', true, false);
  }
  
  private function render_body($mob)
  {
    return
      $this->render_initiative_senses($mob) .
      $this->render_auras($mob) .
      $this->render_hitpoints_bloodied($mob) .
      $this->render_defenses($mob) .
      $this->render_immune($mob) .
      $this->render_resist($mob) .
      $this->render_vulnerable($mob) .
      $this->render_saving_throws($mob) .
      $this->render_speed($mob) .
      $this->render_action_points($mob);
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
          '<div class="label">Senses</div>' .
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
      $joined .= '<div class="label">' . $this->join($aura, 'name', false) . '</div>';
    }

    if ($aura->{'keywords'}) {
      $joined .= '<div class="value">(' . $this->join_array($aura, 'keywords') . ')</div>';
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
          '<div class="label"> ' . $label . '</div>' .
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

  private function render_resist($mob)
  {
    $resists = $mob->{'resist'};
    if ($resists && count($resists) > 0) {
      $joined = '';
      $joined .= '<div class="row">';
      $joined .= '<div class="group resist">';
      $joined .= '<div class="label">Resist</div>';
      
      foreach ($resists as $resist) {
        $joined .= '<div class="value">' . $this->join_resist($resist) . '</div>';

        if ($resist->{'description'}) {
          $joined .= '<div class="value">' . $this->join($resist, 'description', true, false, false) . '</div>';
        }
      }
      
      $joined .= '</div>';
      $joined .= '</div>';
      
      return $joined;
    } else {
      return '';
    }
  }

  private function join_resist($resist)
  {
    return $this->join($resist, 'value', true, false) . $this->join($resist, 'type', true, false);
  }

  private function render_vulnerable($mob)
  {
    $vunlerables = $mob->{'vulnerable'};
    if ($vunlerables && count($vunlerables) > 0) {
      $joined = '';
      $joined .= '<div class="row">';
      $joined .= '<div class="group vunlerable">';
      $joined .= '<div class="label">Vunlerable</div>';
      
      foreach ($vunlerables as $vunlerable) {
        $joined .= '<div class="value">' . $this->join_vunlerable($vunlerable) . '</div>';
        
        if ($vunlerable->{'description'}) {
          $joined .= '<div class="value">' . $this->join($vunlerable, 'description', true, false, false) . '</div>';
        }
      }
      
      $joined .= '</div>';
      $joined .= '</div>';
      
      return $joined;
    } else {
      return '';
    }
  }

  private function join_vunlerable($vunlerable)
  {
    return
      $this->join($vunlerable, 'value', true, false) . $this->join($vunlerable, 'type', true, false);
  }

  private function render_saving_throws($mob)
  {
    if ($mob->{'saving_throws'}) {
      return
        '<div class="row">' .
          $this->render_value($mob, 'saving_throws', 'Saving Throws') .
        '</div>';
    } else {
      return '';
    }
  }

  private function render_speed($mob)
  {
    if ($mob->{'speed'}) {
      return
        '<div class="row">' .
          $this->render_value($mob, 'speed', 'Speed') .
        '</div>';
    } else {
      return '';
    }
  }

  private function render_action_points($mob)
  {
    if ($mob->{'action_points'}) {
      return
        '<div class="row">' .
          $this->render_value($mob, 'action_points', 'Action Points') .
        '</div>';
    } else {
      return '';
    }
  }

  private function render_powers($mob)
  {
    $powers = $mob->{'powers'};
    
    if ($powers && count($powers) > 0) {
      $joined = '';
      
      foreach ($powers as $power) {
        $joined .= $this->render_power($power);
      }
      
      return $joined;
    } else {
      return '';
    }
  }

  private function render_power($power)
  {
    $joined = '';
    $joined .= '<div class="row power">';
    $joined .= '<div class="shade">';
    
    if ($power->{'name'}) {
      $joined .= '<div class="label">' . $this->join($power, 'name', false, true) . '</div>';
    }

    if ($power->{'action'} || $power->{'recharge'}) {
      $joined .= '<div class="value">(';
      $joined .= $this->join($power, 'action', false, false);
      if ($power->{'action'} || $power->{'recharge'}) {
        $joined .= ', ';
      }
      $joined .= $this->join($power, 'recharge', false, false, false);
      $joined .= ')</div>';
    }
    
    if ($power->{'keywords'} && count($power->{'keywords'}) > 0) {
      $joined .= '<div class="value">';
      $joined .= $this->join_array($power, 'keywords', true);
      $joined .= '</div>';
    }
    
    $joined .= '</div>'; // shade
    
    $joined .= '<div class="value">';

    if ($power->{'range'}) {
      $joined .= $this->render_range($power->{'range'});
    }
    
    //           Reach 3; +26 vs AC; 2d8 + 9 damage. If the attack reduces a 
    // humanoid living target to 0 hit points or fewer, the target disappears 
    // and becomes a soulspiked spirit impaled on the devourer (see soulspiked 
    // spirit).
    //         </div>
    $joined .= 'placeholder';
    $joined .= '</div>';
    $joined .= '</div>'; // row

    return $joined;
  }

  private function render_range($range)
  {
    $joined = '';

    if ($range->{'reach'}) {
      $joined .= $this->render_value($range, 'reach', 'Reach');
    }

    if ($range->{'range'}) {
      $joined .= $this->render_value($range, 'range', 'Range');
    }

    if ($range->{'area_burst'}) {
      $joined .= $this->render_value($range, 'area_burst', 'Area burst');
    }

    if ($range->{'close_burst'}) {
      $joined .= $this->render_value($range, 'close_burst', 'Close burst');
    }

    if ($range->{'close_blast'}) {
      $joined .= $this->render_value($range, 'close_blast', 'Close blast');
    }

    return $joined;
  }

  private function join($json, $key, $padleft = true, $padright = true, $ucwords = true)
  {
    if ($json->{$key}) {
      return
        ($padleft ? '&nbsp;' : '') .
        ($ucwords ? ucwords($json->{$key}) : $json->{$key}) .
        ($padright ? '&nbsp;' : '');
    } else {
      return '';
    }
  }
  
  private function join_array($json, $key, $padleft = false, $padright = false, $ucwords = true)
  {
    if ($json->{$key} && count($json->{$key}) > 0) {
      $joined = ($padleft ? '&nbsp;' : '');

      for ($i = 0; $i < count($json->{$key}); $i++) {
        if ($i > 0) {
          $joined .= ',&nbsp;';
        }
        
        $joined .= ($ucwords ? ucwords($json->{$key}[$i]) : $json->{$key}[$i]);
      }

      $joined .= ($padright ? '&nbsp;' : '');
      return $joined;
    } else {
      return '';
    }
  }
}
