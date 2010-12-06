<?php
					
// get values from plugin_HeirarchyElement table where there is no parent (implies only parent elements)
$array1 = $wpdb->get_results('SELECT CustomH_Id, CustomH_Name FROM '.$wpdb->prefix.'plugin_HeirarchyElement WHERE (CustomH_Parent IS null) ORDER BY CustomH_Name');
					
// For each key of the array assign variable name "topID"
// For each value of the array assign variable name "topName".

foreach($array1 as $element)
{
	$topID = $element->CustomH_Id;
	$topName = $element->CustomH_Name;

	echo '<tr>';
		echo "<td>";
			echo "<form name='chooseSecond' action='' method='POST'>";
			echo "<input type='hidden' name='parentName' value='".$topName."'>";
			echo "<input type='hidden' name='parentID' value='".$topID."'>";
			?>
			<input style="width:180px;white-space:normal;" type="submit" name="getSecondElements" class="button-secondary" value="<?php esc_attr_e(''.$topName.'');?>" /> 		
			<?php
			echo "</form>";
		echo '</td>';
		// the 2nd column will allow the user to delete the primary elements
		echo "<td>";
			echo "<form name='deleteElement' action='' method='POST'>";
				echo "<input type='hidden' name='elementName' value='".$topName."'>";
				echo "<input type='hidden' name='elementID' value='".$topID."'>";
				echo "<input type='hidden' name='elementID' value='".$topID."'>";
				echo "<input type='hidden' name='isParent' value='true'>";
				?>
				<input style="width:180px;white-space:normal;" type="submit" name="deleteElement" class="button-secondary" value="<?php esc_attr_e('Delete: '.$topName.'');?>" /> 
				<?php
			echo "</form>";
		echo "</td>";
	echo '</tr>';
}
?>