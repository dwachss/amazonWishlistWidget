<?php
/*
Plugin Name: Amazon Wish List Hack
Plugin URI: https://github.com/dwachss/amazonWishlistWidget
Description: Since Amazon doesn't support getting wishlists anymore, uses screen scraping to grab my wishlist and display it.
Author: Daniel Wachsstock
Version: 2.1
Author URI: http://bililite.com/blog
*/ 

// Wordpress 2.8 Widget code based on http://jessealtman.com/2009/06/08/tutorial-wordpress-28-widget-api/ and http://justcoded.com/article/wordpress-28-multi-widgets/

class Amazon_Wishlist_Hack extends WP_Widget{
  function Amazon_Wishlist_Hack(){
    $this->WP_Widget ('amazonwishlisthack', __('My Amazon Wish List'), array(
     'description' => 'Screen scraping Amazon Wishlist widget'
    ));
  } // constructor
  function widget ($args, $instance){
    extract ($args, EXTR_SKIP);
    extract ($instance, EXTR_SKIP);
    echo $before_widget.$before_title.$title.$after_title;
    echo $this->wishlist($listid, $filter, $tag, $size, $count);
    echo $after_widget;
  } // widget display code
  function update ($new, $old){
    $count = $new['count'];
    if ($count <= 0) $count = 1;
    $new['count'] = $count;
    return array_merge ($old, $new);
  } // options update
  function form ($instance){
    extract ($instance, EXTR_SKIP);
    $this->textElement ('title', 'Title', $title);
    echo '<p><label>Items to show <select name="'.$this->get_field_name('count').'">';
    for ($i = 1; $i <= 10; ++$i){
      echo "<option value=\"$i\"";
      if ($i == $count) echo ' selected="selected"';
      echo ">$i</option>";
    }
    echo '</select></label></p>';
    $this->textElement ('listid', 'Wishlist ID', $listid);
    echo '<p><label>Filter <select name="'.$this->get_field_name('filter').'">';
    $this->optionElement('all', $filter, 'All Products');
    $this->optionElement('3', $filter, 'Books');
    $this->optionElement('94', $filter, 'DVD');
    $this->optionElement('58', $filter, 'Home Improvement');
    $this->optionElement('206', $filter, 'Jewelry');
    $this->optionElement('82', $filter, 'Kitchen');
    $this->optionElement('31', $filter, 'Toys');
    echo '</select></label></p>';
    $this->textElement ('tag', 'Tag', $tag);
    $this->textElement ('size', 'Image Size', $size);
  } // control form
  function textElement ($index, $text, $value){
    $id = $this->get_field_id($index);
    $name = $this->get_field_name ($index);
    $text = __($text); // localize
    echo "<p><label for=\"$id\">$text <input name=\"$name\" class=\"widefat\" value=\"$value\" id=\"$id\" /></label></p>";
  } // textElement
  function optionElement($value, $filter, $text){
    echo "<option value=\"$value\" ";
    if ($filter == $value) echo 'selected="selected"';
    echo ">$text</option>";
  }  // optionElement
	
	// see https://github.com/doitlikejustin/amazon-wish-lister for other ways of doing this
  function wishlist($listID, $filter, $tag, $size, $n){
    // the screen scraping
    $ret = "<p><a href='http://www.amazon.com/gp/registry/wishlist/$listID?tag=$tag'>See the whole list</a></p>";
		$items = $this->getwishlistitems($listID, $filter);
    shuffle($items);
    $items = array_slice ($items, 0, $n);
		foreach ($items as $item){
			$link = $this->itemxpath($item, ".//a[starts-with(@id, 'itemName')]")->item(0);
			$href = $link->attributes->getNamedItem('href')->nodeValue;
			if (preg_match ('|/dp/\w+|', $href, $matches)){
				$href = "http://amazon.com$matches[0]?tag=$tag";
			}else{
				$href = "http://amazon.com$href";
			}
			$title = $link->textContent;
			$author = $link->parentNode->nextSibling->textContent;
			if ($size){
				$image = $this->itemxpath($item,"string(.//img[1]/@src)");
				if (preg_match ('|http://ecx.images-amazon.com/images/I/[^.]+|', $image, $matches)){
					$image = $matches[0]."._SL$size.jpg";
				}else{
				$image = "http://ecx.images-amazon.com/images/G/01/x-site/icons/no-img-sm._SL${size}_.jpg";
				}
				$image = "<img src='$image' alt='$title'><br/>";
			}
			$ret .= "<a href='$href'>$image$title<br/>$author<br/><br/></a>";
		}
    $ret .= "<p><a href='http://www.amazon.com/?_encoding=UTF8&amp;tag=$tag&amp;linkCode=ur2&amp;camp=1789&amp;creative=390957'>Shop On Amazon</a></p>";
		return $ret;

  } // wishlist
	function getwishlistitems ($listID, $filter, $page=1){
		// ignore parsing warnings
		$wishlistdom = new DOMDocument();
		@$wishlistdom->loadHTMLFile("http://www.amazon.com/gp/registry/wishlist/$listID?disableNav=1&filter=$filter&page=$page");
		$wishlistxpath = new DOMXPath ($wishlistdom);
		$items = iterator_to_array($wishlistxpath->query("//div[starts-with(@id,'item_')]"));
		if ($wishlistxpath->evaluate("count(//li[@class='a-last'])")) { // this is the "Next->" button
			$items = array_merge($items, $this->getwishlistitems($listID, $filter, $page+1));
		}
		return $items;
	}
	function itemxpath ($node, $xpath){
		return (new DOMXPath($node->ownerDocument))->evaluate($xpath, $node);
	}
} // class Amazon_Wishlist_Hack


add_action ('widgets_init', create_function('', 'return register_widget("Amazon_Wishlist_Hack");'));
?>
