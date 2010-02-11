<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();	
	}
	
	function index()
	{
		$this->load->library('twitter');
		
		$data['tip'] = $this->twitter->get_tip();

		$data['my'] = $this->twitter->get_mytweets();
		
		$this->load->view('twitt', $data);
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */