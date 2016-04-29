<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

try {
	require_once dirname(__FILE__) . '/../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}

	if (init('action') == 'install') {
		$market = market::byId(init('id'));
		if (!is_object($market)) {
			throw new Exception(__('Impossible de trouver l\'objet associé : ', __FILE__) . init('id'));
		}
		$update = update::byTypeAndLogicalId($market->getType(), $market->getLogicalId());
		if (!is_object($update)) {
			$update = new update();
			$update->setLogicalId($market->getLogicalId());
			$update->setType($market->getType());
			$update->setLocalVersion($market->getDatetime(init('version', 'stable')));
		}
		$update->setConfiguration('version', init('version', 'stable'));
		$update->save();
		$update->doUpdate();
		ajax::success();
	}

	if (init('action') == 'remove') {
		$market = market::byId(init('id'));
		if (!is_object($market)) {
			throw new Exception(__('Impossible de trouver l\'objet associé : ', __FILE__) . init('id'));
		}
		$update = update::byTypeAndLogicalId($market->getType(), $market->getLogicalId());
		if (is_object($update)) {
			$update->remove();
		} else {
			$market->remove();
		}
		ajax::success();
	}

	if (init('action') == 'save') {
		$market_ajax = json_decode(init('market'), true);
		try {
			$market = market::byId($market_ajax['id']);
		} catch (Exception $e) {
			$market = new market();
		}
		if (isset($market_ajax['rating'])) {
			unset($market_ajax['rating']);
		}
		utils::a2o($market, $market_ajax);
		$market->save();
		ajax::success();
	}

	if (init('action') == 'getInfo') {
		ajax::success(repo_market::getInfo(init('logicalId')));
	}

	if (init('action') == 'byLogicalId') {
		if (init('noExecption', 0) == 1) {
			try {
				ajax::success(utils::o2a(market::byLogicalIdAndType(init('logicalId'), init('type'))));
			} catch (Exception $e) {
				ajax::success();
			}
		} else {
			ajax::success(utils::o2a(market::byLogicalIdAndType(init('logicalId'), init('type'))));
		}
	}

	if (init('action') == 'setRating') {
		$market = market::byId(init('id'));
		if (!is_object($market)) {
			throw new Exception(__('Impossible de trouver l\'objet associé : ', __FILE__) . init('id'));
		}
		$market->setRating(init('rating'));
		ajax::success();
	}

	if (init('action') == 'sendReportBug') {
		$ticket = json_decode(init('ticket'), true);
		market::saveTicket($ticket);
		ajax::success(array('url' => config::byKey('market::address') . '/index.php?v=d&p=ticket'));
	}

	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
?>
