<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Twitter Library  for searching the tweets for @politikana from twitter.com
 * 
 * Author: deden fathurahman dedenf@gmail.com
 *
 * 
 * Description:
 * front end wrapper for politikana tweet
 * 
 * 
 * VERSION: 1.0 (2009-04-2)
 * 
 **/

class Twitter{
	/**
	 * URI for the politikana search keyword
	 *
	 * @var string
	 */
	var $uri = "http://search.twitter.com/search.rss?q=to%3Aaulia";
	/**
	 * if you must, other wise, skip it.
	 *
	 * @var array
	 */
	var $strip_tags = array('img');
	/**
	 * enable cache for xml reading?
	 *
	 * @var boolean
	 */
	var $enable_cache = FALSE;
	/**
	 * find your path for your cache my padwan
	 *
	 * @var string
	 */
	var $cache_location = '';
	/**
	 * duration for your cache
	 *
	 * @var int
	 */
	var $cache_duration = 1800;
	/**
	 * Tip Key, well this is depends of your keyword later
	 *
	 * @var string
	 */
	var $tipkey = "-tip-";
	
	/**
	 * Construct this please
	 *
	 * @return Twitter
	 */
	function Twitter(){
		
	}
	
	/**
	 * check our fetch, this is the main event
	 *
	 */
	function check_fetch(){
		$obj =& get_instance();
		$obj->load->model('twitter_model');

		if(!ini_get('safe_mode')){
			set_time_limit(0);
		}
		$interval = 0;
		if($obj->twitter_model->get_lastaccess() - $obj->twitter_model->get_lastfetch() > $interval){
			$this->fetch_tweets();
		}
		$obj->twitter_model->update_last_access();	
	}
	/**
	 * gather all tweets
	 *
	 * @return array
	 */
	function get_tweets(){
		$obj =& get_instance();
		$obj->load->model('twitter_model');
		$last_since_id = $obj->twitter_model->last_since_id();
		//debug($last_since_id);
		if($last_since_id != 0){
			$obj->simplepie->set_feed_url($this->uri."&amp;since_id=".$last_since_id);
		}else{
			$obj->simplepie->set_feed_url($this->uri);
		}
		//$obj->simplepie->set_feed_url($this->uri."&amp;since_id=".$last_since_id);
		$obj->simplepie->enable_cache($this->enable_cache);
		if($this->enable_cache){
			$obj->simplepie->set_cache_duration($this->cache_duration);
			$obj->simplepie->set_cache_location($this->cache_location);
		}
		$obj->simplepie->strip_htmltags($this->strip_tags);
		$obj->simplepie->init();
		$obj->simplepie->handle_content_type();
		$items = $obj->simplepie->get_items();
		return $items;
	}
	
	
	
	/**
	 * okei machine, give me my tweets
	 *
	 */
	function fetch_tweets(){
		$obj =& get_instance();
		$obj->load->model('twitter_model');
		$get_tweets = $this->get_tweets();
		$this->add_new_tweets($get_tweets);
		$obj->twitter_model->update_last_fetch();
	}
	
	/**
	 * Add new tweets to my database please
	 *
	 * @param array $items
	 */
	function add_new_tweets($items){
		$obj =& get_instance();
		$obj->load->model('twitter_model');
		foreach ($items as $item) {
			$new->item_data = array();
			$statuses = explode('/',$item->get_id());
			if ($enclosure = $item->get_enclosure()){
				$image = $enclosure->get_link();
			}
			$tweets_data = array(
							'since_id' => $statuses[5],
							'published' => $item->get_date(),
							'link' => $item->get_link(),
							'title' => $item->get_title(),
							'content' => $item->get_content(),
							'image' => $image,
							'sender' => $statuses[3],
							'sender_uri' => "http://twitter.com/".$statuses[3]
							);
			
			#add
			//echo $tweets_data['since_id'];
			$find = $obj->twitter_model->find_since_id($tweets_data['since_id']);
			if($find == 0){
				$obj->twitter_model->add_tweet($tweets_data);
			}else{
				$check_since_id = $find;
				if($check_since_id == 0){
						$obj->twitter_model->add_tweet($tweets_data);
				}
			}
			
		}
		
	}
	
	/**
	 * search tweets from our kind visitors who gave us a tip
	 *
	 */
	function get_tip(){
		$obj =& get_instance();
		$obj->load->model('twitter_model');
		$this->check_fetch();
		$raw_tweet_tip = $obj->twitter_model->get_tweets_tip();
		$tweet_tip = $raw_tweet_tip->result();
		if(!empty($tweet_tip)){
			foreach ($tweet_tip as $tip){
				preg_match("/(?:[^-]+?)-tip-\s(.+)/",$tip->title, $matches);
				$tips[] =  "<div class=\"tweet_title\"> ".$matches[1]."</div>";
	        	$tips[] .=  "<div class=\"tweet_meta\"><span class=\"tweet_sender\"> thanks to <a href=\"$tip->sender_uri\">".$tip->sender."</a></span> at <span class=\"tweet_date\">".$tip->published."</span> <a href=\"$tip->link\">&raquo;</a></div>";
			}
			
		}else{
			$tips[] = "<div class=\"tweet_error\">No Tip...</div>";
		}
		
		return $tips;
	}
	
	/**
	 * search my tweets, exclude the tip
	 * default dispay is 10 tweets, you could change that later on your call
	 * 
	 * @param int $num
	 * @param int $offset
	 */
	function get_mytweets($num=10, $offset=0){
		$obj =& get_instance();
		$obj->load->model('twitter_model');
		$this->check_fetch();
		$raw_tweet_me = $obj->twitter_model->to_me($num, $offset);
		//debug($raw_tweet_me);
		if(!empty($raw_tweet_me)){
			$tweet_me = $raw_tweet_me->result();
			foreach ($tweet_me as $mytweet){
				$tweets[]  =  "<div class=\"tweet_title\"> ".$mytweet->title."</div>";
	        	$tweets[] .=  "<div class=\"tweet_meta\"><span class=\"tweet_sender\"> <a href=\"$mytweet->sender_uri\">".$mytweet->sender."</a></span> at <span class=\"tweet_date\">".$mytweet->published."</span> <a href=\"$mytweet->link\">&raquo;</a></div>";

			}
		}else{
				$tweets[] = "<div class=\"tweet_title\"> your tweets are empty</div>";
		}
		return $tweets;
	}
}