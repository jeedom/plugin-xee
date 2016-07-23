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

/* * ***************************Includes********************************* */

class xee extends eqLogic {
	/*     * *************************Attributs****************************** */

	private $_collectDate = '';
	public static $_widgetPossibility = array('custom' => true);

	/*     * ***********************Methode static*************************** */

	public static function cron15() {
		log::add('xee','debug','Mise à jour des infos');
		self::refresh_info();
	}
	
	public static function cronHourly() {
		log::add('xee','debug','Mise à jour du token');
		self::refresh_token();
	}
	
	public static function createCars() {
		$access_token = config::byKey('access_token', 'xee', 0);
		$user= self::get_user($access_token);
		log::add('xee','debug',print_r($user,true));
		$userJson = json_decode($user,true);
		$userId = $userJson['id'];
		config::save('userId', $userId,'xee');
		$cars= self::get_cars($access_token,$userId);
		log::add('xee','debug',print_r($cars,true));
		$carsJson = json_decode($cars,true);
		foreach ($carsJson as $car){
			$carid= $car['id'];
			$carname = $car['name'];
			$caryear = $car['year'];
			$carmake = $car['make'];
			$carmodel = $car['model'];
			$carplatenumber = $car['plateNumber'];
			$xeeId = $car['deviceId'];
			$xeeCardId = $car['cardbId'];
			$eqLogic = xee::byLogicalId($carid, 'xee');
			if (!is_object($eqLogic)) {
				$eqLogic = new self();
				$eqLogic->setLogicalId($carid);
				$eqLogic->setName($carname.$carid);
				$eqLogic->setEqType_name('xee');
				$eqLogic->setIsVisible(1);
				$eqLogic->setIsEnable(1);
			}
			$eqLogic->setConfiguration('caryear',$caryear);
			$eqLogic->setConfiguration('carname',$carname);
			$eqLogic->setConfiguration('carmake',$carmake);
			$eqLogic->setConfiguration('carmodel',$carmodel);
			$eqLogic->setConfiguration('carplatenumber',$carplatenumber);
			$eqLogic->setConfiguration('xeeId',$xeeId);
			$eqLogic->setConfiguration('xeeCardId',$xeeCardId);
			$eqLogic->save();
		}
		self::refresh_info();
	}
	
	public static function get_cars($_access_token,$_userId) {
		$http = new com_http("https://cloud.xee.com/v3/users/" . $_userId . "/cars");
		$http->setHeader(array(
                    'Content-Type : application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $_access_token)
					);
		$cars = $http->exec();
		return $cars;
	}
	
	public static function get_user($_access_token) {
		$http = new com_http("https://cloud.xee.com/v3/users/me");
		$http->setHeader(array(
                    'Content-Type : application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $_access_token)
					);
		$user = $http->exec();
		return $user;
	}
	
	public static function get_car($_access_token,$_carId) {
		$http = new com_http("https://cloud.xee.com/v3/cars/" . $_carId);
		$http->setHeader(array(
                    'Content-Type : application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $_access_token)
					);
		$car = $http->exec();
		return $car;
	}
	
	public static function get_car_status($_access_token,$_carId) {
		$http = new com_http("https://cloud.xee.com/v3/cars/" . $_carId . "/status");
		$http->setHeader(array(
                    'Content-Type : application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $_access_token)
					);
		$carstatus = $http->exec();
		return $carstatus;
	}
	
	public static function get_car_locations($_access_token,$_carId) {
		$http = new com_http("https://cloud.xee.com/v3/cars/" . $_carId . "/locations");
		$http->setHeader(array(
                    'Content-Type : application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $_access_token)
					);
		$carlocations = $http->exec();
		return $carlocations;
	}
	
	public static function get_car_signals($_access_token,$_carId) {
		$http = new com_http("https://cloud.xee.com/v3/cars/" . $_carId . "/signals");
		$http->setHeader(array(
                    'Content-Type : application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $_access_token)
					);
		$carsignals = $http->exec();
		return $carsignals;
	}
	
	public static function get_car_trips($_access_token,$_carId) {
		$http = new com_http("https://cloud.xee.com/v3/cars/" . $_carId . "/trips");
		$http->setHeader(array(
                    'Content-Type : application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $_access_token)
					);
		$cartrips = $http->exec();
		return $cartrips;
	}
	
	public static function refresh_token() {
		$access_token = config::byKey('access_token', 'xee', 0);
		$refresh_token = config::byKey('refresh_token', 'xee', 0);
		$clientid = config::byKey('clientid', 'xee', '0');
		$clientsecret = config::byKey('clientsecret', 'xee', '0');
		$cmd =  "curl -v -X POST -u " . $clientid . ":" . $clientsecret ." -d 'grant_type=refresh_token&refresh_token=" . $refresh_token . "' https://cloud.xee.com/v3/auth/access_token";
		$return = shell_exec($cmd);
		log::add('xee','debug',$return);
		$returnencoded = json_decode($return,true);
		$access_token = $returnencoded['access_token'];
		$refresh_token = $returnencoded['refresh_token'];
		config::save('access_token', $access_token,'xee');
		config::save('refresh_token', $refresh_token,'xee');
	}
	
	public static function refresh_info() {
		$userId = config::byKey('userId', 'xee', 0);
		$access_token = config::byKey('access_token', 'xee', 0);
		$cars= self::get_cars($access_token,$userId);
		log::add('xee','debug',print_r($cars,true));
		$carsJson = json_decode($cars,true);
		foreach ($carsJson as $car){
			$carid= $car['id'];
			$carname = $car['name'];
			$caryear = $car['year'];
			$carmake = $car['make'];
			$carmodel = $car['model'];
			$carplatenumber = $car['plateNumber'];
			$xeeId = $car['deviceId'];
			$xeeCardId = $car['cardbId'];
			$eqLogic = xee::byLogicalId($carid, 'xee');
			if (!is_object($eqLogic)) {
				$eqLogic = new self();
				$eqLogic->setLogicalId($carid);
				$eqLogic->setName($carname.$carid);
				$eqLogic->setEqType_name('xee');
				$eqLogic->setIsVisible(1);
				$eqLogic->setIsEnable(1);
			}
			$eqLogic->setConfiguration('caryear',$caryear);
			$eqLogic->setConfiguration('carname',$carname);
			$eqLogic->setConfiguration('carmake',$carmake);
			$eqLogic->setConfiguration('carmodel',$carmodel);
			$eqLogic->setConfiguration('carplatenumber',$carplatenumber);
			$eqLogic->setConfiguration('xeeId',$xeeId);
			$eqLogic->setConfiguration('xeeCardId',$xeeCardId);
			$eqLogic->save();
			$status= self::get_car_status($access_token,$carid);
			log::add('xee','debug',print_r($status,true));
			$statusJson = json_decode($status,true);
			foreach ($statusJson['signals'] as $signal){
				$name = $signal['name'];
				$value = $signal['value'];
				$cmd = $eqLogic->getCmd(null, $name);
				if (!is_object($cmd)) {
					$cmd = new xeecmd();
					$cmd->setLogicalId($name);
					$cmd->setIsVisible(1);
					$cmd->setName(__($name, __FILE__));
				}
				$cmd->setType('info');
				if (substr($name, -3) == 'Sts'){
					$cmd->setSubType('binary');
				} else {
					$cmd->setSubType('numeric');
				}
				$cmd->setEqLogic_id($eqLogic->getId());
				$cmd->save();
				if (($cmd->execCmd() != $cmd->formatValue($value)) || $cmd->execCmd() == '' ) {
					$cmd->setCollectDate('');
					$cmd->event($value);
				}
			}
			$altitude = $statusJson['location']['altitude'];
			$latitude = $statusJson['location']['latitude'];
			$longitude = $statusJson['location']['longitude'];
			$direction = $statusJson['location']['heading'];
			$satellites = $statusJson['location']['satellites'];
			$altitudecmd = $eqLogic->getCmd(null, 'altitude');
			if (is_object($altitudecmd)) {
				if ($altitudecmd->execCmd() != $altitudecmd->formatValue($altitude)) {
					$altitudecmd->setCollectDate('');
					$altitudecmd->event($altitude);
				}
			}
			$latitudecmd = $eqLogic->getCmd(null, 'latitude');
			if (is_object($latitudecmd)) {
				if ($latitudecmd->execCmd() != $latitudecmd->formatValue($latitude)) {
					$latitudecmd->setCollectDate('');
					$latitudecmd->event($latitude);
				}
			}
			$longitudecmd = $eqLogic->getCmd(null, 'longitude');
			if (is_object($longitudecmd)) {
				if ($longitudecmd->execCmd() != $longitudecmd->formatValue($longitude)) {
					$longitudecmd->setCollectDate('');
					$longitudecmd->event($longitude);
				}
			}
			$directioncmd = $eqLogic->getCmd(null, 'direction');
			if (is_object($directioncmd)) {
				if ($directioncmd->execCmd() != $directioncmd->formatValue($direction)) {
					$directioncmd->setCollectDate('');
					$directioncmd->event($direction);
				}
			}
			$satellitescmd = $eqLogic->getCmd(null, 'satellites');
			if (is_object($satellitescmd)) {
				if ($satellitescmd->execCmd() != $satellitescmd->formatValue($satellites)) {
					$satellitescmd->setCollectDate('');
					$satellitescmd->event($satellites);
				}
			}
		}
		
	}

	/*     * *********************Methode d'instance************************* */

	public function postSave() {
		$altitude = $this->getCmd(null, 'altitude');
		if (!is_object($altitude)) {
			$altitude = new xeecmd();
			$altitude->setLogicalId('altitude');
			$altitude->setIsVisible(1);
			$altitude->setName(__('Altitude', __FILE__));
		}
		$altitude->setType('info');
		$altitude->setSubType('numeric');
		$altitude->setEqLogic_id($this->getId());
		$altitude->save();
		
		$latitude = $this->getCmd(null, 'latitude');
		if (!is_object($latitude)) {
			$latitude = new xeecmd();
			$latitude->setLogicalId('latitude');
			$latitude->setIsVisible(1);
			$latitude->setName(__('Latitude', __FILE__));
		}
		$latitude->setType('info');
		$latitude->setSubType('numeric');
		$latitude->setEqLogic_id($this->getId());
		$latitude->save();
		
		$longitude = $this->getCmd(null, 'longitude');
		if (!is_object($longitude)) {
			$longitude = new xeecmd();
			$longitude->setLogicalId('longitude');
			$longitude->setIsVisible(1);
			$longitude->setName(__('Longitude', __FILE__));
		}
		$longitude->setType('info');
		$longitude->setSubType('numeric');
		$longitude->setEqLogic_id($this->getId());
		$longitude->save();
		
		$direction = $this->getCmd(null, 'direction');
		if (!is_object($direction)) {
			$direction = new xeecmd();
			$direction->setLogicalId('direction');
			$direction->setIsVisible(1);
			$direction->setName(__('Direction', __FILE__));
		}
		$direction->setType('info');
		$direction->setSubType('numeric');
		$direction->setEqLogic_id($this->getId());
		$direction->save();
		
		$satellites = $this->getCmd(null, 'satellites');
		if (!is_object($satellites)) {
			$satellites = new xeecmd();
			$satellites->setLogicalId('satellites');
			$satellites->setIsVisible(1);
			$satellites->setName(__('Satellites', __FILE__));
		}
		$satellites->setType('info');
		$satellites->setSubType('numeric');
		$satellites->setEqLogic_id($this->getId());
		$satellites->save();
		
		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new xeecmd();
		}
		$refresh->setName(__('Rafraichir', __FILE__));
		$refresh->setLogicalId('refresh');
		$refresh->setEqLogic_id($this->getId());
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->save();
	}

	/*     * **********************Getteur Setteur*************************** */

}

class xeeCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = null) {
		if ($this->getType() == '') {
			return '';
		}
		xee::refresh_info();
	}

	/*     * **********************Getteur Setteur*************************** */
}
