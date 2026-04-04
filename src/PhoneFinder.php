<?php

namespace PhoneFinder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PhoneFinder
{
    private const BASE_URL = 'https://api.enrow.io';

    private static function request(string $apiKey, string $method, string $path, ?array $body = null): array
    {
        $client = new Client(['base_uri' => self::BASE_URL]);

        $options = [
            'headers' => [
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        try {
            $response = $client->request($method, $path, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $data = json_decode($e->getResponse()->getBody()->getContents(), true);
                $message = $data['message'] ?? 'API error ' . $e->getResponse()->getStatusCode();
                throw new \RuntimeException($message, $e->getResponse()->getStatusCode(), $e);
            }
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Find a phone number for a person via LinkedIn URL or name and company.
     *
     * @param string $apiKey Your Enrow API key.
     * @param array $params {
     *     @type string $linkedinUrl   LinkedIn profile URL (preferred).
     *     @type string $firstName     First name of the person.
     *     @type string $lastName      Last name of the person.
     *     @type string $companyDomain Company domain (e.g. "apple.com").
     *     @type string $companyName   Company name (e.g. "Apple Inc.").
     *     @type string $custom        Custom tracking parameter.
     *     @type string $webhook       Webhook URL for async notification.
     * }
     * @return array Search result containing an id to poll with get().
     */
    public static function find(string $apiKey, array $params): array
    {
        $body = [];

        if (!empty($params['linkedinUrl'])) {
            $body['linkedin_url'] = $params['linkedinUrl'];
        }
        if (!empty($params['firstName'])) {
            $body['first_name'] = $params['firstName'];
        }
        if (!empty($params['lastName'])) {
            $body['last_name'] = $params['lastName'];
        }
        if (!empty($params['companyDomain'])) {
            $body['company_domain'] = $params['companyDomain'];
        }
        if (!empty($params['companyName'])) {
            $body['company_name'] = $params['companyName'];
        }
        if (!empty($params['custom'])) {
            $body['custom'] = $params['custom'];
        }

        if (!empty($params['webhook'])) {
            $body['settings'] = ['webhook' => $params['webhook']];
        }

        return self::request($apiKey, 'POST', '/phone/single', $body);
    }

    /**
     * Get the result of a single phone search.
     *
     * @param string $apiKey Your Enrow API key.
     * @param string $id     The search ID returned by find().
     * @return array Phone result with number, country, qualification, etc.
     */
    public static function get(string $apiKey, string $id): array
    {
        return self::request($apiKey, 'GET', '/phone/single?id=' . urlencode($id));
    }

    /**
     * Find phone numbers for multiple people in a single batch.
     *
     * @param string $apiKey Your Enrow API key.
     * @param array $params {
     *     @type array  $searches Array of searches, each with linkedinUrl, fullName, companyDomain, companyName, custom.
     *     @type string $webhook  Webhook URL for async notification.
     * }
     * @return array Batch result containing batchId, total, status.
     */
    public static function findBulk(string $apiKey, array $params): array
    {
        $searches = array_map(function (array $search): array {
            $item = [];
            if (!empty($search['linkedinUrl'])) {
                $item['linkedin_url'] = $search['linkedinUrl'];
            }
            if (!empty($search['firstName'])) {
                $item['first_name'] = $search['firstName'];
            }
            if (!empty($search['lastName'])) {
                $item['last_name'] = $search['lastName'];
            }
            if (!empty($search['companyDomain'])) {
                $item['company_domain'] = $search['companyDomain'];
            }
            if (!empty($search['companyName'])) {
                $item['company_name'] = $search['companyName'];
            }
            if (!empty($search['custom'])) {
                $item['custom'] = $search['custom'];
            }
            return $item;
        }, $params['searches']);

        $body = ['searches' => $searches];

        if (!empty($params['webhook'])) {
            $body['settings'] = ['webhook' => $params['webhook']];
        }

        return self::request($apiKey, 'POST', '/phone/bulk', $body);
    }

    /**
     * Get the results of a bulk phone search.
     *
     * @param string $apiKey Your Enrow API key.
     * @param string $id     The batch ID returned by findBulk().
     * @return array Batch results with status, completed count, and results array.
     */
    public static function getBulk(string $apiKey, string $id): array
    {
        return self::request($apiKey, 'GET', '/phone/bulk?id=' . urlencode($id));
    }
}
