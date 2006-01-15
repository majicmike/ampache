<?php
/*

 Copyright (c) 2001 - 2006 Ampache.org
 All rights reserved.

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
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/*!
	@header Song Class
*/

class Song {

	/* Variables from DB */
	var $id;
	var $file;
	var $album;
	var $artist;
	var $title;
	var $year;
	var $comment;
	var $bitrate;
	var $rate;
	var $mode;
	var $size;
	var $time;
	var $track;
	var $genre;
	var $type;
	var $mime;
	var $played;
	var $enabled;
	var $addition_time;
	var $update_time;

	/*!
		@function Song
		@discussion Song class, for modifing a song.
		@param $song_id 	The ID of the song
	 */
	function Song($song_id = 0) {

		/* If we have passed an id then do something */
		if ($song_id) { 

			/* Assign id for use in get_info() */
			$this->id = sql_escape($song_id);

			/* Get the information from the db */
			if ($info = $this->get_info()) {

				/* Assign Vars */
				$this->file		= $info->file;
				$this->album 		= $info->album;
				$this->artist		= $info->artist;
				$this->title		= $info->title;
				$this->comment		= $info->comment;
				$this->year		= $info->year;
				$this->bitrate		= $info->bitrate;
				$this->rate		= $info->rate;
				$this->mode		= $info->mode;
				$this->size		= $info->size;
				$this->time		= $info->time;
				$this->track		= $info->track;
				$this->genre		= $info->genre;
				$this->addition_time	= $info->addition_time;
				$this->catalog		= $info->catalog;
				$this->played   	= $info->played;
				$this->update_time 	= $info->update_time;
				$this->enabled 		= $info->enabled;

				// Format the Type of the song
				$this->format_type();
			}

		}

	} //constructor


	/*!
		@function get_info
		@discussion get's the vars for $this out of the database 
		@param $this->id	Taken from the object
	*/
	function get_info() {

		/* Grab the basic information from the catalog and return it */
		$sql = "SELECT song.id,file,catalog,album,song.comment,year,artist,".
			"title,bitrate,rate,mode,size,time,track,genre,played,song.enabled,update_time,".
			"addition_time FROM song WHERE song.id = '$this->id'";
			
		$db_results = mysql_query($sql, dbh());

		$results = mysql_fetch_object($db_results);

		return $results;

	} // get_info

	/*!
		@function format_type
		@discussion gets the type of song we are trying to 
			play, used to set mime headers and to trick 
			players into playing them correctly
	*/
	function format_type() { 

		preg_match('/\.([A-Za-z0-9]+)$/', $this->file,$results);

		$this->type = $results[1];

		switch ($this->type) { 
			case "spx":
			case "ogg":
				$this->mime = "application/x-ogg";
				break;
			case "wma":
			case "WMA":
			case "asf":
				$this->mime = "audio/x-ms-wma";
				break;
			case "mp3":
			case "mpeg3":
				$this->mime = "audio/mpeg";
				break;
			case "rm":
			case "ra":
				$this->mime = "audio/x-realaudio";
				break;
			case "flac";
				$this->mime = "audio/x-flac";
				break;
			case 'aac':
			case 'mp4':
			case 'm4a':
				$this->mime = "audio/mp4";
				break;
			case 'mpc':
				$this->mime = "audio/x-musepack";
				break;
			default:
				$this->mime = "audio/mpeg";
				break;
		}

	} // get_type
	
	/*!
		@function get_album_songs
		@discussion gets an array of song objects based on album
	*/
	function get_album_songs($album_id) {

		$sql = "SELECT id FROM song WHERE album='$album_id'";
		$db_results = mysql_query($sql, dbh());

		while ($r = mysql_fetch_object($db_results)) {
			$results[] = new Song($r->id);
		}

		return $results;

	} // get_album_songs

	/*!
		@function get_album_name
		@discussion gets the name of $this->album
	*/
	function get_album_name() {

		$sql = "SELECT name,prefix FROM album WHERE id='$this->album'";
		$db_results = mysql_query($sql, dbh());

		$results = mysql_fetch_array($db_results);

		if ($results['prefix']) { 
			return $results['prefix'] . " " .$results['name'];
		}
		else {
			return $results['name'];
		}

	} // get_album_name

	/*!
		@function get_artist_name
		@discussion gets the name of $this->artist
	*/
	function get_artist_name() {

		$sql = "SELECT name,prefix FROM artist WHERE id='$this->artist'";
		$db_results = mysql_query($sql, dbh());

		$results = mysql_fetch_array($db_results);

		if ($results['prefix']) {
			return $results['prefix'] . " " . $results['name'];
		}
		else {
			return $results['name'];
		}

	} // get_album_name

	/*!
		@function get_genre_name
		@discussion gets the name of the genre
	*/
	function get_genre_name() {

		$sql = "SELECT name FROM genre WHERE id='$this->genre'";
		$db_results = mysql_query($sql, dbh());

		$results = mysql_fetch_array($db_results);

		return $results['name'];
	
	} // get_genre_name

	/**
	 * get_flags
	 * This gets any flag information this song may have, it always
	 * returns an array as it may be possible to have more then
	 * one flag
	 */
	function get_flags() { 

		$sql = "SELECT id,flag,comment FROM flagged WHERE object_type='song' AND object_id='$this->id'";
		$db_results = mysql_query($sql, dbh());

		$results = array();

		while ($r = mysql_fetch_assoc($db_results)) { 
			$results[] = $r;
		}

		return $results;
		
	} // get_flag

	/**
	 * has_flag
	 * This just returns true or false depending on if this song is flagged for something
	 * We don't care what so we limit the SELECT to 1
	 */
	function has_flag() { 

		$sql = "SELECT id FROM flagged WHERE object_type='song' AND object_id='$this->id' LIMIT 1";
		$db_results = mysql_query($sql, dbh());

		if (mysql_fetch_assoc($db_results)) { 
			return true;
		}

		return false;

	} // has_flag

	/**
	 * set_played
	 * this checks to see if the current object has been played
	 * if not then it sets it to played
	 * @package Song
	 * @catagory Class
	 */
	function set_played() { 

		if ($this->played) { 
			return true;
		}

		/* If it hasn't been played, set it! */
		$this->update_played('1');

		return true;

	} // set_played
	
	/*!
		@function compare_song_information
		@discussion this compares the new ID3 tags of a file against
			    the ones in the database to see if they have changed
			    it returns false if nothing has changes, or the true 
			    if they have.
		@param $song	The origional song object
		@param $new_song	The new version of the song
	*/
	function compare_song_information($song,$new_song) {

		if ($song->title == "No Title Found") { $song->title = false; }
	

		if (trim($song->title) != trim(stripslashes($new_song->title)) && strlen($new_song->title) > 0) { 
			$array['change']	= true;
			$array['text'] 		.= "<br />" . _("Title") . " [$song->title] " . _("updated to") . " [$new_song->title]\n";
		} // if title
		if ($song->bitrate != $new_song->bitrate) {
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Bitrate") . " [$song->bitrate] " . _("updated to") . " [$new_song->bitrate]\n";
		} // if bitrate
		if ($song->rate != $new_song->rate) { 
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Rate") . " [$song->rate] " . _("updated to") . " [$new_song->rate]\n";
		} // if rate
		if ($song->mode != $new_song->mode) { 
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Mode") . " [$song->mode] " . _("updated to") . " [$new_song->mode]\n";
		} // if mode
		if ($song->time != $new_song->time) { 
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Time") . " [$song->time] " . _("updated to") . " [$new_song->time]\n";
		} // if time
		if ($song->track != $new_song->track) {
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Track") . " [$song->track] " . _("updated to") . " [$new_song->track]\n";
		} // if track
		if ($song->size != $new_song->size) {
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Filesize") . " [$song->size] " . _("updated to") . " [$new_song->size]\n";
		} // if artist
		if ($song->artist != $new_song->artist) {
			$array['change']	= true;
			$name = $song->get_artist_name(); 
			$array['text']		.= "<br />" . _("Artist") . " [$name] " . _("updated to") . " [$new_song->f_artist]\n";
		} // if artist
		if ($song->album != $new_song->album) {
			$array['change']	= true;
			$name = $song->get_album_name() . " - " . $song->year; 
			$array['text']		.= "<br />" . _("Album") . " [$name] " . _("updated to") . " [$new_song->f_album]\n";
		} // if album
		if ($song->year != $new_song->year) { 
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Year") . " [$song->year] " . _("updated to") . " [$new_song->year]\n";
		} // if year
		if (trim(stripslashes($song->comment)) != trim(stripslashes($new_song->comment))) { 
			$array['change']	= true;
			$array['text']		.= "<br />" . _("Comment") . " [$song->comment] " . _("updated to") . " [$new_song->comment]\n";
		} // if comment
		if ($song->genre != $new_song->genre) { 
			$array['change']	= true;
			$name = $song->get_genre_name();
			$array['text']		.= "<br />" . _("Genre") . " [$name] " . _("updated to") . " [$new_song->f_genre]\n";
		} // if genre

		return $array;

	} // compare_song_information

	/*!
		@function update_song
		@discussion this is the main updater for a song it actually
			    calls a whole bunch of mini functions to update
			    each little part of the song... lastly it updates
			    the "update_time" of the song
		@param $song_id		The id of the song we are updating
		@param $new_song	A object with the new song params
	*/
	function update_song($song_id, $new_song) {

		$this->update_title($new_song->title,$song_id);
		$this->update_bitrate($new_song->bitrate,$song_id);
		$this->update_rate($new_song->rate,$song_id);
		$this->update_mode($new_song->mode,$song_id);
		$this->update_size($new_song->size,$song_id);
		$this->update_time($new_song->time,$song_id);
		$this->update_track($new_song->track,$song_id);
		$this->update_artist($new_song->artist,$song_id);
		$this->update_genre($new_song->genre,$song_id);
		$this->update_album($new_song->album,$song_id);
		$this->update_year($new_song->year,$song_id);
		$this->update_comment($new_song->comment,$song_id);
		$this->update_played(0,$song_id);
		$this->update_utime($song_id);

	} // update_song

	/*!
		@function update_year
		@discussion update the year tag
	*/
	function update_year($new_year,$song_id=0) {
		
		if ($_SESSION['userdata']['access'] === 'admin') {
			$this->update_item('year',$new_year,$song_id);
		}
	} // update_year

	/*!
		@function update_comment
		@discussion updates the comment field
	*/
	function update_comment($new_comment,$song_id=0) { 
		
		if ($_SESSION['userdata']['access'] === 'admin') {
			$this->update_item('comment',$new_comment,$song_id);
		}
	} // update_comment

	/*!
		@function update_title
		@discussion updates the title field
	*/
	function update_title($new_title,$song_id=0) {
	
		if ($_SESSION['userdata']['access'] === 'admin') { 
			$this->update_item('title',$new_title,$song_id);
		}
	} // update_title

	/*!
		@function update_bitrate
		@discussion updates the bitrate field
	*/
	function update_bitrate($new_bitrate,$song_id=0) {
		
		if ($_SESSION['userdata']['access'] === 'admin') {
			$this->update_item('bitrate',$new_bitrate,$song_id);
		}

	} // update_bitrate

	/*!
		@function update_rate
		@discussion updates the rate field
	*/
	function update_rate($new_rate,$song_id=0) {
		
		if ($_SESSION['userdata']['access'] === 'admin') {
			$this->update_item('rate',$new_rate,$song_id);
		}

	} // update_rate

	/*!
		@function update_mode
		@discussion updates the mode field
	*/
	function update_mode($new_mode,$song_id=0) {

		if ($_SESSION['userdata']['access'] === 'admin') {
			$this->update_item('mode',$new_mode,$song_id);
		}

	} // update_mode

	/*!
		@function update_size
		@discussion updates the size field
	*/
	function update_size($new_size,$song_id=0) { 
		
		if ($_SESSION['userdata']['access'] === 'admin') { 
			$this->update_item('size',$new_size,$song_id);
		}

	} // update_size

	/*!
		@function update_time
		@discussion updates the time field
	*/
	function update_time($new_time,$song_id=0) { 
		
		if ($_SESSION['userdata']['access'] === 'admin') { 
			$this->update_item('time',$new_time,$song_id);
		}

	} // update_time

	/*!
		@function update_track
		@discussion this updates the track field
	*/
	function update_track($new_track,$song_id=0) { 

		if ($_SESSION['userdata']['access'] === 'admin') { 
			$this->update_item('track',$new_track,$song_id);
		}

	} // update_track

	/*!
		@function update_artist
		@discussion updates the artist field
	*/
	function update_artist($new_artist,$song_id=0) {

		if ($_SESSION['userdata']['access'] === 'admin') { 
			$this->update_item('artist',$new_artist,$song_id);
		} 

	} // update_artist

	/*!
		@function update_genre
		@discussion updates the genre field
	*/
	function update_genre($new_genre,$song_id=0) { 

		if ($_SESSION['userdata']['access'] === 'admin') { 
			$this->update_item('genre',$new_genre,$song_id);
		}

	} // update_genre

	/*!
		@function update_album
		@discussion updates the album field
	*/
	function update_album($new_album,$song_id=0) { 

		if ($_SESSION['userdata']['access'] === 'admin') {
			$this->update_item('album',$new_album,$song_id);
		}

	} // update_album

	/*!
		@function update_utime
		@discussion sets a new update time
	*/
	function update_utime($song_id=0,$time=0) {

		if (!$time) { $time = time(); }

		if ($_SESSION['userdata']['access'] === 'admin') { 
			$this->update_item('update_time',$time,$song_id);
		}

	} // update_utime

	/*!
		@function update_played
		@discussion sets the played flag
	*/
	function update_played($new_played,$song_id=0) { 

		$this->update_item('played',$new_played,$song_id);

	} // update_played


	/*!
		@function update_enabled
		@discussion sets the enabled flag
	*/
	function update_enabled($new_enabled,$song_id=0) {

		if ($_SESSION['userdata']['access'] === 'admin' || $_SESSION['userdata']['access'] === '100') {
			$this->update_item('enabled',$new_enabled,$song_id);
		}

	} // update_enabled

	/*!
		@function update_item
		@discussion this is a generic function that is called
			    by all the other update functions... 
		@param $field	The field we are updating
		@param $value	The new value for said field
		@param $song_id	ID of the song, uses $this->id by default
	*/
	function update_item($field,$value,$song_id=0) {

		if (!$song_id) { $song_id = $this->id; }

		$value = sql_escape($value);

		$sql = "UPDATE song SET $field='$value' WHERE id='$song_id'";
		$db_results = mysql_query($sql, dbh());
	
		$this->{$field} = $value;

	} //update_item


	/*!
		@function format_song
		@discussion this takes a song object
			and formats it for display
			and returns the object cleaned up
	*/
	function format_song() {

		// Format the filename
		preg_match("/^.*\/(.*?)$/",$this->file, $short);
		$this->f_file = htmlspecialchars($short[1]);
		    
		// Format the album name
		$this->f_album_full = $this->get_album_name();
		$this->f_album = truncate_with_ellipse($this->f_album_full,conf('ellipse_threshold_album'));

		// Format the artist name
		$this->f_artist_full = $this->get_artist_name();
		$this->f_artist = truncate_with_ellipse($this->f_artist_full,conf('ellipse_threshold_artist'));

		// Format the title
		$this->f_title = truncate_with_ellipse($this->title,conf('ellipse_threshold_title'));

		// Create A link inclduing the title
		$this->f_link = "<a href=\"" . conf('web_path') . "/song.php?action=m3u&amp;song=" . $this->id . "\">$this->f_title</a>";

		// Format the Bitrate
		$this->f_bitrate = intval($this->bitrate/1000) . "-" . strtoupper($this->mode);

		// Format Genre
		$this->f_genre = $this->get_genre_name(); 

		// Format the Time
		$min = floor($this->time/60);
		$sec = sprintf("%02d", ($this->time%60) );
		$this->f_time = $min . ":" . $sec;

		// Format the size
		$this->f_size = sprintf("%.2f",($this->size/1048576));

		// Set style
		if (preg_match("/id3/", $this->flagtype)) { $this->f_style = "style=\"color: #33c;\""; }
		elseif (preg_match("/(mp3|del|sort|ren)/", $this->flagtype)) { $this->f_style = "style=\"color: #C00;\""; }
		if (!$this->enabled) { $this->f_style = "style=\"text-decoration: line-through;\""; }
		
		return true;

	} // format_song

       /*!
       *       @function       get_rel_path
       *       @discussion    returns the path of the song file stripped of the catalog path
       *			used for mpd playback 
       */
       function get_rel_path($file_path=0,$catalog_id=0) {
       
		if (!$file_path) { 
			$info = $this->get_info( );
			$file_path = $info->file;
		}
		if (!$catalog_id) { 
			$catalog_id = $info->catalog;
		}
	        $catalog = new Catalog( $catalog_id );
                $info = $catalog->get_info( );
                $catalog_path = $info->path;
		$catalog_path = rtrim($catalog_path, "/");
                return( str_replace( $catalog_path . "/", "", $file_path ) );
	       
       } // get_rel_path


	/*! 
		@function fill_info
		@discussion this takes the $results from getid3 and attempts to fill
			as much information as possible from the file name using the
			pattern set in the current catalog
	*/
	function fill_info($results,$pattern,$catalog_id,$key) { 

		$filename = $this->get_rel_path($results['file'],$catalog_id);

		if (!strlen($results[$key]['title'])) { 
			$results[$key]['title']		= $this->get_info_from_filename($filename,$pattern,"%t");
		}
		if (!strlen($results[$key]['track'])) { 
			$results[$key]['track']		= $this->get_info_from_filename($filename,$pattern,"%T");
		}
		if (!strlen($results[$key]['year'])) { 
			$results[$key]['year']		= $this->get_info_from_filename($filename,$pattern,"%y");
		}
		if (!strlen($results[$key]['album'])) { 
			$results[$key]['album']		= $this->get_info_from_filename($filename,$pattern,"%A");
		}
		if (!strlen($results[$key]['artist'])) { 
			$results[$key]['artist']	= $this->get_info_from_filename($filename,$pattern,"%a");
		}
		if (!strlen($results[$key]['genre'])) { 
			$results[$key]['genre']		= $this->get_info_from_filename($filename,$pattern,"%g");
		}

		return $results;

	} // fill_info

	/*!	
		@function get_info_from_filename
		@discussion get information from a filename based on pattern
	*/
	function get_info_from_filename($file,$pattern,$tag) { 

                $preg_pattern = preg_replace("/$tag/","(.+)",$pattern);
                $preg_pattern = preg_replace("/\%\w/",".+",$preg_pattern);
                $preg_pattern = "/" . str_replace("/","\/",$preg_pattern) . "\..+/";

		preg_match($preg_pattern,$file,$matches);

		return stripslashes($matches[1]);

	} // get_info_from_filename
	
	/*!
		@function native_stream
		@discussion returns true if the $song->type streams ok, false if it must be transcoded to stream
	*/
	function native_stream() {
		
		switch ($this->type) {
			//TODO: fill out these cases once we have it working for m4a
			case "m4a":
				$return = false;
			break;
			default:
				$return = true;
			break;
		}	// end switch
		
		return $return;

	} // end native_stream
	
	/**
	 * stream_cmd
	 * test if the song type streams natively and 
	 * if not returns a transcoding command from the config
	 */
	function stream_cmd() {
	
		$return = 'downsample_cmd';
		
		if (!$this->native_stream()) {
			switch ($this->type) {
				case "m4a":
					$return = "stream_cmd_m4a";
				break;
				default:
					$return = "downsample_cmd";
				break;
			} // end switch
		} // end if not native_stream
		
		return $return;
	} // end stream_cmd

} // end of song class

?>
