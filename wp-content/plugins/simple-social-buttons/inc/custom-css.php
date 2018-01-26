 <style media="screen">

    <?php if ( isset( $this->selected_position['inline'] ) ): ?>
    .simplesocialbuttons.simplesocialbuttons_inline .ssb-fb-like {
      margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
    }
    <?php endif ?>
     /*inline margin*/
    <?php if ( 'sm-round' == $this->selected_theme && isset( $this->selected_position['inline'] ) ): ?>
    .simplesocialbuttons.simplesocialbuttons_inline.simplesocial-sm-round button{
      margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
    }
  <?php endif ?>

  <?php if ( 'simple-round' == $this->selected_theme && isset( $this->selected_position['inline'] ) ) : ?>
    .simplesocialbuttons.simplesocialbuttons_inline.simplesocial-simple-round button{
      margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
    }
  <?php endif ?>

  <?php if ( 'round-txt' == $this->selected_theme && isset( $this->selected_position['inline'] )  ) : ?>
    .simplesocialbuttons.simplesocialbuttons_inline.simplesocial-round-txt button{
      margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
    }
  <?php endif ?>

  <?php if ( 'round-btm-border' == $this->selected_theme && isset( $this->selected_position['inline'] ) ) : ?>
    .simplesocialbuttons.simplesocialbuttons_inline.simplesocial-round-btm-border button{
      margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
    }
  <?php endif ?>

  <?php if ( 'flat-button-border' == $this->selected_theme && isset( $this->selected_position['inline'] ) ) : ?>
    .simplesocialbuttons.simplesocialbuttons_inline.simplesocial-flat-button-border button{
      margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
    }
  <?php endif ?>

  <?php if ( 'round-icon' == $this->selected_theme && isset( $this->selected_position['inline'] ) ) : ?>
    .simplesocialbuttons.simplesocialbuttons_inline.simplesocial-round-icon button{
      margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
    }

  <?php endif ?>

     <?php if ( 'simple-icons' == $this->selected_theme && isset( $this->selected_position['inline'] ) ) : ?>
     .simplesocialbuttons.simplesocialbuttons_inline.simplesocial-simple-icons button{
         margin: <?php echo $this->inline_option['icon_space'] == '1' && $this->inline_option['icon_space_value'] != '' ? $this->inline_option['icon_space_value'] . 'px' : ''; ?>;
     }

     <?php endif ?>
     /*margin-digbar*/

  <?php if ( 'sm-round' == $this->selected_theme && isset( $this->selected_position['sidebar'] ) ) : ?>
    div[class*="simplesocialbuttons-float"].simplesocialbuttons.simplesocial-sm-round button{
      margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
    }
  <?php endif ?>

  <?php if ( 'simple-round' == $this->selected_theme && isset( $this->selected_position['sidebar'] ) ) : ?>
    div[class*="simplesocialbuttons-float"].simplesocialbuttons.simplesocial-simple-round button{
      margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
    }
  <?php endif ?>

  <?php if ( 'round-txt' == $this->selected_theme && isset( $this->selected_position['sidebar'] ) ) : ?>
  div[class*="simplesocialbuttons-float"].simplesocialbuttons.simplesocial-round-txt button{
    margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
  }
  <?php endif ?>

  <?php if ( 'round-btm-border' == $this->selected_theme && isset( $this->selected_position['sidebar'] ) ) : ?>
    div[class*="simplesocialbuttons-float"].simplesocialbuttons.simplesocial-round-btm-border button{
      margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
    }
  <?php endif ?>

 <?php if ( 'round-icon' == $this->selected_theme && isset( $this->selected_position['sidebar'] ) ) : ?>
   div[class*="simplesocialbuttons-float"].simplesocialbuttons.simplesocial-round-icon button{
     margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
   }
 <?php endif ?>

   <?php if ( 'simple-icons' == $this->selected_theme && isset( $this->selected_position['sidebar'] ) ) : ?>
   div[class*="simplesocialbuttons-float"].simplesocialbuttons.simplesocial-simple-icons button{
       margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
   }
   div[class*="simplesocialbuttons-float"].simplesocialbuttons.simplesocial-simple-icons .ssb-fb-like{
       margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
   }
   <?php endif ?>

   <?php if ( isset( $this->selected_position['sidebar'] ) && $this->sidebar_option['icon_space'] == '1' ) : ?>
   div[class*="simplesocialbuttons-float"].simplesocialbuttons .ssb-fb-like{
       margin: <?php echo $this->sidebar_option['icon_space'] == '1' && $this->sidebar_option['icon_space_value'] != '' ? $this->sidebar_option['icon_space_value'] . 'px 0' : ''; ?>;
   }
   <?php endif ?>


</style>
