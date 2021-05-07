<?php
class metafad_common_views_renderer_ShortField extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
    {
      if($value != null && is_array($value) && sizeof($value) > 1)
      {
        $string = '';
        $count = 1;
        foreach ($value as $v) {
          if($count == sizeof($value) && $count > 2)
          {
            if(preg_match('/\\d/', $v))
            {
              $string .= '-';
            }
          }
          $string .= $v;
          $count++;
        }
      }
      else
      {
        $string = is_array($value) ? $value[0] : $value;
      }
      $pp = (strlen($string) >= 50) ? '...' : '';
      
      return mb_substr($string,0,50,'UTF-8').$pp;
    }
}
