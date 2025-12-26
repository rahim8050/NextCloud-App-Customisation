<?php

declare(strict_types=1);

namespace OCA\WeatherApis\Service;

use Closure;
use InvalidArgumentException;

final class UrlValidator {
	private const IPV4_BLOCKED_RANGES = [
		['0.0.0.0', '0.255.255.255'],
		['10.0.0.0', '10.255.255.255'],
		['100.64.0.0', '100.127.255.255'],
		['127.0.0.0', '127.255.255.255'],
		['169.254.0.0', '169.254.255.255'],
		['172.16.0.0', '172.31.255.255'],
		['192.0.0.0', '192.0.0.255'],
		['192.0.2.0', '192.0.2.255'],
		['192.88.99.0', '192.88.99.255'],
		['192.168.0.0', '192.168.255.255'],
		['198.18.0.0', '198.19.255.255'],
		['198.51.100.0', '198.51.100.255'],
		['203.0.113.0', '203.0.113.255'],
		['224.0.0.0', '239.255.255.255'],
		['240.0.0.0', '255.255.255.255'],
	];

	private const IPV6_BLOCKED_RANGES = [
		['::', 128],
		['::1', 128],
		['fc00::', 7],
		['fe80::', 10],
		['fec0::', 10],
		['ff00::', 8],
		['2001:db8::', 32],
	];

	private readonly ?Closure $dnsResolver;

	public function __construct(?callable $dnsResolver = null) {
		$this->dnsResolver = $dnsResolver === null ? null : Closure::fromCallable($dnsResolver);
	}

	/**
	 * @param string $url
	 * @param bool $devAllowInsecureLocalHttp
	 * @param string $devAllowlistHosts
	 *
	 * @throws InvalidArgumentException
	 */
	public function validate(string $url, bool $devAllowInsecureLocalHttp, string $devAllowlistHosts): void {
		$parts = parse_url($url);
		if ($parts === false) {
			throw new InvalidArgumentException('Unable to parse the URL.');
		}

		$scheme = strtolower($parts['scheme'] ?? '');
		if ($scheme === '') {
			throw new InvalidArgumentException('URL scheme is required.');
		}

		if (!in_array($scheme, ['http', 'https'], true)) {
			throw new InvalidArgumentException('Only http/https schemes are supported.');
		}

		if (isset($parts['user']) || isset($parts['pass'])) {
			throw new InvalidArgumentException('URLs with embedded credentials are not allowed.');
		}

		$host = $parts['host'] ?? '';
		if ($host === '') {
			throw new InvalidArgumentException('URL host is required.');
		}

		$normalizedHost = strtolower($host);
		if ($normalizedHost === 'localhost') {
			throw new InvalidArgumentException('Localhost hosts are disallowed.');
		}

		$allowlist = $this->normalizeAllowlist($devAllowlistHosts);
		$hostIsAllowlisted = in_array($normalizedHost, $allowlist, true);
		$devOverrideActive = $devAllowInsecureLocalHttp && $hostIsAllowlisted;

		if ($scheme !== 'https' && !$devOverrideActive) {
			throw new InvalidArgumentException('Insecure URLs require the dev override.');
		}

		$resolvedIps = $this->resolveHost($host);
		if ($resolvedIps === []) {
			throw new InvalidArgumentException('DNS resolution failed for the configured host.');
		}

		foreach ($resolvedIps as $ip) {
			if ($this->isBlockedIp($ip) && !$devOverrideActive) {
				throw new InvalidArgumentException('URL resolves to a blocked IP address.');
			}
		}
	}

	/**
	 * @return array<int, string>
	 */
	private function normalizeAllowlist(string $value): array {
		if ($value === '') {
			return [];
		}

		$entries = preg_split('/[\r\n,]+/', $value);
		if ($entries === false) {
			return [];
		}

		$normalized = [];
		foreach ($entries as $entry) {
			$trimmed = trim((string)$entry);
			if ($trimmed === '') {
				continue;
			}

			$normalized[] = strtolower($trimmed);
		}

		return array_values(array_unique($normalized));
	}

	/**
	 * @return array<int, string>
	 */
	private function resolveHost(string $host): array {
		if (filter_var($host, FILTER_VALIDATE_IP)) {
			return [$host];
		}

		$resolver = $this->dnsResolver ?? fn (string $target): array => $this->performDnsResolution($target);
		$addresses = $resolver($host);
		if ($addresses === null) {
			return [];
		}

		$filtered = array_filter($addresses, static fn (string $value): bool => $value !== '');

		return array_values(array_unique($filtered));
	}

	/**
	 * @return array<int, string>
	 */
	private function performDnsResolution(string $host): array {
		$results = [];

		$types = [
			DNS_A => ['ip'],
			DNS_AAAA => ['ipv6'],
		];

		foreach ($types as $type => $fields) {
			$records = dns_get_record($host, $type);
			if ($records === false) {
				continue;
			}

			foreach ($records as $record) {
				foreach ($fields as $field) {
					if (isset($record[$field])) {
						$results[] = $record[$field];
					}
				}
			}
		}

		return $results;
	}

	private function isBlockedIp(string $ip): bool {
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return $this->isIpv4Blocked($ip);
		}

		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return $this->isIpv6Blocked($ip);
		}

		return true;
	}

	private function isIpv4Blocked(string $ip): bool {
		$value = $this->ipv4ToUint($ip);
		foreach (self::IPV4_BLOCKED_RANGES as [$start, $end]) {
			$startValue = $this->ipv4ToUint($start);
			$endValue = $this->ipv4ToUint($end);
			if ($value >= $startValue && $value <= $endValue) {
				return true;
			}
		}

		return false;
	}

	private function isIpv6Blocked(string $ip): bool {
		foreach (self::IPV6_BLOCKED_RANGES as [$network, $mask]) {
			if ($this->ipv6Matches($ip, $network, $mask)) {
				return true;
			}
		}

		return false;
	}

	private function ipv4ToUint(string $ip): float {
		$value = ip2long($ip);
		if ($value === false) {
			return 0.0;
		}

		if ($value >= 0) {
			return $value;
		}

		return 0x100000000 + $value;
	}

	private function ipv6Matches(string $ip, string $network, int $mask): bool {
		$ipBits = $this->ipv6ToBinary($ip);
		$networkBits = $this->ipv6ToBinary($network);

		if ($ipBits === '' || $networkBits === '') {
			return false;
		}

		$mask = min(max($mask, 0), strlen($ipBits));

		return strncmp($ipBits, $networkBits, $mask) === 0;
	}

	private function ipv6ToBinary(string $ip): string {
		$packed = inet_pton($ip);
		if ($packed === false) {
			return '';
		}

		$bits = '';
		for ($i = 0, $length = strlen($packed); $i < $length; $i++) {
			$bits .= str_pad(decbin(ord($packed[$i])), 8, '0', STR_PAD_LEFT);
		}

		return $bits;
	}
}
