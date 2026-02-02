<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use OCP\AppFramework\Http;

/**
 * @psalm-type AllowedStatus = 100|101|102|200|201|202|203|204|205|206|207|208|226|300|301|302|303|304|305|306|307|400|401|402|403|404|405|406|407|408|409|410|411|412|413|414|415|416|417|418|422|423|424|426|428|429|431|500|501|502|503|504|505|506|507|508|509|510|511
 */
final class HttpStatus {
	private const ALLOWED = [
		100,
		101,
		102,
		200,
		201,
		202,
		203,
		204,
		205,
		206,
		207,
		208,
		226,
		300,
		301,
		302,
		303,
		304,
		305,
		306,
		307,
		400,
		401,
		402,
		403,
		404,
		405,
		406,
		407,
		408,
		409,
		410,
		411,
		412,
		413,
		414,
		415,
		416,
		417,
		418,
		422,
		423,
		424,
		426,
		428,
		429,
		431,
		500,
		501,
		502,
		503,
		504,
		505,
		506,
		507,
		508,
		509,
		510,
		511,
	];

	/**
	 * @psalm-assert-if-true AllowedStatus $status
	 */
	public static function isAllowed(int $status): bool {
		return in_array($status, self::ALLOWED, true);
	}

	/**
	 * @psalm-return AllowedStatus
	 */
	public static function normalize(int $status): int {
		if (self::isAllowed($status)) {
			/** @var AllowedStatus $status */
			return $status;
		}

		return Http::STATUS_INTERNAL_SERVER_ERROR;
	}
}
