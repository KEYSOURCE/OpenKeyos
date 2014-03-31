
/**
* Contains AJAX functions and classes for KRIFS interactions
*/


/**
* Class for representing an object linked to a ticket
* For multi-ID objects (such as AD objects), the object_id field will store both
* IDs, separated by '_'.
* Note that class and object names are stored in the object, to avoid the need
* of creating and storing maps of ID-names.
*/
function TicketObject (object_class, object_class_name, object_id, object_name)
{
	if (object_class) this.object_class = object_class;
	else this.object_class = 0;
	if (object_class_name) this.object_class_name = object_class_name;
	else this.object_class_name = '';
	
	if (object_id) this.object_id = object_id;
	else this.object_id = 0;
	if (object_name) this.object_name = object_name;
	else this.object_name = '';
}


/** 
* Class for representing a list of objects to be linked to a ticket.
* The objects are stored as an array of TicketObject objects. Upon adding
* a new object to the list, the list is automatically sorted.
* Note that when appending a new object the list is not automatically checked
* for duplicates. If needed, this can be done manually through hasObject() method.
*/
function TicketObjectsList ()
{
	/** Array storing the linked objects from this list */
	this.objects = new Array ();
	
	/** Appends a new object to the list */
	this.appendObject = function (object_class, object_class_name, object_id, object_name)
	{
		this.objects[this.objects.length] = new TicketObject (object_class, object_class_name, object_id, object_name);
		this.sortObjects ();
	}
	
	/** Sorts the list of objects, by class ID and by object name */
	this.sortObjects = function ()
	{
		var is_sorted = false;
		var i = 0;
		
		while (!is_sorted)
		{
			is_sorted = true;
			for (i=0; i<this.objects.length-1; i++)
			{
				if ((this.objects[i].object_class > this.objects[i+1].object_class) || 
				  (this.objects[i].object_class == this.objects[i+1].object_class && this.objects[i].object_name > this.objects[i+1].object_name))
				{
					is_sorted = false;
					tmp = this.objects[i];
					this.objects[i] = this.objects[i+1];
					this.objects[i+1] = tmp;
				}
			}
		}
	}
	
	/** Returns true or false if the specified object (by class ID and object ID) exists in the list or not */
	this.hasObject = function (object_class, object_id)
	{
		var ret = false;
		for (var i=0; i<this.objects.length; i++)
		{
			if (this.objects[i].object_class == object_class && this.objects[i].object_id == object_id)
			{
				ret = true;
				break;
			}
		}
		return ret;
	}
	
	/** Removes from the list the object at the specified position */
	this.removeObjectByIdx = function (idx)
	{
		this.objects.splice (idx, 1);
	}
	
	/** Deletes the entire list of objects */
	this.clearList = function ()
	{
		this.objects = new Array ();
	}
}
