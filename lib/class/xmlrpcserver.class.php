<?php
/*

 Copyright (c) 2001 - 2007 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/**
 * xmlRpcServer
 * This class contains all the methods that the /server/xmlrpc.server.php will respond to 
 * to add a new method, just define a new public static function in here and it will be automagicaly
 * populated to xmlrpcserver.<FUNCTION> in /server/xmlrpc.server.php 
 */

class xmlRpcServer {

	/**
 	 * get_catalogs
	 * This returns a list of the current non-remote catalogs hosted on this Ampache instance
	 * It requires a key be passed as the first element
	 * //FIXME: USE TOKEN!
	 */
	public static function get_catalogs($xmlrpc_object) { 

		// Pull out the key
		$variable = $xmlrpc_object->getParam(0); 
		$key = $variable->scalarval(); 

		// Check it and make sure we're super green
		if (!Access::check_network('rpc',$_SERVER['REMOTE_ADDR'],'','5',$key)) { 
			debug_event('XMLSERVER','Error ' . $_SERVER['REMOTE_ADDR'] . ' with key ' . $key . ' does not match any ACLs','1'); 
			return new xmlrpcresp(0,'503','Key/IP Mis-match Access Denied'); 
		} 

		// Go ahead and gather up the information they are legit
		$results = array(); 

		$sql = "SELECT `catalog`.`name`,COUNT(`song`.`id`) AS `count`,`catalog`.`id` AS `catalog_id` FROM `catalog` ". 
			"LEFT JOIN `song` ON `catalog`.`id`=`song`.`catalog` WHERE `catalog`.`catalog_type`='local' " . 
			"GROUP BY `catalog`.`id`"; 
		$db_results = Dba::query($sql); 

		while ($row = Dba::fetch_assoc($db_results)) { 
			$results[] = $row; 
		} 

		// We need to set time limit at this point as who know how long this data is going to take
		// to return to the client
		set_time_limit(0); 

		$encoded_array = php_xmlrpc_encode($results); 
		debug_event('XMLSERVER','Returning data about ' . count($results) . ' catalogs to ' . $_SERVER['REMOTE_ADDR'],'5'); 

		return new xmlrpcresp($encoded_array); 

	} // get_catalogs

	/**
	 * get_songs
	 * This is a basic function to return all of the song data in a serialized format. It takes a start and end point
	 * as well as the TOKEN for auth mojo
	 * //FIXME: USE TOKEN!
  	 */
	public static function get_songs($xmlrpc_object) { 

		// We're going to be here a while
		set_time_limit(0); 

                // Pull out the key
                $variable = $xmlrpc_object->getParam(0);
                $key = $variable->scalarval();

                // Check it and make sure we're super green
                if (!Access::check_network('rpc',$_SERVER['REMOTE_ADDR'],'','5',$key)) {
                        debug_event('XMLSERVER','Error ' . $_SERVER['REMOTE_ADDR'] . ' with key ' . $key . ' does not match any ACLs','1');
                        return new xmlrpcresp(0,'503','Key/IP Mis-match Access Denied');
                }
		
		// Now pull out the start and end
		$start	= intval($xmlrpc_object->params['1']->me['int']); 
		$end	= intval($xmlrpc_object->params['2']->me['int']);

		// Get Catalogs first
		$sql = "SELECT `catalog`.`id` FROM `catalog` WHERE `catalog`.`catalog_type`='local'"; 
		$db_results = Dba::query($sql); 

		while ($row = Dba::fetch_assoc($db_results)) { 
			$where_sql .= "`song`.`catalog`='" . $row['id'] . "' OR"; 
		} 

		$where_sql = rtrim($where_sql,'OR'); 

		$sql = "SELECT `song`.`id` FROM `song` WHERE `song`.`enabled`='1' AND ($where_sql) LIMIT $start,$end"; 
		$db_results = Dba::query($sql); 

		while ($row = Dba::fetch_assoc($db_results)) { 
			$song = new Song($row['id']); 
			$song->fill_ext_info(); 
			$song->album	= $song->get_album_name(); 
			$song->artist	= $song->get_artist_name(); 
			$song->genre	= $song->get_genre_name(); 

			$output = serialize($song); 
			$results[] = $output; 
		} // end while

		$encoded_array = php_xmlrpc_encode($results); 
		debug_event('XMLSERVER','Encoded ' . count($results) . ' songs (' . $start . ',' . $end . ')','5'); 

		return new xmlrpcresp($encoded_array);

	} // get_songs

	/**
	 * handshake
	 * This should be run before any other XMLRPC actions, it checks the KEY encoded with a timestamp then returns a valid TOKEN to be
	 * used in all further communication 
	 */
	public static function handshake($xmlrpc_object) { 

		// Pull out the params
		$encoded_key 	= $xmlrpc_object->params['0']->me['string']; 
		$timestamp	= $xmlrpc_object->params['0']->me['int'];

	} // handshake

} // xmlRpcServer
?>