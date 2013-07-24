<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link			http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category		Pagination
 * @author		Raheel
*/

//--------------------------------------------------------------------------

/**
 * How to use
 * Create an array for your desired settings and passit to initialize function
 * $config['total_items']	=	count($result);
 * $config['base_url']	=	site_url('welcome/index/');
 * $this->pagination->initialize($config);
 * 
 * Now call functions
 * $data['links']			=	$this->pagination->page_links();
 * $data['perpage_links']	=	$this->pagination->perpage_links();
 * 
 * For query do this
 * $limit	=	$this->uri->segment(3,0);
 * $offset	=	$this->uri->segment(4,5);
 * $start	=	$limit > 1 ? ($limit * $offset) - $offset : 0;
 * $query	=	"SELECT * FROM mytable ORDER BY blah LIMIT $start , $offset"; 
 * That's All
*/
  
Class Pagination
{
	var $base_url				=	'';
	var $first_link			=	'First';
	var $next_link			=	' > ';
	var $prev_link			=	' < ';
	var $last_link			=	'Last';
	var $other_link			=	'';
	var $link_class			=	'';
	var $display_item_class	=	'';
	var $page				=	'page';
	var $perpage				=	'perpage';
	
	var $display_pages		=	TRUE;
	var $page_query_string	=	FALSE;
	var $perpage_display		=	TRUE;
	var $reverse_items_array	=	FALSE;
	
	var $display_items_array	=	array(5,10,15,20);
	
	var $total_items			=	100;
	var $display_items		=	20;
	var $current_page			=	0;
	var $current_perpage		=	0;
	var $page_segment			=	3;
	var $perpage_segment		=	4;
	var $total_pages			=	5;
	var $num_links			=	2; 
	var $base_page			=	0;
	var $first_page			=	1;
	
	var $start				=	0;
	var	$end					=	0;
	var $CI					=	'';
	
	public function __construct($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);
		}
	}
	
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
	}	
	
	function settings()
	{
		if ($this->link_class != '')
		{
			$this->link_class = 'class="'.$this->link_class.'" ';
		}
		
		if ($this->display_item_class != '')
		{
			$this->display_item_class = 'class="'.$this->display_item_class.'" ';
		}

		/*	if total items or display is 0 return	*/
		if($this->total_items == 0 OR $this->display_items == 0)	
		{
			return '';
		}
		
		
		/*	Reverse the array 	*/
		if($this->reverse_items_array == TRUE)
		{
			/*	Sort the array in case it is not sorted	*/
			sort($this->display_items_array);
			
			$this->display_items_array	=	array_reverse($this->display_items_array);
		}
		
		$this->CI	=	& get_instance();
		
		if ($this->CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			if ($this->CI->input->get($this->query_string_segment) != $this->base_page)
			{
				$this->current_page = $CI->input->get($this->query_string_segment);

				// Prep the current page
				$this->current_page = (int) $this->current_page;
			}
		}
		else
		{
			if ($this->CI->uri->segment($this->page_segment) != $this->base_page)
			{
				$this->current_page = $this->CI->uri->segment($this->page_segment);

				// Prep the current page 
				$this->current_page = (int) $this->current_page;
			}
		}
	
		if(($this->perpage_display == TRUE) AND count($this->display_items_array) > 0)
		{
			/*	default but need to set with segment and query string		*/
			
			if($this->CI->uri->segment($this->perpage_segment)){
				$this->current_perpage	=	$this->CI->uri->segment($this->perpage_segment);
			}else{
				$this->current_perpage	=	reset($this->display_items_array);
			}
			
			$this->total_pages		=	ceil($this->total_items / $this->current_perpage);
			$this->base_page			=	1;
		}else{
			$this->total_pages		=	ceil($this->total_items / $this->display_items);
		}	

		// Set current page to 1 if using page numbers instead of offset
		if ($this->display_pages AND $this->current_page == 0)
		{
			$this->current_page = $this->base_page;
		}

		$this->num_links = (int)$this->num_links;

		if ($this->num_links < 1)
		{
			show_error('Your number of links must be a positive number.');
		}

		if ( ! is_numeric($this->current_page))
		{
			$this->current_page = $this->base_page;
		}

		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->display_pages)
		{
			if ($this->current_page > $this->total_pages)
			{
				$this->current_page = $this->total_pages;
			}
		}
		else
		{
			if ($this->current_page > $this->total_items)
			{
				$this->current_page = ($this->total_pages - 1) * $this->current_perpage;
			}
		}		
		
		if ( ! $this->display_pages)
		{
			$this->current_page = floor(($this->current_page/$this->current_perpage) + 1);
		}

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$this->start = (($this->current_page - $this->num_links) > 0) ? $this->current_page - ($this->num_links - 1) : 1;
		$this->end   = (($this->current_page + $this->num_links) < $this->total_pages) ? $this->current_page + $this->num_links : $this->total_pages;
	
	}

	public function page_links()
	{
		$this->settings();
		
		$output	=	'';
		// Render the "First" link
		
		if  ($this->current_page > ($this->num_links + 1))
		{
			$output .= 	'<a '.$this->link_class.'href="'.$this->base_url. '/'. $this->first_page .'/'.$this->current_perpage.'">'.$this->first_link.'</a> ';
		}

		// Render the "previous" link
		if  ($this->current_page != 1)
		{
			if ($this->display_pages)
			{
				$i = $this->current_page - 1;
			}
			else
			{
				$i = $this->current_page - $this->perpage;
			}

			if ($i == 0 && $this->first_url != '')
			{
				$output .=	$this->generate_link($this->first_url , $this->current_perpage , $this->prev_link);
			}
			else
			{
				$i = ($i == 0) ? '' : $i;
				$output .=	$this->generate_link($i , $this->current_perpage , $this->prev_link);
			}
		}
		
		// Render the pages
		if ($this->display_pages !== FALSE)
		{
			// Write the digit links
			for ($loop = $this->start -1; $loop <= $this->end; $loop++)
			{
				if ($this->display_pages)
				{
					$i = $loop;
				}
				else
				{
					$i = ($loop * $this->current_perpage) - $this->current_perpage;
				}

				if ($i >= $this->base_page)
				{
					if ($this->current_page == $loop)
					{
						$output .= $this->current_page; // Current page
					}
					else
					{
						$n = ($i == $this->base_page) ? '' : $i;

						if ($n == '')
						{
							$output .=	$this->generate_link($this->first_page , $this->current_perpage , $loop);
						}
						else
						{
							$n = ($n == '') ? '' : $n;

							$output .=	$this->generate_link($n , $this->current_perpage , $loop);
						}
					}
				}
			}
		}
		
		// Render the "next" link
		
		if ($this->current_page < $this->end)
		{
			if ($this->display_pages)
			{
				$i = $this->current_page + 1;
			}
			else
			{
				$i = ($this->current_page * $this->perpage);
			}

			$output .=	$this->generate_link($i , $this->current_perpage , $this->next_link);
		}

		// Render the "Last" link

		if (($this->current_page + $this->num_links) < $this->total_pages)
		{
			if ($this->display_pages)
			{
				$i = $this->total_pages;
			}
			else
			{
				$i = (($this->total_items * $this->current_perpage) - $this->current_perpage);
			}
			$output .=	$this->generate_link($i , $this->current_perpage , $this->next_link);
		}

		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		return $output;
	}
	
	public function perpage_links()
	{

		$output	=	'';	
		
		if(!$this->perpage_display OR $this->total_items == 0 OR $this->display_items == 0 OR count($this->display_items_array) == 0)
		{
			return $output;
		}
		
		foreach($this->display_items_array as $display_items_row)
		{
			if($this->current_perpage == $display_items_row)
			{
				$output .=  $display_items_row;
			}else{
				$output .=	$this->generate_link($this->first_page , $display_items_row , $display_items_row);
			}
		}
		
		return $output;
	}
	
	private function generate_link($page , $perpage , $text)
	{
		$output	=	' <a '.$this->link_class.'href="'.$this->base_url. '/' . $page .'/'.$perpage.'">'.$text.'</a> ';	
		return $output;
	}
	
	
}