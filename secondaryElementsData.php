<table style="text-align:center;">
	<tr>
		<td> 
			<b>Secondary Level Elements</b>
		</td>
		<td> 
			&nbsp
		</td>
	</tr>

<?php			

// if the post button was pressed, then do the below
if(!empty($_POST) && isset($_POST))
{
	
  //get variables
  $parentID = $_POST['parentID'];
  $parentName = $_POST['parentName'];
  
  echo '<tr>';
	echo "<td>";
		echo "(Top Level: ".$parentName.")";
	echo '</td>';
  echo '</tr>';
  echo '<tr>';
	echo "<td>";
		echo "&nbsp";
	echo '</td>';
  echo '</tr>';

	//query to select data from table
	$array1 = $wpdb->get_results("SELECT CustomH_Name,CustomH_Id FROM ".$wpdb->prefix."plugin_HeirarchyElement WHERE (CustomH_Parent='".$parentID."') ORDER BY CustomH_Name");
	if(!$array1)
	{
		echo '<tr>';
		echo "<td>";
		echo "No Elements to Display";
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo "<td>";
		echo "&nbsp";
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		?>
		
		<form action="" method="POST" name="enterNewSecond">	
		<table style="text-align:center;">
			<tr>
				<td>
					<input type="text" name="newSecondaryLevelName" />
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="parentID" value="<?php echo $parentID;?>">
					<input type="hidden" name="parentName" value="<?php echo $parentName;?>">
					<input type="submit" name="Submit2nd" class="button-primary" value="<?php esc_attr_e('Add 2nd Level Element') ?>" />
				</td>
			</tr>
		</table>
		</form>
	<?php
	}
	else
	{		
			foreach($array1 as $element)
			{
				$secondaryName = $element->CustomH_Name;
				$secondaryID = $element ->CustomH_Id;
				echo '<tr>';
					echo "<td>";
						echo $secondaryName;
					echo '</td>';
				
					// the 2nd column will allow the user to delete the primary elements
					echo "<td>";
						echo "<form name='deleteElement' action='' method='POST'>";
						echo "<input type='hidden' name='elementName' value='".$secondaryName."'>";
						echo "<input type='hidden' name='elementID' value='".$secondaryID."'>";
						echo "<input type='hidden' name='parentID' value='".$parentID."'>";
						?>
						<input style="width:180px;white-space:normal;" type="submit" name="deleteElement" class="button-secondary" value="<?php esc_attr_e('Delete: '.$secondaryName.'');?>" /> 
						<?php
						echo "</form>";
					echo "</td>";;
				echo '</tr>';
			}
			?>
			<tr>
				<td>
					&nbsp
				</td>
			</tr>
		</table>
		
		<form action="" method="POST" name="enterNewSecond">	
		<table style="text-align:center;">
			<tr>
				<td>
					<input type="text" name="newSecondaryLevelName" />
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="parentID" value="<?php echo $parentID;?>">
					<input type="hidden" name="parentName" value="<?php echo $parentName;?>">
					<input type="submit" name="Submit2nd" class="button-primary" value="<?php esc_attr_e('Add 2nd Level Element') ?>" />
				</td>
			</tr>
		</table>
		</form>
			<?php
	}
}
else
{
	?>
	<tr>
		<td> 
			&nbsp
		</td>
	</tr>
	<?php
	echo "<tr>";
	echo "<td>";
	echo "No Parent Category Selected";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
};			
					