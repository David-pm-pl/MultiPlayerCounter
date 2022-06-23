<?php

declare(strict_types=1);

namespace davidglitch04\MultiPlayerCounter;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

use function intval;
use function serialize;
use function strval;
use function unserialize;
use function utf8_decode;
use function utf8_encode;

/**
 * Class UpdatePlayersTask
 * @package davidglitch04\MultiPlayerCounter
 */
class UpdatePlayersTask extends AsyncTask {
    /**
     * @var string
     */
	private string $serversData;

	/**
	 * @param array<int, object> $servers
	 */
	public function __construct(array $servers) {
		$this->serversData = utf8_encode(serialize($servers));
	}

    /**
     * @return void
     */
	public function onRun() : void {
		$res = ['count' => 0, 'maxPlayers' => 0, 'errors' => []];
		$serversConfig = (array) unserialize(utf8_decode($this->serversData));
		foreach ($serversConfig as $serverInfo) {
			if ($serverInfo instanceof ServerInfo) {
				$status = $serverInfo->getInfo();
				if ($status["Status"] == "online") {
					$res['count'] += $status["Players"];
					$res['maxPlayers'] += $status["Max"];
				} elseif ($status["Status"] == "offline") {
					$res['errors'][] = $status["error"];
				}
			}
		}
		$this->setResult($res);
	}

    /**
     * @return void
     */
	public function onCompletion() : void {
		$server = Server::getInstance();
        $res = (array) $this->getResult();
		$err = (array) $res['errors'];
		foreach ($err as $e) {
			$server->getLogger()->warning(strval($e));
		}
		$plugin = $server->getPluginManager()->getPlugin("MultiPlayerCounter");
		if ($plugin instanceof Main) {
			$plugin->setCachedPlayers(intval($res['count']));
			$plugin->setCachedMaxPlayers(intval($res['maxPlayers']));
		}
	}
}
