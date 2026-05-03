<?php

namespace App\Http\Controllers\Dev;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class InspectRequestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $allData = $this->gatherRequestData($request);
        $filteredData = $this->filterRequestData($allData, $request);

        return response()->json($filteredData, options: 64 | 256 | 512);
    }

    protected function gatherRequestData(Request $request): array
    {
        return [
            'method' => $request->getMethod(),
            'query_params' => $request->query(),
            'content_type' => $request->header('content-type'),
            'accept' => $request->header('accept'),
            'headers' => Arr::mapWithKeys(
                (array) $request->headers->all(),
                fn ($v, $k) => [$k => implode('', (array) $v)],
            ),
            'body' => $request->all(),
            'body_raw' => $request->getContent(),
        ];
    }

    protected function filterRequestData(array $data, Request $request): array
    {
        $onlyParam = $request->input('only');

        if (!$onlyParam) {
            return $data;
        }

        $keysToKeep = $this->parseOnlyParameter($onlyParam);

        if (!$keysToKeep) {
            return $data;
        }

        if (static::inOnlyParam('raw', $keysToKeep)) {
            $keysToKeep[] = 'body_raw';
        }

        if (static::inOnlyParam('methods', $keysToKeep)) {
            $keysToKeep[] = 'method';
        }

        if (static::inOnlyParam('header', $keysToKeep)) {
            $keysToKeep[] = 'headers';
        }

        if (static::inOnlyParam(['query', 'params'], $keysToKeep)) {
            $keysToKeep[] = 'query_params';
        }

        if (static::inOnlyParam('type', $keysToKeep)) {
            $keysToKeep[] = 'content_type';
        }

        return array_filter($data, fn ($key) => in_array($key, $keysToKeep, true), ARRAY_FILTER_USE_KEY);
    }

    protected static function parseStringOnlyParameter(?string $onlyParam): array
    {
        if (!$onlyParam) {
            return [];
        }

        $onlyParam = trim(trim($onlyParam ?: '', ','));
        $onlyParam = str_replace([',]', ', ]'], ']', $onlyParam);
        $onlyParam = static::isValidString($onlyParam) ? explode(',', $onlyParam) : $onlyParam;

        if (!$onlyParam) {
            return [];
        }

        if (static::isValidString($onlyParam)) {
            $onlyParam = explode(',', $onlyParam);
        }

        if (is_string($onlyParam)) {
            $onlyParam = json_validate($onlyParam) ? (array) json_decode($onlyParam, true) : explode(',', $onlyParam);
        }

        return array_filter(is_array($onlyParam) ? $onlyParam : [$onlyParam]);
    }

    protected static function isValidString(mixed $input): bool
    {
        if (!$input || !is_string($input)) {
            return false;
        }

        return preg_match('/^\s*[a-zA-Z][a-zA-Z0-9_]*\s*(?:,\s*[a-zA-Z][a-zA-Z0-9_]*\s*)*$/', trim($input)) === 1;
    }

    protected static function parseOnlyParameter(mixed $onlyParam): array
    {
        if (!in_array(gettype($onlyParam), ['array', 'string'])) {
            return [];
        }

        $keys = array_filter(
            array_map(
                fn ($v) => static::isValidString($v) ? strtolower(trim($v)) : '',
                Arr::wrap(is_string($onlyParam) ? static::parseStringOnlyParameter($onlyParam) : $onlyParam),
            ),
            fn ($v) => static::isValidString($v),
        );

        return array_values($keys);
    }

    protected static function inOnlyParam(
        string|array|null $keysToCheck,
        array $only = [],
    ): bool {
        if (!$only) {
            return false;
        }

        $only = array_filter(Arr::wrap($only ?: []), 'is_string');
        $keysToCheck = array_filter(Arr::wrap($keysToCheck ?: []), 'is_string');

        if (!$only || !$keysToCheck) {
            return false;
        }

        if (in_array('all', $only)) {
            return true;
        }

        foreach ($only as $item) {
            if (is_string($item) && in_array($item, $only)) {
                return true;
            }
        }

        return false;
    }
}
