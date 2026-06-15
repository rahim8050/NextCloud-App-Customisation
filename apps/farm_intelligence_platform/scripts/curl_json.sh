#!/usr/bin/env bash

set -euo pipefail

die() {
	printf 'ERROR: %s\n' "$1" >&2
	exit 1
}

method="POST"
url=""
body=""
file=""

while [[ $# -gt 0 ]]; do
	case "$1" in
		--get)
			method="GET"
			shift
			;;
		--file)
			shift
			if [[ $# -eq 0 ]]; then
				die 'Missing path after --file'
			fi
			if [[ -n "$file" ]]; then
				die 'Multiple --file options are not allowed'
			fi
			file="$1"
			shift
			;;
		--)
			shift
			break
			;;
		-*)
			die "Unknown option: $1"
			;;
		*)
			if [[ -z "$url" ]]; then
				url="$1"
			elif [[ -z "$body" ]]; then
				body="$1"
			else
				die "Unexpected argument: $1"
			fi
			shift
			;;
	esac
done

if [[ -z "$url" ]]; then
	die 'A URL is required as the first argument'
fi

clean_url="${url//$'\r'/}"
clean_url="${clean_url//$'\n'/}"

if [[ "$clean_url" != "$url" ]]; then
	url="$clean_url"
fi

if [[ -z "$url" ]]; then
	die 'The provided URL is empty after stripping CR/LF'
fi

if [[ "$url" == *[[:space:]]* ]]; then
	die 'The URL must not contain whitespace characters'
fi

if [[ "$method" == "GET" ]]; then
	if [[ -n "$file" || -n "$body" ]]; then
		die 'GET mode does not accept a request body'
	fi
	curl -sS -X GET "$url"
	exit
fi

if [[ -n "$file" && -n "$body" ]]; then
	die 'Specify a body string or --file, not both'
fi

if [[ -n "$file" ]]; then
	if [[ ! -f "$file" ]]; then
		die "Payload file not found: $file"
	fi
	body=$(< "$file")
fi

if [[ -z "$body" ]]; then
	die 'A JSON body is required for POST requests (string or --file)'
fi

curl -sS -X POST "$url" \
	-H 'Content-Type: application/json' \
	--data-binary "$body"
