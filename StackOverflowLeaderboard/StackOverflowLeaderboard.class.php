<?php
/*
Plugin Name: Stack Overflow Leaderboard
Plugin URI: http://strategy.turner.com
Description: List of Stack Overflow users with their reputation.
Version: 1.0.0
Author: Simon Germain - Turner Broadcasting System, Inc.
License: GPLv2 or later

Stack Overflow Leaderboard. Lists reputation and badges from user
profiles on Stack Overflow and displays it as a leaderboard.
Copyright (C) 2012  Turner Broadcasting System, Inc.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

class StackOverflowLeaderboard extends WP_Widget {

	public function __construct() {
		$widget_opts = array(
            'class' => 'wsplus',
            'desc' => 'Showcase Your Stackoverflow Profile'
        );
        
        $this->WP_Widget('StackOverflowLeaderboard', 'Stack Overflow Leaderboard', $widget_opts);
	}
	
	public function form($instance) {
        $instance = wp_parse_args((array) $instance,
                                  array(
                                            'title' => '',
                                            'id_list' => array()
                                        )
                                  );
                                  
        //Instance Element Shortcuts
        $title = $instance['title'];
        $id_list = $instance['id_list'];
        
        ?>
		<div>
            <strong style="text-decoration: underline;">General Options</strong>
            <input type="hidden" name="<?php echo $this->get_field_name('id_to_remove'); ?>" value="" />
            <br><br>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" placeholder="Example: My Stackoverflow Stats" />
            </p>
            <script>
            	function removeId(clickedElement) {
                	var form = jQuery(clickedElement).parents('form');
                	var submitBtn = jQuery('input[type$="submit"]', form);
                	var listText = jQuery(clickedElement).parents('li').text();
                	var idToRemove = listText.substring(0, listText.indexOf(' '));
                	if (confirm('Are you sure you wish to remove ID: ' + idToRemove)) {
						jQuery('input[name$="<?= $this->get_field_name('id_to_remove') ?>"]', form).val(idToRemove);
						submitBtn.click();
                	}
            	}
            </script>
            <p>
            	<label for="<?= $this->get_field_id('stackoverflow_id_leaderboard'); ?>">List of registered users: </label>
            	<ul class="SOList">
            	<?php if (count($id_list) > 0): ?>
        		<?php foreach ($id_list as $key => $value): ?>
            		<li><?= $value ?> (<a href="javascript:void" onclick="removeId(this); return false;">Remove</a>)</li>
            	<?php endforeach; ?>
            	<?php else: ?>
	            	<li>No entries.</li>
            	<?php endif; ?>
            	</ul>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('stackoverflow_id'); ?>">Add a Stackoverflow id: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('stackoverflow_id'); ?>" name="<?php echo $this->get_field_name('stackoverflow_id'); ?>" type="text" value="<?php echo attribute_escape($stackoverflow_id); ?>" placeholder="Example: 3726381" />
            </p>
            <?php if ($_SESSION['SOSession_Messages'] != ''): ?>
            <p>
            	<?= $_SESSION['SOSession_Messages']; ?>
            </p>
            <?php $_SESSION['SOSession_Messages'] = ''; ?>
            <?php endif; ?>
            <p>
            </p>
        </div>
        <?php
	}
	
	function update($new, $old){
		
		error_log('New: ' . print_r($new, 1));
		error_log('Old: ' . print_r($old, 1));
		
        $instance = $old;
        
        $instance['title'] = $new['title'];
        
       	if (is_array($old['id_list'])) {
       		$instance['id_list'] = $old['id_list'];
       	}
        
        if (count($instance['id_list']) == 0) {
        	$instance['id_list'] = array();
        }
        
        error_log('Updating');
        
        if ($new['id_to_remove'] != '') {
        	unset($instance['id_list'][array_search($new['id_to_remove'], $instance['id_list'])]);
        	$session_msg = '<span class="success">Successfully removed ID ' . $new['id_to_remove'] . '</span>';
        }
        
        $instance['id_to_remove'] = '';
        
        error_log(print_r($instance, 1));
        
        if ($new['stackoverflow_id'] != '') {
        	if (!array_search($new['stackoverflow_id'], $instance['id_list'])) {
        		array_push($instance['id_list'], $new['stackoverflow_id']);
        		$session_msg = '<span class="success">Successfully added ID ' . $new['stackoverflow_id'] . '</span>';
        	}
        	else {
        		$session_msg = '<span class="error">ID ' . $new['stackoverflow_id'] . ' is already in the list. Skipping!</span>';
        	}
        }
        
        $_SESSION['SOSession_Messages'] = $session_msg;
        
        return $instance;
    }
	
	public function widget($args, $instance) {
		print $args['before_widget'];
		print '<h3>' . $instance['title'] . '</h3><br />';
		print '<ul>';
		if (is_array($instance['id_list']) && count($instance['id_list']) > 0) {
			$users = getUserData(implode(';', $instance['id_list']));
			foreach ($users['items'] as $current_user) {
				?>
					<li class="displayName"><a href="<?= $current_user['link'] ?>" target="_blank"><?= $current_user['display_name'] ?></a> (<span><?php if ($current_user['badge_counts']['gold'] != '0'): ?> <span class="badge gold"></span><span class="score"><?= $current_user['badge_counts']['gold'] ?></span><?php endif; ?><?php if ($current_user['badge_counts']['silver'] != '0'): ?> <span class="badge silver"></span><span class="score"><?= $current_user['badge_counts']['silver'] ?></span><?php endif; ?><?php if ($current_user['badge_counts']['bronze'] != '0'): ?> <span class="badge bronze"></span><span class="score"><?= $current_user['badge_counts']['bronze'] ?><?php endif; ?></span></span> ): <?= $current_user['reputation']?></li>
				<?php 
			}
		}
		else {
			print '<li>No registered users.</li>';
		}
		print '</ul>';
		print $args['after_widget'];
	}
	
}

function stackOverflowLeaderboardCSS() {

	$SOLUrl = get_bloginfo('url') . '/wp-content/plugins/StackOverflowLeaderboard/stackoverflowleaderboard.css';
	wp_register_style('SOLStyleSheets', $SOLUrl);
    wp_enqueue_style( 'SOLStyleSheets');
}

function sanitizeObject($d) {
    if (is_object($d)) {
        $d = get_object_vars($d);
    } 
    if (is_array($d)) {
	return array_map(__FUNCTION__, $d);
    } else {
	return $d;
    }
}

function getUserData($ids, $type=''){

	// Define Local Variables
    $site = 'stackoverflow';
    $baseurl = 'compress.zlib://http://api.stackexchange.com/2.0/users/';
    $apikey = 'DG247bg7nfODEu9MZnJrsg((';
    
    //Construct Url and Get Data off SO and use Sanitizer.
    if($type == ''){
        $url = $baseurl.$ids.'?key='.$apikey.'&site='.$site.'&order=desc&sort=reputation&filter=default';
    } else if ($type == 'byQuestionids'){
        $url = 'compress.zlib://http://api.stackexchange.com/2.0/questions/'.$ids.'?key='.$apikey.'&site='.$site.'&order=desc&sort=activity';
    } else{
        $url = $baseurl.$ids.'/'.$type.'?key='.$apikey.'&site='.$site.'&order=desc&sort=activity&filter=default';
    }
    $results = json_decode(file_get_contents($url, false, stream_context_create(array('http'=>array('header'=>"Accept-Encoding: gzip\r\n")))));
    $results = sanitizeObject($results);
    return $results;
}

add_action('wp_print_styles', 'stackOverflowLeaderboardCSS');
add_action('widgets_init', create_function('', "return register_widget('StackOverflowLeaderboard');"))

?>