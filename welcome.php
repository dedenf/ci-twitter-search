<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();	
	}
	
	function index()
	{
//		$this->load->library('simplepie');
//		$this->simplepie->set_feed_url('http://localhost/assets/touser-atom.xml');
//		$this->simplepie->enable_cache(FALSE);
//		//$this->simplepie->set_cache_duration(1800);
//		$this->simplepie->strip_htmltags('img');
//		$this->simplepie->set_item_limit(5);
//		$this->simplepie->init();
//		$this->simplepie->handle_content_type();
//		$data['feed'] = $this->simplepie->get_items();
		$this->load->library('twitter');
		$this->load->model('twitter_model');
		$data['title'] = "asd";
		$lastaccess = $this->twitter_model->get_lastaccess();
		$this->twitter->check_fetch();
		//debug($lastaccess);
		//$data['tweet'] = $this->twitter->get_tweets();
		//$data['twit'] = $this->twitter->get_tweet();
		//$data['sender'] = $this->twitter->get_sender();
		$data['tips'] = $this->twitter->get_tip();
		//debug($data['sender']);
		$this->load->view('welcome_message', $data);
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */