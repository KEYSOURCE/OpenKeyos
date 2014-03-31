
<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/ajax_krifs.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

window.resizeTo (650, 400);
var parent_window = window.opener;

// This will store the temporary list of objects in the pop-up window
var selected_objects = parent_window.getLinkedObjects ();

// This will store all the available objects
var all_objects = new Array ();
{foreach from=$all_objects key=class_id item=objects}
	all_objects[{$class_id}] = new TicketObjectsList ();
	{foreach from=$objects key=object_id item=object_name}
		all_objects[{$class_id}].appendObject ({$class_id}, '{$TICKET_OBJECT_CLASSES.$class_id}', '{$object_id}','{$object_name|escape}');
	{/foreach}
{/foreach}

{literal}

// Updates the list of available objects, based on the currently selected objects class
function updateAvailableObjects ()
{
	var frm = document.forms['frm_t'];
	var lst_classes = frm.elements['list_available_classes'];
	var lst_available_objects = frm.elements['list_available_objects'];
			
	if (lst_classes.options.length > 0)
	{
		var c_class_id = lst_classes.options[lst_classes.selectedIndex].value;
		for (var i=lst_available_objects.options.length-1; i>=0; i--) lst_available_objects.options[i] = null;
		for (i=0; i<all_objects[c_class_id].objects.length; i++)
		{
			obj = all_objects[c_class_id].objects[i];
			lst_available_objects.options[i] = new Option ('#'+obj.object_id+': '+obj.object_name, obj.object_id);
		}
	}
}

// Updates the list of selected objects. This should be called anytime the selection gets changed
function updateSelectedObjects ()
{
	var frm = document.forms['frm_t'];
	var lst_objects = frm.elements['list_objects'];
	for (var i=lst_objects.options.length-1; i>=0; i--) lst_objects.options[i] = null;
	
	for (i = 0; i<selected_objects.objects.length; i++)
	{
		obj = selected_objects.objects[i];
		lst_objects.options[i] = new Option (obj.object_class_name+': #'+ obj.object_id+': '+obj.object_name);
	}
}

// Adds an object to the selected list
function addObject ()
{
	var frm = document.forms['frm_t'];
	var lst_classes = frm.elements['list_available_classes'];
	var lst_available_objects = frm.elements['list_available_objects'];
	
	var c_class_id = lst_classes.options[lst_classes.selectedIndex].value;
	var c_class_name = lst_classes.options[lst_classes.selectedIndex].text;
	var obj = all_objects[c_class_id].objects[lst_available_objects.selectedIndex];
	
	// Check if the object has not been selected already
	if (selected_objects.hasObject (c_class_id, obj.object_id)) alert ('This object has already been selected');
	else 
	{
		selected_objects.appendObject (c_class_id, c_class_name, obj.object_id, obj.object_name);
		updateSelectedObjects ();
	}
}

// Removes an object from the selected list
function removeObject ()
{
	var frm = document.forms['frm_t'];
	var lst_objects = frm.elements['list_objects'];
	selected_objects.removeObjectByIdx (lst_objects.selectedIndex);
	updateSelectedObjects ();
}

// "Saves" the selected objects by passing them back to the caller window
function saveObjects ()
{
	parent_window.setLinkedObjects (selected_objects);
	window.close ();
}

// Deletes all selected objects
function clearAllObjects ()
{
	selected_objects = new TicketObjectsList ();
	updateSelectedObjects ();
}

{/literal}

//]]>
</script>

<div style="disply:block; padding: 10px;">
<form action="" method="POST" name="frm_t">

<h2>Select Linked Objects</h2>

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Customer: &nbsp;&nbsp;&nbsp;#{$customer->id}: {$customer->name|escape}</td>
	</tr>
	</thead>
	
	{if count($all_objects) > 0}
	<tr>
		<td colspan="2">
			Available objects classes:<br/>
			<select name="list_available_classes" style="width: 300px;" onchange="updateAvailableObjects();">
			{foreach from=$all_objects key=class_id item=objects}
				<option value="{$class_id}">{$TICKET_OBJECT_CLASSES.$class_id}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	
	<tr>
		<td>
			Available objects:<br/>
			<select name="list_available_objects" size="12" style="width: 250px;" ondblclick="addObject();">
			</select>
		</td>
		<td class="post_highlight">
			Selected objects:<br/>
			<select name="list_objects" size="12" style="width: 250px;" ondblclick="removeObject();">
			</select>
		</td>
	</tr>
	{else}
	<tr>
		<td colspan="2" class="light_text">[No objects available]</td>
	</tr>
	{/if}
</table>
<p/>

{if count($all_objects) > 0}
<input type="submit" name="save" value="Set objects" onclick="saveObjects(); return false;" />
{/if}
<input type="submit" name="cancel" value="Cancel" onclick="window.close(); return false;" />
&nbsp;&nbsp;&nbsp;
{if count($all_objects) > 0}
<input type="submit" name="clear_all" value="Clear all" onclick="clearAllObjects(); return false;" />
{/if}

</form>
</div>

{if count($all_objects) > 0}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
updateAvailableObjects();
updateSelectedObjects();
//]]>
</script>
{/if}