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

use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScheduleUpdateTask extends Task{

    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onRun() : void{
        $this->plugin->getServer()->getAsyncPool()->submitTask(new UpdatePlayersTask($this->plugin->getConfig()->get('servers-to-query')));
    }
}