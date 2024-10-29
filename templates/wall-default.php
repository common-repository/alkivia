<?php
/**
 * Default template for site activity wall.
 * Templates for the site Wall must be prefixed with 'wall-'.
 *
 * There are available this variables:
 * 		- text_domain: Translations textDomain.
 * 		- items: An array with the activity items.
 *
 * @version		$Rev: 203885 $
 * @author		Jordi Canals
 * @copyright   Copyright (C) 2009, 2010 Jordi Canals
 * @license		GNU General Public License version 2
 * @link		http://alkivia.org
 * @package		Alkivia
 * @subpackage	Community
 *

	Copyright 2009, 2010 Jordi Canals <devel@jcanals.cat>

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	version 2 as published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

?>

<div id="wall">

<?php foreach ( $items as $item ) :
    echo '<p>' . $item['avatar'];
    echo '<span class="datetime">' . ak_time_ago($item['date'], $i18n) . '</span><br />' . $item['text'] . '</p>';
endforeach; ?>
</div>
