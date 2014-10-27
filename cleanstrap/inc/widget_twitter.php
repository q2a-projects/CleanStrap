<?php
	class cs_twitter_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					array(
						'label' => 'Twitter ID:',
						'type' => 'string',
						'value' => qa_opt('cs_twitter_id'),
						'suffix' => 'your twitter username',
						'tags' => 'NAME="cs_twitter_id_field"',
					),
					array(
						'label' => 'Widget Title:',
						'type' => 'string',
						'value' => qa_opt('cs_twitter_title'),
						'suffix' => 'you can leave it empty',
						'tags' => 'NAME="cs_twitter_title_field"',
					),	
					array(
						'label' => 'number of latest tweets:',
						'suffix' => 'tweets',
						'type' => 'number',
						'value' => (int)qa_opt('cs_twitter_t_count'),
						'tags' => 'NAME="cs_twitter_t_count_field"',
					),
					array(
						'label' => 'Consumer key:',
						'type' => 'string',
						'value' => qa_opt('cs_twitter_ck'),
						'tags' => 'NAME="cs_twitter_ck_field"',
					),	
					array(
						'label' => 'Consumer secret:',
						'type' => 'string',
						'value' => qa_opt('cs_twitter_cs'),
						'tags' => 'NAME="cs_twitter_cs_field"',
					),	
					array(
						'label' => 'Access token:',
						'type' => 'string',
						'value' => qa_opt('cs_twitter_at'),
						'tags' => 'NAME="cs_twitter_at_field"',
					),
					array(
						'label' => 'Access token secret:',
						'type' => 'string',
						'value' => qa_opt('cs_twitter_ts'),
						'tags' => 'NAME="cs_twitter_ts_field"',
						'error' => $this->twitter_api_error_html(),
					),	
				),
			);
		}

		
		function allow_template($template)
		{
			$allow=false;
			
			switch ($template)
			{
				case 'activity':
				case 'qa':
				case 'questions':
				case 'hot':
				case 'ask':
				case 'categories':
				case 'question':
				case 'tag':
				case 'tags':
				case 'unanswered':
				case 'user':
				case 'users':
				case 'search':
				case 'admin':
				case 'custom':
					$allow=true;
					break;
			}
			
			return $allow;
		}

		
		function allow_region($region)
		{
			$allow=false;
			
			switch ($region)
			{
				case 'main':
				case 'side':
				case 'full':
					$allow=true;
					break;
			}
			
			return $allow;
		}
		function twitter_api_error_html()
		{
			return 'To use twitter API you must register your application. to do this visit <a href="https://dev.twitter.com/">twitter development Page</a> and log in with your Twitter credential. then visit <a href="https://dev.twitter.com/apps/">My applications</a> and creat your application and fill these fields from your application API detail. <br /> if these fields are set correctly and your application has permission to work with this domain then you will get your recent tweets in this widget.'; 
		}
		function get_tweets()
		{
			global $cache;
			$age = 3600; //one hour
			if (isset($cache['twitter'])){
				if ( ((int)$cache['twitter']['age'] + $age) > time()) {
					$tweets = $cache['twitter'];
					unset($tweets['age']);
					return $tweets;
				}
			}

			$user = qa_opt('cs_twitter_id');
			$count=(int)qa_opt('cs_twitter_t_count');
			$title=qa_opt('cs_twitter_title');
			
			require_once Q_THEME_DIR.'/inc/TwitterAPIExchange.php';
			// Setting our Authentication Variables that we got after creating an application
			$settings = array(
				'oauth_access_token' => qa_opt('cs_twitter_at'),
				'oauth_access_token_secret' => qa_opt('cs_twitter_ts'),
				'consumer_key' => qa_opt('cs_twitter_ck'),
				'consumer_secret' => qa_opt('cs_twitter_cs')
			);

			$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
			$requestMethod = "GET";

			$getfield = "?screen_name=$user&count=$count";
			$twitter = new TwitterAPIExchange($settings);
			$tweets = json_decode($twitter->setGetfield($getfield)
				->buildOauth($url, $requestMethod)
					->performRequest(),$assoc = TRUE);
			//$tweets = array(array('text' => "hello @towhidn"));
			$cache['twitter'] =  $tweets;
			$cache['twitter']['age'] = time();
			$cache['changed'] = true;	
			return $tweets;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			if(!function_exists('curl_version'))
				return;
				
			$user = qa_opt('qa_twitter_id');
			$count=(int)qa_opt('qa_twitter_t_count');
			$title=qa_opt('qa_twitter_title');

			$themeobject->output('<DIV class="qa-tweeter-widget">');
				$themeobject->output('<H2 class="qa-tweeter-header">'.$title.'</H2>');
				
			$tweets=$this->get_tweets();

			if (empty($tweets)) return;			
			$themeobject->output('<ul class="qa-tweeter-list">');
			foreach($tweets as $items)
			{
				// links
				$items['text'] = preg_replace(
					'@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@',
					 '<a href="$1">$1</a>',
					$items['text']);
				//users
				$items['text'] = preg_replace(
					'/@(\w+)/',
					'<a href="http://twitter.com/$1">@$1</a>',
					$items['text']);	
				// hashtags
				$items['text'] = preg_replace(
					'/\s+#(\w+)/',
					' <a href="http://search.twitter.com/search?q=%23$1">#$1</a>',
					$items['text']);
					
				//echo "Time and Date of Tweet: ".$items['created_at']."<br />";
				$themeobject->output( '<li class="qa-tweeter-item">'. $items['text'].'</li>');
				//echo "Tweeted by: ". $items['user']['name']."<br />";
				//echo "Screen name: ". $items['user']['screen_name']."<br />";
				//echo "Followers: ". $items['user']['followers_count']."<br />";
				//echo "Friends: ". $items['user']['friends_count']."<br />";
				//echo "Listed: ". $items['user']['listed_count']."<br /><hr />";
			}
			 $themeobject->output('</ul>');

			 $themeobject->output('</DIV>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/