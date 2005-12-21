<?php
/*

 Copyright (c) 2001 - 2005 Ampache.org
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
	@header Index of Ampache
	@discussion Do most of the dirty work of displaying the mp3 catalog

*/
require_once("modules/init.php");

/* We need to attempt to init the mpd object */
$myMpd = init_mpd();

show_template('header');


if (conf('refresh_limit') > 0) { show_template('javascript_refresh'); }
$action = scrub_in($_REQUEST['action']);

?>

<p style="font-size: 8pt; font-weight: bold;">
	<?php echo _("Welcome to"); ?> <a href="http://www.ampache.org/index.php">Ampache v.<?php echo conf('version'); ?></a>
</p>
<!-- Big Daddy Table -->
<table style="padding-left:5px;padding-right:5px;padding-top:5px;padding-bottom:5px;" >
<tr>
	<td style="padding-left:17px;" valign="top" colspan="2">
		<?php show_now_playing(); ?>
	</td>
</tr>	
<!--	<tr><td colspan="2">&nbsp;</td></tr>  -->
<tr>
	<td valign="top">
	<!-- Left table -->
	<table border="0">
	<tr>
		<td valign="top" align="right">
			<?php show_local_catalog_info(); ?>
		</td>
		<td valign="top" align="left">
    <?php 
			if ($user->prefs['play_type'] == 'mpd' && !conf('localplay_menu')) { 
		    		show_mpd_control(); 
			}  else {
				if ( $items = get_global_popular('album') ) { 
					show_info_box(_("Most Popular Albums"), 'album',$items);
				}
		  }
		?>
		</td>	
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>

      <?php 
      if ($user->prefs['play_type'] == 'mpd' && !conf('localplay_menu')) { 
	echo '<tr><td colspan="2" valign="top">';
	show_mpd_pl();
	echo '</td></tr>';
      }
      ?>

  <tr><td colspan="2">&nbsp;</td></tr>  
	<tr>
	        <td valign="top" align="right">
	                <?php
	                        if ( $items = get_global_popular('artist') ) {
	                                show_info_box(_("Most Popular Artists"), 'artist', $items);
	                        }
	                ?>
	        </td>
	        <td valign="top" align="left">
	                <?php
	                        if ( $items = get_global_popular('song') ) {
	                                show_info_box(_("Most Popular Songs"), 'song', $items);
	                        }
	                ?>
	        </td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
	        <td valign="top" align="right">
	                <?php
	                        if ( $items = get_newest('artist') ) {
	                                show_info_box(_("Newest Artist Additions"), '', $items);
	                        }
	                ?>
	        </td>
	        <td valign="top" align="left">
	                <?php
	                        if ( $items = get_newest('album') ) {
	                                show_info_box(_("Newest Album Additions"), '', $items);
	                        }
	                ?>
	        </td>
	</tr>
	</table>
	</td>
	<td valign="top">
	<!-- Right table -->
	<table border="0">
	<tr>
		<td valign="top" rowspan="7">
		<?php 
		    if($user->prefs['play_type'] == 'local_play') {
		        show_local_control();
		        echo "<br />";
		    } else {
			echo "&nbsp;";
		    }
		?>
		</td>
	</tr>
	</table>
	</td>
	<!-- End Right Table -->
</tr>
</table>

<?php show_footer(); ?>
