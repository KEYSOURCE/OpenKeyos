<?php

class_load ('CustomerInternetContract');

define ('ICON_FILE_ATTACH', 'images/icons/document_attachment_32.png');
define ('ICON_FILE_ATTACH_WIDTH', 32);
define ('ICON_FILE_ATTACH_HEIGHT', 32);

/**
* Class for managing photos for customers. Photos can be attached not only to customers,
* but to specific object linked to customers (e.g. computers, peripherals, locations).
*
* The uploaded file is created in the uploads directory upon invoking load_from_array(),
* NOT upon calling save_data (). The save_data() method only takes care of saving the
* data into the database.
*
* Accordingly, the delete() method will check for the file to be deleted even if the
* object has not been saved to database (doesn't have an ID).
*/

class CustomerPhoto extends Base
{
	/** Attachment ID
	* @var int */
	var $id = null;
	
	/** The ID of the customer to which the photo belongs to
	* @var int */
	var $customer_id = null;
	
	/** The object class - if the photo is linked to a specific object - see $GLOBALS['PHOTO_OBJECT_CLASSES']
	* @var int */
	var $object_class = 0;
	
	/** The object ID - if the photo is linked to a specific object
	* @var int */
	var $object_id = 0;
	
	/** The time when the attachment was uploaded
	* @var time */
	var $uploaded = 0;
	
	/** The original name of the file that was uploaded
	* @var string */
	var $original_filename = '';
	
	/** The local stored file name (without the path)
	* @var string */
	var $local_filename = '';	
	
	/** A subject for this photo
	* @var string */
	var $subject = '';
	
	/** Comments for this photo
	* @var text */
	var $comments = '';
	
	/** An optional external URL to use for this photo
	* @var text */
	var $ext_url = '';
	
	
	/** Image width - calculated from the size of the file when the object is loaded
	* @var int */
	var $width = 0;
	
	/** Image height - calculated from the size of the file when the object is loaded
	* @var int */
	var $height = 0;
	
	/** Thumbnail image width - calculated from the size of the file when the object is loaded
	* @var int */
	var $thumbnail_width = 0;
	
	/** Thumbnail image width - calculated from the size of the file when the object is loaded
	* @var int */
	var $thumbnail_height = 0;
	
	
	var $table = TBL_CUSTOMERS_PHOTOS;
	var $fields = array ('id', 'customer_id', 'object_class', 'object_id', 'uploaded', 'original_filename', 'local_filename', 'subject', 'comments', 'ext_url');


	/**
	* Constructor. Also loads the object data if an object ID is provided
	* @param	int	$id		The ID of the photo
	*/
	function CustomerPhoto ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
                        //$this->verify_access();
		}
	}
	
	
	/** Loads the object data and calculates the images size */
	function load_data ()
	{
		parent::load_data ();
		$this->load_image_sizes ();
	}
	
	
	/** Calculate the image sizes */
	function load_image_sizes ()
	{
		$fname = $this->get_full_path();
		$t_fname = $this->get_thumb_full_path();
		
		if ($fname) list($this->width, $this->height, $type, $attr) = @getimagesize($fname); // XXXXXXXXXX
		if ($t_fname) list($this->thumbnail_width, $this->thumbnail_height, $type, $attr) = @getimagesize($t_fname);
	}
	
	
	/**
	* Loads the attachment data from an array and creates the local copy of the uploaded file
	* uploaded file.
	* @param	array		$data		Associative array with the details of an uploaded file.
	*						It must contain the following keys:
	*						- name : the name of the file
	*						- tmp_name : the temporary name where the uploaded file was saved
	*						- local_filename : (Optional) if a file has already been uploaded at a previous step,
	*						  then specify the name that was generated at that time.
	*						- customer_id : the ID of the customer 
	*						- object_class : (Optional) the object class, if this is linked to an object
	* 						- object_id : (Optional) the object ID, if this is linked to an object
	*						- subject: (Optional) t
	*/
	function load_from_array ($data = array())
	{
		if ($data['name']) $this->original_filename = $data['name'];
		if ($data['ext_url'])
		{
			if (!preg_match ('/^http(s)*\:\/\//', $data['ext_url'])) $data['ext_url'] = 'http://'.$data['ext_url'];
		}
		
		$fields = array ('customer_id', 'object_class', 'object_id', 'subject', 'comments', 'ext_url');
		foreach ($fields as $field) if (isset($data[$field])) $this->$field = $data[$field];
		
		if ($data['tmp_name'] and file_exists($data['tmp_name']))
		{
			// We have an uploaded file
			$this->uploaded = time();
			
			// Check if a file has not been uploaded previously. If yes, reuse the name
			if (!$this->local_filename)
			{
				if ($data['local_filename']) $this->local_filename = $data['local_filename'];
				else $this->local_filename = $this->generate_name ();
			}
		
			move_uploaded_file ($data['tmp_name'], DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename);
			if (!file_exists(DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename)) $this->local_filename = '';
			else
			{
				// Upload was OK, now resize the image (if needed) and generate thumbnail.
				$this->resize_image ();
				$this->generate_thumbnail ();
			}
		}
		elseif ($data['local_filename'] and file_exists(DIR_UPLOAD_CUSTOMER.'/'.$data['local_filename']))
		{
			// A file has been uploaded at a previous step
			$this->local_filename = $data['local_filename'];
		}
		
		// Make sure to syncronize image size
		$this->load_image_sizes ();
	}
	
	
	/** If the image exceeds the max allowed with or height, the image gets resized */
	function resize_image ()
	{
		$fname = $this->get_full_path();
		if ($fname)
		{
			list($orig_width, $orig_height, $type, $attr) = @getimagesize($fname); //zzzzzzzzzz
			if ($orig_width>0 and $orig_height>0)
			{
				$ratio = min (IMAGE_MAX_WIDTH/$orig_width, IMAGE_MAX_HEIGHT/$orig_height);
	
				if ($ratio < 1)
				{
					// The image needs to be resized
					$width = intval($orig_width * $ratio); $height = intval($orig_height * $ratio);
					$fp = fopen($fname, 'r');
					$img_content = fread($fp, filesize($fname));
					fclose($fp);
					$orig_img = imagecreatefromstring ($img_content);
					
					$new_img = imagecreatetruecolor($width, $height);
					$color = ImageColorAllocate($new_img, 255, 255, 255);
					imagefill ($new_img, 0, 0, $color);
					imagecopyresampled ($new_img, $orig_img, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
					
					// Save the resized image
					imagejpeg ($new_img, $fname);
					imagedestroy($orig_img);
					imagedestroy($new_img);
				}
			}
		}
	}
	
	
	/** Generate the thumbnail for the uploaded image */
	function generate_thumbnail ()
	{
		$fname = $this->get_full_path();
		if ($fname)
		{
			$t_fname = $fname . THUMBNAIL_SUFFIX;
			list($orig_width, $orig_height, $type, $attr) = @getimagesize($fname); //XXXXXXXXXXXXXXX
			
			if ($orig_width>0 and $orig_height>0)
			{
				$ratio = min (THUMBNAIL_MAX_WIDTH/$orig_width, THUMBNAIL_MAX_HEIGHT/$orig_height);
				
				if ($ratio < 1)
				{
					// The image needs to be resized to generate the thumbnail
					$width = intval($orig_width * $ratio); $height = intval($orig_height * $ratio);
					$fp = fopen($fname, 'r');
					$img_content = fread($fp, filesize($fname));
					fclose($fp);
					$orig_img = imagecreatefromstring ($img_content);
					
					$new_img = imagecreatetruecolor($width, $height);
					$color = ImageColorAllocate($new_img, 255, 255, 255);
					imagefill ($new_img, 0, 0, $color);
					imagecopyresampled ($new_img, $orig_img, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
					
					// Save the resized thumbnail
					imagejpeg ($new_img, $t_fname);
					imagedestroy($orig_img);
					imagedestroy($new_img);
				}
			}
			else
			{
				// Since this was not an image, delete the existing thumbnail
				@unlink ($t_fname);
			}
			
// 			else
// 			{
// 				error_msg ('The specified file is not a recognized image file. Please try again.');
// 			}
		}
	}
	
	/** Generates a unique file name for storing an uploaded ticket attachment */
	function generate_name ()
	{
		$name = FILE_PREFIX_CUSTOMER_PHOTO.$this->customer_id.'_';
		$name = basename(tempnam (DIR_UPLOAD_CUSTOMER, $name));
		
		return $name;
	}
	
	
	/** Checks if the photo data is valid - meaning if the local file has been properly created */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->local_filename or ($this->local_filename and !file_exists(DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename)))
		{
			error_msg ('The uploading of the picture has failed. Please try again.');
			$ret = false;
		}
		
		if (!$this->subject)
		{
			error_msg ('Please specify the subject.');
			$ret = false;
		}
		if ($this->object_class and (!$this->object_id or $this->object_id<0))
		{
			error_msg ('Please specify the linked object.');
			$ret = false;
		}
		if (!$this->customer_id)
		{
			error_msg ('Please specify the customer.');
		}
		
		return $ret;
	}
	
	
	/** Deletes an object from database and the attachment file from disk */
	function delete ()
	{
		// Delete the photo file from disk, if it exists
		if ($this->local_filename)
		{
			if (file_exists(DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename)) @unlink (DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename);
		}
		
		// Delete the object from database, if exists
		if ($this->id)
		{
			parent::delete ();
		}
	}
	
	
	/** Returns true or false if the linked file is an image or not. The decision is simply
	* done by checking if the object contains a width and a height */
	function is_image ()
	{
		return ($this->width > 0 and $this->height > 0);
	}
	
	/** Returns the full path to the image file - only if the filename is defined and the file actually exists */
	function get_full_path ()
	{
		$ret = '';
		if ($this->local_filename)
		{
			if (file_exists(DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename))
			{
				$ret = DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename;
			}
		}
		
		return $ret;
	}
	
	/** Returns the full path to the thumbnail file - only if the filename is defined and the file actually exists */
	function get_thumb_full_path ()
	{
		$ret = '';
		if ($this->local_filename)
		{
			if (file_exists(DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename.THUMBNAIL_SUFFIX))
			{
				$ret = DIR_UPLOAD_CUSTOMER.'/'.$this->local_filename.THUMBNAIL_SUFFIX;
			}
		}
		
		return $ret;
	}
	
	
	function get_url ($thumb = false)
	{
		$ret = './?cl=customer&amp;op=customer_photo_show&amp;';
		if ($thumb) $re.= 'thumb=1&amp;';
		if ($this->id) $ret.= 'id='.$this->id;
		else $ret.= 'tmp_name='.$this->local_filename.'&amp;orig_name='.urlencode($this->original_filename);
		return $ret;
	}
	
	/** Composes the IMG tag for displaying the thumbnail image */
	function get_thumb_tag ()
	{
		$ret = '';
		if ($this->get_thumb_full_path())
		{
			//$ret = '<img src="./?cl=customer&amp;op=customer_photo_show&amp;thumb=1&amp;';
			//if ($this->id) $ret.= 'id='.$this->id.'" ';
			//else $ret.= 'tmp_name='.$this->local_filename.'" ';
			
			$ret = '<img src="'.$this->get_url(true).'" ';
			$ret.= 'width="'.$this->thumbnail_width.'" height="'.$this->thumbnail_height.'" ';
			$ret.= 'alt="'.htmlspecialchars($this->original_filename).'" ';
			$ret.= 'title="'.htmlspecialchars($this->original_filename).'"/>';
		}
		elseif ($this->get_full_path())
		{
			$ret = '<img src="'.ICON_FILE_ATTACH.'" width="'.ICON_FILE_ATTACH_WIDTH.'" height="'.ICON_FILE_ATTACH_WIDTH.'" /> ';
		}
		else
		{
			$ret = '<p class="error">[File&nbsp;missing]</p>';
		}
		
		return $ret;
	}
	
	/** Composes the IMG tag for displaying the full image */
	function get_tag ()
	{
		$ret = '';
		if ($this->get_full_path())
		{
			if ($this->is_image())
			{
				$ret = '<img src="'.$this->get_url().'" ';//./?cl=customer&amp;op=customer_photo_show&amp;';
				//if ($this->id) $ret.= 'id='.$this->id.'" ';
				//else $ret.= 'tmp_name='.$this->local_filename.'" ';
				$ret.= 'width="'.$this->width.'" height="'.$this->height.'" ';
				$ret.= 'alt="'.htmlspecialchars($this->original_filename).'" ';
				$ret.= 'title="'.htmlspecialchars($this->original_filename).'"/>';
			}
			else
			{
				$ret = '<img src="'.ICON_FILE_ATTACH.'" width="'.ICON_FILE_ATTACH_WIDTH.'" height="'.ICON_FILE_ATTACH_WIDTH.'" /> ';
			}
		}
		else
		{
			$ret = '<p class="error">[File does not exist]</p>';
		}
		
		return $ret;
	}
	
	/**
	* [Class Method] Returns photos according to a specified criteria
	* @param	array		$filter			Associative array with filtering criteria. Can contain:
	* 							- customer_id: The ID of a customer 
	*							- object_class: An object class
	*							- object_id: And object ID
	* @return	array(CustomerPhoto)			Array with the matched CustomerPhoto objects
	*/
	public static function get_photos ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT p.id FROM '.TBL_CUSTOMERS_PHOTOS.' p WHERE ';
		
		if ($filter['customer_id']) $q.= 'p.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['object_class']) $q.= 'p.object_class='.$filter['object_class'].' AND ';
		if ($filter['object_id']) $q.= 'p.object_id='.$filter['object_id'].' AND ';
		
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q.= 'ORDER BY p.object_class, p.subject, p.uploaded ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new CustomerPhoto ($id);
		
		return $ret;
	}

    function verify_access() {
        $uid = get_uid();
        class_load('User');
        $user = new User($uid);
        if($user->type == USER_TYPE_CUSTOMER) {
            if($this->customer_id != $user->customer_id) {
                $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                header("Location: $url\n\n");
                exit;
            }
        }
    }
}
?>