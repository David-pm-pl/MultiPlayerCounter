<?php

/**
 * MultiPlayerCounter plugin for PocketMine-MP
 * Copyright (C) 2022 DavidGlitch04 <https://github.com/DavidGlitch04>
 *
 * MultiPlayerCounter is licensed under the GNU General Public License v3.0 (GPL-3.0 License)
 *
 * GNU General Public License <https://www.gnu.org/licenses/>
 */
 
declare(strict_types=1);

namespace davidglitch04\MultiPlayerCounter;

use libpmquery\PMQuery;
use libpmquery\PmQueryException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use function intval;
use function utf8_decode;
use function utf8_encode;
use function serialize;
use function unserialize;
use function strval;

/**
 * Class UpdatePlayersTask
 * @package davidglitch04\MultiPlayerCounter
 */
class UpdatePlayersTask extends AsyncTask{

    /** @var string $serverData */
    private string $serversData;

    public function __construct(?array $servers){/* @phpstan-ignore-line */
        $this->serversData = utf8_encode(serialize($servers));
    }

    public function onRun() : void{
        $res = ['count' => 0, 'maxPlayers' => 0, 'errors' => []];
        $serversConfig = (array)unserialize(utf8_decode($this->serversData));
        foreach ($serversConfig as $serverinfo){
            if ($serverinfo instanceof ServerInfo){
                $ip = $serverinfo->getIp();
                $port = $serverinfo->getPort();
                try {
                    $qData = PMQuery::query($ip, $port);
                }catch (PmQueryException $e){
                    $res['errors'][] = 'Failed to query '.$serverinfo->toString().': '.$e->getMessage();
                    continue;
                }
                $res['count'] += $qData['Players'];
                $res['maxPlayers'] += $qData['MaxPlayers'];
            }
        }
        $this->setResult($res);
    }

    public function onCompletion() : void{
	    $server = Server::getInstance();
        /**@var array $res */
	    $res = (array)$this->getResult();
		$err = (array)$res['errors'];
        foreach($err as $e){
            $server->getLogger()->warning(strval($e));
        }
        $plugin = $server->getPluginManager()->getPlugin("MultiPlayerCounter");
        if($plugin instanceof Main){
            $plugin->setCachedPlayers(intval($res['count']));
            $plugin->setCachedMaxPlayers(intval($res['maxPlayers']));
        }
    }
}
