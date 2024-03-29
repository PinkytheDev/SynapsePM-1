<?php

namespace synapsepm\utils;


use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping;
use pocketmine\utils\MainLogger;

class Utils {

    const NUKKIT_RUNTIMEID_TABLE = "http://host.firebt.net/blocks.json";

    public static function initBlockRuntimeIdMapping() {
        try {
            $reflect = new \ReflectionClass(RuntimeBlockMapping::class);
            $legacyToRuntimeMap = $reflect->getProperty("legacyToRuntimeMap");
            $runtimeToLegacyMap = $reflect->getProperty("runtimeToLegacyMap");
            $bedrockKnownStates = $reflect->getProperty("bedrockKnownStates");

            $legacyToRuntimeMap->setAccessible(true);
            $runtimeToLegacyMap->setAccessible(true);
            $bedrockKnownStates->setAccessible(true);

            $registerMapping = $reflect->getMethod("registerMapping");
            $registerMapping->setAccessible(true);

            $runtimeIdMap = json_decode(file_get_contents(self::NUKKIT_RUNTIMEID_TABLE, false, stream_context_create(
                [
                    "ssl" => [
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                    ]
                ]
            )), true);

            $bedrockKnownStates->setValue($runtimeIdMap);
            $runtimeToLegacyMap->setValue([]);
            $legacyToRuntimeMap->setValue([]);

            foreach ($runtimeIdMap as $k => $obj) {
                $registerMapping->invokeArgs(null, [$k, $obj['legacy_id'], $obj['data']]);
            }

        } catch (\ReflectionException $e) {
            MainLogger::getLogger()->logException($e);
        }
    }
}