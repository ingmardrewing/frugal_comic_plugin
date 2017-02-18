<?php

class FcpFormats {

  public static function js(){
    return <<<'PRELOAD'
<script language='javascript'>
jQuery(window).load(function(){
var img = jQuery('<img>')[0];
img.src = "%s";
});
</script>
PRELOAD;
  }

  public static function get_comicpage_html(){
    return '%s<a href="%s" rel="next">%s</a>%s<p>%s</p>';
  }

  public static function get_navi_format(){
    return '<div id="stripnav" style="width: 100%%;text-align:right; margin-bottom:80px;"><ul>%s%s%s%s</div>';
  }

  public static function get_listelem_outer_format(){
    return '<li style="list-style-type:none;float:right;margin-right:10px;">%s</li>';
  }

  public static function get_listelem_inner_format () {
    return '<a href="%s">%s</a>';
  }

  public static function get_inner_custom_box_format(){
    return <<<'INNER_CUSTOM_BOX'
<label for="fcp_field">Comic Image URI for Post ID %s</label>
<input name="fcp_field" id="fcp_field" class="postbox" value="%s" style="width:100%%;" />
INNER_CUSTOM_BOX;
  }

  public static function get_next_and_prefetch_headerlinks(){
    return <<<'HEADERLINKS2'
<link ref="next" title="%s" href="%s" />
<link ref="prefetch" title="%s" href="%s" />
HEADERLINKS2;
  }

  public static function headerlinks(){
    return <<<'HEADERLINKS'
<link ref="start" title="%s" href="%s" />
<link ref="prev" title="%s" href="%s" />
%s
%s
HEADERLINKS;
  }

  public static function get_settings_editing_html(){
    return <<<'SETTINGS_EDITING'
%s
<div class="wrap">
  <h2>%s</h2>
  <form name="form1" method="post" action="">
    <input type="hidden" name="%s" value="Y">
    <p>
      <label for="%s">%s</label>
      <input type="text" name="%s" value="%s" size="20">
    </p>
    <hr />
    <p>
      <label for="%s">%s</label>
      <input type="text" name="%s" value="%s" size="20">
      </p>
      <hr />
    <p class="submit">
      <input type="submit" name="Submit" class="button-primary" value="%s">
    </p>
  </form>
</div>
SETTINGS_EDITING;
  }

  public static function get_setting_saved_format(){
    return '<div class="updated"><p><strong>%s</strong></p></div>';
  }

}
