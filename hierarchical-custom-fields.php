<?php
/*
Plugin Name: Hierarchical  Custom Fields
Description: This plugin will allow a user to add/edit/delete parent and child custom fields within a hierarchical structure.
Version: 1.0
Author: Chris J. Sanders
Author URI: http://www.cjsand.com
License: GNLv3

   This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//  1) Initiate the plugin...setup the database, etc

// if a user tries to activate the plugin, this will be invoked
register_activation_hook(__FILE__,'my_plugin_install');

// after the hook is invoked, it will seek this function
function my_plugin_install () {
   
	global $wpdb;

   $plugin_table = $wpdb->prefix . "plugin_HeirarchyElement";

	if($wpdb->get_var("SHOW TABLES LIKE '$plugin_table'") != $plugin_table) 
	{
		$sql = "CREATE TABLE " . $plugin_table . " (
		  CustomH_Id mediumint(9) NOT NULL AUTO_INCREMENT,
		  CustomH_Name tinytext NOT NULL,
		  CustomH_Parent mediumint(9),
		  UNIQUE KEY CustomH_Id (CustomH_Id)
		);";
	
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		// name for a new parent element
		$name = "Default Parent";
	
		// insert the new parent element into the table
	  	$rows_affected = $wpdb->insert( $plugin_table, array( 'CustomH_Name' => $name) );
	  
	}
}

//  2) Let the user change this data 

// first, create the hook
add_action('admin_menu', 'hCustomFields_menu');


// next, the function that will be called when the hook is invoked
function hCustomFields_menu() {
	
// who is allowed to view the menu
$allowed_group = 'manage_options';

// add the top-level menu
add_menu_page(__('Hierarchical Menu Admin','hCustomFields'), __('Hierarchical Custom Fields','hCustomFields'),$allowed_group,'hCustomFields','menu_page_display');

}

function menu_page_display()
{
	//must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    global $wpdb;
    
    // if the user wanted to delete a parent element
    if( isset($_POST[ 'deleteElement' ])) 
    {
        // Read their posted value
        $elementID = $_POST[ 'elementID' ];
        $elementName = $_POST[ 'elementName' ];
        $isParent = $_POST['isParent'];
        
        // Save the posted value in the database
        $wpdb->query("DELETE FROM ".$wpdb->prefix."plugin_HeirarchyElement WHERE CustomH_Id='".$elementID."'");
		
        // if a parent, delete all secondary elements associated with that element
        if ($isParent=='true')
        {
	        $wpdb->query("DELETE FROM ".$wpdb->prefix."plugin_HeirarchyElement WHERE CustomH_Parent='".$elementID."'");
        };

        // Put an settings updated message on the screen
        echo "<div class='updated'><p><strong>";
        echo _e("Element '".$elementName."' Deleted.", 'hCustomFields');
        echo "</strong></p></div>";
	}
    
    // if the user created a parent element
    if( isset($_POST[ 'Submit1st' ])) 
    {
        // Read their posted value
        $newTopElement = $_POST[ 'newTopLevelName' ];
        
        // Save the posted value in the database
        $wpdb->query("INSERT INTO ".$wpdb->prefix."plugin_HeirarchyElement (CustomH_Name) VALUES ('".$newTopElement."')");


        // Put an settings updated message on the screen
        echo "<div class='updated'><p><strong>";
        echo _e("New Parent Element '".$newTopElement."' Saved.", 'hCustomFields');
        echo "</strong></p></div>";
	}
	  
	// if the user created a secondary element
	if( isset($_POST[ 'Submit2nd' ]))
	{
		// Read their posted value
        $newSecondElement = $_POST[ 'newSecondaryLevelName' ];
        $parentID = $_POST[ 'parentID' ];
        
        // Save the posted value in the database
        $wpdb->query("INSERT INTO ".$wpdb->prefix."plugin_HeirarchyElement (CustomH_Name,CustomH_Parent) VALUES ('".$newSecondElement."','".$parentID."')");

        // Put an settings updated message on the screen
		echo "<div class='updated'><p><strong>";
        echo _e("New Secondary Element '".$newSecondElement."' Saved.", 'hCustomFields');
        echo "</strong></p></div>";
		
	} 
	
	    // Now display the settings editing screen
	
	    echo '<div class="wrap">';
	
	    // header
	
	    echo "<h2>" . __( 'Hierarchical Custom Field Elements Admin', 'hCustomFields' ) . "</h2>";
	
	    // settings form
	    
	    ?>
	<div align="center">
	<table style="text-align:center;margin-left:auto;margin-right:auto;">
	<tr>
		<td>
			<table style="text-align:center;">
				<tr>
					<td>
						<b>Top Level Elements</b>
					</td>
					<td>
						&nbsp
					</td>
				</tr>
				<tr>
					<td> 
						&nbsp
					</td>
					<td> 
						&nbsp
					</td>
				</tr>
				
					<?php include 'primaryElementsData.php';?>
				
					<tr>
						<td>
							&nbsp
						</td>
						<td>
							&nbsp
						</td>
					</tr>
					
				</table>
				
				<form action="" method="POST" name="enterNewTop">
				<table style="text-align:center;">
					<tr>
						<td>
							<input type="text" name="newTopLevelName" />
						</td>
					</tr>
					<tr>
						<td>
							<input type="submit" name="Submit1st" class="button-primary" value="<?php esc_attr_e('Add 1st Level Element') ?>" />
						</td>
					</tr>
				</table>
				</form>
			</td>
			<td valign="top">
				<div id='showdata'>
					<?php include 'secondaryElementsData.php';?>
				</div>
			</td>
			</tr>	
		</table>
		</div>
	
	</form>
	
	<p style="font-weight:bold;">Note: Deleting a parent element will also delete all secondary elements associated with that parent element!</p>
	</div>
<?php
}

//  3) Pull the data into a custom fields box for each post and let the user select the appropriate records

/* Define the custom box */
add_action('add_meta_boxes', 'hCustomFields_add_box');

/* Adds a box to the main column on the Post and Page edit screens */
function hCustomFields_add_box() {
    add_meta_box( 'hCustomFields_sectionid', __( 'Hierarchical Custom Fields', 'hCustomFields_textdomain' ), 
                'hCustomFields_inner_custom_box', 'post' );
}

/* Prints the box content */
function hCustomFields_inner_custom_box() {

  // for ajax
	
  // wordpress might already load the jQuery
  //<script type="text/javascript" src="jquery.min.js" ></script>

  
  global $post;
  
  // The actual fields for data entry
  echo '<label for="hCustomFields_parent_element">' . __("Parent: ", 'hCustomFields_textdomain' ) . '</label> ';
  
	//declare global wpdb
	global $wpdb;
  
  	// get values from plugin_HeirarchyElement table where there is no parent (implies only parent elements)
	$array1 = $wpdb->get_results('SELECT CustomH_Id, CustomH_Name FROM '.$wpdb->prefix.'plugin_HeirarchyElement WHERE (CustomH_Parent IS null) ORDER BY CustomH_Name');
	
	// get the values of the previously selected parent element (parent if CustomH_Parent is null)
	$metaArray = $wpdb->get_row("SELECT CustomH_Id, CustomH_Name FROM ".$wpdb->prefix."plugin_HeirarchyElement, ".$wpdb->prefix."postmeta WHERE ((CustomH_Id = meta_value) AND (post_ID='".$post->ID."') AND (meta_key = 'Parent_Element'))");
	
	// create selector
	?>
	<select name="parentSelect" id="parentSelect" onChange=populate_Select(this.options[this.selectedIndex].value,<?php echo $post->ID;?>)>
		<?php
		// if the array holds a previously selected element, display it
		if (!(empty($metaArray)))
		{
			echo "<option value='".$metaArray->CustomH_Id."' SELECTED>".$metaArray->CustomH_Name."</option>";
			echo "<SCRIPT LANGUAGE='javascript'>populate_Select(".$metaArray->CustomH_Id.",".$post->ID.");</script>";
		}
		else
		{
		?>
			<option value="-1" SELECTED>None Selected</option>
		<?php
		};
						
	// For each key of the array assign variable name "topID"
	// For each value of the array assign variable name "topName".
	foreach($array1 as $element)
	{
		$topID = $element->CustomH_Id;
		$topName = $element->CustomH_Name;
		
		echo "<option value='".$topID."'>".$topName;
		?>
	<?php
	}
	
	//echo close selector
	echo "</select>";
	
	// Secondary Element Label
  echo '<label for="hCustomFields_secondary_element">' . __("Secondary Element: ", 'hCustomFields_textdomain' ) . '</label> ';
  
  // create selector that will be populated by AJAX
	echo "<select id='secondarySelect' name='secondarySelect' disabled='true'>";
	
	// close selector
	echo "</select>";
	
	echo wp_nonce_field('check_nonce','hCustomFields_nonce');
  
}

/* Do something with the data entered */
add_action('save_post', 'hCustomFields_save_postdata');

/* When the post is saved, saves our custom data */
function hCustomFields_save_postdata( $post_id ) 
{
	
	// check to make sure the call isn't for an autosave
	if ((wp_is_post_revision( $post_id )) || (wp_is_post_autosave( $post_id )))
	{
  	return $post_id;
	};
  	
	  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce( $_POST['hCustomFields_nonce'], 'check_nonce' )) {
    return $post_id;
  };

  // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
  // to do anything
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
    return $post_id;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return $post_id;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return $post_id;
  };

  // OK, we're authenticated: we need to find and save the data
  
  	// so you can access the dB
	global $wpdb; 
	
	$parentID = $_POST['parentSelect'];
	$secondaryID = $_POST['secondarySelect'];
  
 	// get the value of the previously selected parent element (parent if CustomH_Parent is null)
	$metaArray = $wpdb->get_row("SELECT CustomH_Id, CustomH_Name FROM ".$wpdb->prefix."plugin_HeirarchyElement, ".$wpdb->prefix."postmeta WHERE (CustomH_Id = meta_value) AND (post_ID='".$post_id."') AND (meta_key='Parent_Element')");
	
	// check to see if previous parent meta row exist. 
	if (empty($metaArray))
	{
		
		// check to make sure the selector doesn't have -1 (no value selected)
		if ($parentID!=='-1')
		{
			// insert the parent element
	   		$wpdb->query("INSERT INTO ".$wpdb->prefix."postmeta (meta_key,meta_value,post_id) VALUES ('Parent_Element','".$parentID."','".$post_id."')");
		};
	}
	else
	{
		// check to see if previous parent meta row exist. if the select id != -1 : if no, insert. if yes, update
		if ((!($parentID == '-1'))&&(!($parentID == $metaArray->CustomH_Id)))
		{
			
			// update the parent element
	   		$wpdb->query("UPDATE ".$wpdb->prefix."postmeta SET meta_value='".$parentID."' WHERE (post_id='".$post_id."') AND (meta_key='Parent_Element')");
	   		
	   		// delete the old secondary element
	   		$wpdb->query("DELETE FROM ".$wpdb->prefix."postmeta WHERE (post_ID='".$post_id."') AND (meta_key='Secondary_Element')");
   		}
		else if ($parentID == '-1')
		{
			
			// delete the current row
			$wpdb->query("DELETE FROM ".$wpdb->prefix."postmeta WHERE (post_ID='".$post_id."') AND (meta_key='Parent_Element')");
		}
		else
		{
			
		};
	};
	
	// get the values of the previously selected secondary element
	$metaArray1 = $wpdb->get_row("SELECT CustomH_Id, CustomH_Name FROM ".$wpdb->prefix."plugin_HeirarchyElement, ".$wpdb->prefix."postmeta WHERE (CustomH_Id = meta_value) AND (post_ID='".$post_id."') AND (meta_key='Secondary_Element')");
	
	// check to see if previous secondary meta row exist.
	if (empty($metaArray1))
	{
		// check to make sure the selector doesn't have -1 (no value selected)
		if ($secondaryID!=='-1')
		{
			// insert the parent element
			$wpdb->query("INSERT INTO ".$wpdb->prefix."postmeta (meta_key,meta_value,post_id) VALUES ('Secondary_Element','".$secondaryID."','".$post_id."')");
		};
	}
	else
	{
		// check to see if previous secondary meta row exist. if the select id != -1 : if no, insert. if yes, update
		if (($secondaryID!=='-1')&&(!($parentID == $metaArray1->CustomH_Id)))
		{
			// update the parent element
	   		$wpdb->query("UPDATE ".$wpdb->prefix."postmeta SET meta_value='".$secondaryID."' WHERE (post_id='".$post_id."') AND (meta_key='Secondary_Element')");
		}
		else if ($secondaryID=='-1')
		{
			// delete the current row
			$wpdb->query("DELETE FROM ".$wpdb->prefix."postmeta WHERE (post_ID='".$post_id."') AND (meta_key='Secondary_Element')");
		}
		else
		{			
		};
	};
	
   return $mydata;
}

// all below code is for AJAX processing

add_action('admin_head', 'my_action_javascript');

function my_action_javascript() {
?>
<script type="text/javascript" >

function populate_Select(parentID,postID){
jQuery(document).ready(function($) {

	 //Empty secondary categories
	 $('#secondarySelect').empty();
	
	var data = {
		action: 'my_special_action',
		parentID: parentID,
		postID: postID
	};

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response) 
	{
		$('#secondarySelect').removeAttr("disabled").append(response);
	});
});
};
</script>
<?php
}

// if my_special_action is called via javascript, direct to the my_action_callback php function
add_action('wp_ajax_my_special_action', 'my_action_callback');

function my_action_callback() {
	global $wpdb; // this is how you get access to the database
	
	$parentID = $_POST['parentID'];
	$postID = $_POST['postID'];

    $array2 = $wpdb->get_results("SELECT CustomH_Id, CustomH_Name FROM ".$wpdb->prefix."plugin_HeirarchyElement WHERE (CustomH_Parent='".$parentID."') ORDER BY CustomH_Name");
    
    // get the values of the previously selected secondary element
	$metaArray2 = $wpdb->get_row("SELECT CustomH_Id, CustomH_Name FROM ".$wpdb->prefix."plugin_HeirarchyElement, ".$wpdb->prefix."postmeta WHERE (CustomH_Id = meta_value) AND (post_ID='".$postID."') AND (CustomH_Parent='".$parentID."')");
	
	// if the array holds a previously selected element, display it
	if (!(empty($metaArray2)))
	{	
		$option = "<option value='".$metaArray2->CustomH_Id."' SELECTED>".$metaArray2->CustomH_Name."</option>";
	}
	else
	{
			
    	$option = "<option value='-1'>None selected</option>";
	};
    
    // For each key of the array assign variable name "secondaryID"
	// For each value of the array assign variable name "secondaryName".
	foreach($array2 as $element)
	{
		$secondaryID = $element->CustomH_Id;
		$secondaryName = $element->CustomH_Name;
		
		$option .= "<option value='".$secondaryID."'>".$secondaryName."</option";
	}
	
	echo $option;

	die();
}//end function

?>