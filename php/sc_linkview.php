<?php

// This class handles the shortcode [linkview]
class sc_linkview {
	private static $instance;
	private $atts;
	private $slider_ids;
	private $slider_parameters;

	public static function &get_instance() {
		// Create class instance if required
		if( !isset( self::$instance ) ) {
			self::$instance = new sc_linkview();
		}
		// Return class instance
		return self::$instance;
	}

	private function __construct() {
		// Define all available attributes
		$this->atts = array(
			'view_type'      => array( 'section' => 'general',
			                           'val'     => 'list<br />slider',
			                           'std_val' => 'list',
			                           'desc'    => 'This attribute specifies how the links are displayed. The standard is to show the links in a list.<br />
			                                         The second option is to show the links in a slider. This normally only make sense if you show the images, but it is also possible to show the link name with this option.' ),

			'cat_name'       => array( 'section' => 'general',
			                           'val'     => 'Cat 1,Cat 2,...',
			                           'std_val' => '',
			                           'desc'    => 'This attribute specifies which categories should be shown. If you leave the attribute empty all categories are shown.<br />
			                                         If the cat_name has spaces, simply wrap the name in quotes.<br />
			                                         Example: <code>[linkview cat_name="Social Media"]</code><br />
			                                         If you want to define multiple categories you can give them in a list splitted by the delimiter ","<br />
			                                         Example: <code>[linkview cat_name="Blogroll,Social Media"]</code>' ),

			'exclude_cat'    => array( 'section' => 'general',
			                           'val'     => 'Cat 1,Cat 2,...',
			                           'std_val' => '',
			                           'desc'    => 'This attribute specifies which categories should be excluded. This attribute is only considered if the attribute "cat_name" is not set.<br />
			                                         If the cat_name has spaces, simply wrap the name in quotes.<br />
			                                         If you want to define multiple categories you can give them in a list splitted by the delimiter ","<br />
			                                         Example: <code>[linkview exclude_cat="Blogroll,Social Media"]</code>' ),

			'show_cat_name'  => array( 'section' => 'general',
			                           'val'     => '0 ... false<br />1 ... true',
			                           'std_val' => '1',
			                           'desc'    => 'This attribute specifies if the category name is shown as a headline.' ),

			'show_img'       => array( 'section' => 'general',
			                           'val'     => '0 ... false<br />1 ... true',
			                           'std_val' => '0',
			                           'desc'    => 'This attribute specifies if the image is displayed instead of the name. This attribute is only considered for links where an image was set.' ),

			'link_items'     => array( 'section' => 'general',
			                           'val'     => 'name<br />address<br />description<br />image<br />rss<br />notes<br />rating',
			                           'std_val' => '',
			                           'desc'    => 'The standard is to leave this option emtpy. Then only the link name or the link image (see attribute "show_img") is shown.<br />
			                                         If you enter one or more of the available items here you overwrite that standard-settings and you have the ability to modify the items and their order to your wishes.
			                                         Multiple items must be seperated by a semicolon, the items will be showed in the order you give them.' ),

			'vertical_align' => array( 'section' => 'general',
			                           'val'     => 'std<br />top<br />bottom<br />middle',
			                           'std_val' => 'std',
			                           'desc'    => 'This attribute specifies the vertical alignment of the links. Changing this attribute normally only make sense if the link-images are displayed.<br />
			                                         If you change this value you can for example modify the vertical alignment of the list symbol relativ to the image or the vertical alignment of images with different size in a slider.' ),

			'target'         => array( 'section' => 'general',
			                           'val'     => 'std<br />blank<br />top<br />none',
			                           'std_val' => 'std',
			                           'desc'    => 'Set one of the given values to overwrite the standard value which was set for the link.<br />
			                                         Set the attribute to "std" if you don´t want to overwrite the standard.' ),

			'class_suffix'   => array( 'section' => 'general',
			                           'val'     => 'string',
			                           'std_val' => '',
			                           'desc'    => 'This attribute sets the class suffix to allow different css settings for different link lists or sliders on the same site.<br />
			                                         The standard is an empty string which specifies that no specific suffix will be used.' ),

			'list_symbol'    => array( 'section' => 'list',
			                           'val'     => 'std<br />none<br />circle<br />square<br />disc',
			                           'std_val' => 'std',
			                           'desc'    => 'This attribute sets the style type of the list symbol.<br />
			                                         The standard value is "std", this means the standard type which is set in your theme will be used. Set one of the other values to overwrite this standard.<br />
			                                         A good example for the usage is to set the value to "none" for an image link list. The list symbols will be hidden which often looks better when images are used.' ),

			'slider_width'   => array( 'section' => 'slider',
			                           'val'     => 'Number',
			                           'std_val' => '0',
			                           'desc'    => 'This attribute sets the fixed width of the slider. If the attribute is set to 0 the width will be calculated automatically due to the given image sizes.<br />
			                                         This attribute is only considered if the view type "slider" is selected.' ),

			'slider_height'  => array( 'section' => 'slider',
			                           'val'     => 'Number',
			                           'std_val' => '0',
			                           'desc'    => 'This attribute sets the fixed height of the slider. If the attribute is set to 0 the height will be calculated automatically due to the given image sizes.<br />
			                                         This attribute is only considered if the view type "slider" is selected.' ),

			'slider_pause'   => array( 'section' => 'slider',
			                           'val'     => 'Number',
			                           'std_val' => '6000',
			                           'desc'    => 'This attribute sets the duration between the the slides in milliseconds. This is the time where you can see the link standing still before the next slide starts.<br />
			                                         This attribute is only considered if the view type "slider" is selected.' ),

			'slider_speed'   => array( 'section' => 'slider',
			                           'val'     => 'Number',
			                           'std_val' => '1000',
			                           'desc'    => 'This attribute sets the animation speed of the slider in milliseconds. This is the time used to slide from one link to the next one.<br />
			                                         This attribute is only considered if the view type "slider" is selected.' )
		);
		$this->slider_ids = NULL;
		$this->slider_parameters = NULL;
	}

	// main function to show the rendered HTML output
	public function show_html( $atts ) {
		// add leading "-" for css-suffix
		if( isset( $atts['class_suffix'] ) ) {
			$atts['class_suffix'] = '-'.$atts['class_suffix'];
		}

		// check attributes
		$std_values = array();
		foreach( $this->atts as $aname => $attribute ) {
			$std_values[$aname] = $attribute['std_val'];
		}
		$a = shortcode_atts( $std_values, $atts );

		// set categories
		$categories = $this->categories( $a );

		$out = '';
		foreach( $categories as $cat ) {
			// get links
			$args = array(
				'orderby'        => 'name',
				'limit'          => -1,
				'category_name'  => $cat->name);
			$links = get_bookmarks( $args );

			// generate output
			if( !empty( $links ) ) {
				$out .='
					<div class="lv-category'.$a['class_suffix'].'">';
				$out .= $this->html_category( $cat, $a );
				$list_id = $this->create_random_id();
				$slider_size = array( 0, 0 );
				if( 'slider' === $a['view_type'] ) {
					$this->slider_ids[] = $list_id;
					$slider_size = $this->slider_size( $a, $links );
					$out .= $this->html_slider_styles( $links, $a, $list_id, $slider_size );
				}
				$out .= $this->html_link_list( $links, $a, $list_id, $slider_size );
				$out .= '
					</div>';
			}
		}
		return $out;
	}

	public function get_atts( $section=NULL ) {
		if( NULL == $section ) {
			return $this->atts;
		}
		else {
			$atts = NULL;
			foreach( $this->atts as $aname => $attr ) {
				if( $attr['section'] === $section ) {
					$atts[$aname] = $attr;
				}
			}
			return $atts;
		}
	}

	public function get_slider_ids() {
		return $this->slider_ids;
	}

	private function categories( $a ) {
		$catarray = array();
		if( empty( $a['cat_name'] ) ) {
			$catarray = get_terms( 'link_category', 'orderby=name' );
			if( $a['exclude_cat'] != '' ) {
				$excludecat = array_map( 'trim', explode( ",", $a['exclude_cat'] ));
				$diff = Array();
				foreach( $catarray as $cat ) {
					if( array_search( $cat->name, $excludecat ) === false ) {
						array_push( $diff, $cat );
					}
				}
				$catarray = $diff;
				unset( $diff );
			}
		}
		else {
			$catnames = array_map( 'trim', explode( ",", $a['cat_name'] ));
			foreach( $catnames as $catname ) {
				if( get_term_by( 'name', $catname, 'link_category') != false )
				{
					array_push( $catarray, get_term_by( 'name', $catname, 'link_category' ) );
				}
			}
		}
		return $catarray;
	}

	private function slider_size( $a, $links ) {
		if(	$a['slider_width'] > 0 && $a['slider_height'] > 0 ) {
			$width = $a['slider_width'];
			$height = $a['slider_height'];
		}
		else {
			$width = 0;
			$height = 0;
			foreach( $links as $link ) {
				if( $a['show_img'] > 0 && $link->link_image != null ) {
					list( $w, $h ) = getimagesize( $link->link_image );
					$width = max( $width, $w );
					$height = max( $height, $h );
				}
			}
			$ratio = 1;
			if( $a['slider_width'] > 0 ) {
				$ratio = $a['slider_width'] / $width;
			}
			else if( $a['slider_height'] > 0 ) {
				$ratio = $a['slider_height'] / $height;
			}
			$width = round( $width * $ratio );
			$height = round( $height * $ratio );
			// If no image was in all links, set manual size
			if( !$width )
				$width = 300;
			if( !$height )
				$height = 30;
		}
		return array( $width, $height );
	}

	private function html_category( $cat, $a ) {
		$out = '';
		if( $a['show_cat_name'] > 0 ) {
			$out .= '
					<h2 class="lv-cat-name'.$a['class_suffix'].'">'.$cat->name.'</h2>';
		}
		return $out;
	}

	private function html_link_list( $links, $a, $list_id, $slider_size ) {
		$out = '
					<div id="'.$list_id.'">
					<ul class="lv-link-list'.$a['class_suffix'].'"';
		if( $a['list_symbol'] == 'none' || $a['list_symbol'] == 'circle' || $a['list_symbol'] == 'square' || $a['list_symbol'] == 'disc' ) {
			$out .= ' style="list-style-type:'.$a['list_symbol'].';"';
		}
		$out .= '>';
		foreach( $links as $link ) {
			$out .= '
						<li class="lv-list-item'.$a['class_suffix'].'"><div class="lv-link'.$a['class_suffix'].'"';
			if( 'slider' !== $a['view_type'] && ( 'top' === $a['vertical_align'] || 'middle' === $a['vertical_align'] || 'bottom' === $a['vertical_align'] ) ) {
				$out .= ' style="display:inline-block; vertical-align:'.$a['vertical_align'].';"';
			}
			$out .= '>';
			$out .= $this->html_link( $link, $a, $slider_size );
			$out .= '</div></li>';
		}
		$out .= '
					</ul>
					</div>';
		return $out;
	}

	private function html_slider_styles( $links, $a, $list_id, $slider_size ) {
		list( $slider_width, $slider_height ) = $slider_size;
		// prepare slider parameters which is used in footer script
		$this->slider_parameters[$list_id] = array( 'auto' => 'true',
		                                              'pause' => $a['slider_pause'],
		                                              'speed' => $a['slider_speed'],
		                                              'continuous' => 'true',
		                                              'controlsShow' => 'false' );
		// styles
		$out = '
			<style>
				#'.$list_id.' ul, #'.$list_id.' li {
					margin:0;
					padding:0;
					list-style:none;
				}
				#'.$list_id.' li {
					width: '.$slider_width.'px;
					height: '.$slider_height.'px;
					overflow: hidden;
					text-align: center;
				}
				#'.$list_id.' img {
					max-width: 100%;
				}';
		if( $a['vertical_align'] == 'top' || $a['vertical_align'] == 'middle' || $a['vertical_align'] == 'bottom' ) {
			$out .= '
				#'.$list_id.' .lv-link'.$a['class_suffix'].' {
					display: table-cell;
					text-align: center;
					vertical-align: '.$a['vertical_align'].';
					width: '.$slider_width.'px;
					height: '.$slider_height.'px;
				}
				#'.$list_id.' .lv-link'.$a['class_suffix'].' * {
					vertical-align: '.$a['vertical_align'].';
				}';
		}
		$out .= '
			</style>';
		return $out;
	}

	private function html_link( $l, $a, $slider_size ) {
		$out = '<a class="lv-anchor'.$a['class_suffix'].'" href="'.$l->link_url;

		if( $a['target'] == 'blank' || $a['target'] == 'top' || $a['target'] == 'none' ) {
			$target = '_'.$a['target'];
		}
		else {
			$target = $l->link_target;
			// set target to _none if an empty string was returned
			if( $target == '' )
				$target = '_none';
		}
		$out .= '" target="'.$target.'" title="'.$l->link_name;
		if( $l->link_description != "" ) {
			$out .= ' ('.$l->link_description.')';
		}
		$out .= '">';

		if( '' === $a['link_items'] ) {
			// simple style (name or image)
			if( $a['show_img'] > 0 && $l->link_image != null ) {
				// image
				$out .= $this->html_link_item($l, 'image', $a, $slider_size );
			}
			else {
				// name
				$out .= $this->html_link_item($l, 'name', $a, $slider_size );
			}
		}
		else {
			// enhanced style (all items given in link_items attribute)
			$items = explode( ',', $a['link_items'] );
			foreach( $items as $item ) {
				$out .= $this->html_link_item($l, $item, $a, $slider_size );
			}
		}
		$out .= '</a>';
		return $out;
	}

	private function html_link_item( $l, $item, $a, $slider_size ) {
		$out = '<div class="lv-item-'.$item.$a['class_suffix'].'">';
		switch( $item ) {
			case 'address':
				$out .= $l->link_url;
				break;
			case 'description':
				$out .= $l->link_description;
				break;
			case 'image':
				$out .= '<img src="'.$l->link_image.'"'.$this->html_img_size( $l->link_image, $slider_size[0], $slider_size[1] ).' alt="'.$l->link_name.'" />';
				break;
			case 'rss':
				$out .= $l->link_rss;
				break;
			case 'notes':
				$out .= $l->link_notes;
				break;
			case 'rating':
				$out .= $l->link_rating;
				break;
			default: // 'name'
				$out .= $l->link_name;
				break;
		}
		$out .= '</div>';
		return $out;
	}

	private function html_img_size( $image, $slider_width=0, $slider_height=0 ) {
		if( $slider_width <= 0 || $slider_height <= 0 ) {
			return '';
		}
		else {
			$slider_ratio = $slider_width / $slider_height;
			list( $img_width, $img_height ) = getimagesize( $image );
			$img_ratio = $img_width / $img_height;
			if( $slider_ratio > $img_ratio ) {
				$scale = $slider_height / $img_height;
			}
			else {
				$scale = $slider_width / $img_width;
			}
			return ' width="'.round($img_width*$scale).'px" height="'.round($img_height*$scale).'px"';
		}
	}

	private function create_random_id() {
		$id = mt_rand( 10000, 99999 );
		$id = 'lv-id-'.$id;
		return $id;
	}

	public function print_slider_script() {
		$out = '<script type="text/javascript">
				jQuery(document).ready(function(){';
		foreach( $this->slider_ids as $id ) {
			$out .= '
					jQuery("#'.$id.'").easySlider({';
			foreach( $this->slider_parameters[$id] as $param => $value ) {
				$out .= '
						'.$param.': '.$value.',';
			}
			$out .= '
					});';
		}
		$out .= '
				});
			</script>';
		echo $out;
	}
} // end class sc_linkview
?>
