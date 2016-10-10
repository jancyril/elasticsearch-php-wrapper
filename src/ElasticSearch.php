<?php
/**
 * This class utilizes the official PHP Client for Elasticsearch that can be found  here https://github.com/elastic/elasticsearch-php.
 * This class contains methods for common operation for accessing and manipulating documents in Elasticsearch.
 * This will help you avoid the repetitive task of setting up the Elasticsearch client builder everytime you need to use it.
 *
 * @author Jan Cyril Segubience <jancyrilsegubience@gmail.com>
 *
 * @link https://github.com/jancyril/elasticsearch-php-helper
 */

namespace Janitor;

use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    /**
     * Property that will contain the instance of Elasticsearch\ClientBuilder.
     *
     * @var object
     */
    private $client;

    /**
     * Property that will contain the name of the index.
     *
     * @var string
     */
    private $index = '';

    /**
     * Property that will contain the type.
     *
     * @var string
     */
    private $type = '';

    /**
     * Build a new instance of the client builder and set the elasticsearch host.
     *
     * @param object $client
     */
    public function __construct()
    {
        $client = new ClientBuilder();
        $this->client = $client->setHosts([getenv('ELASTICSEARCH_HOST')])->build();
    }

    /**
     * This will get the index.
     *
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * This will set the index.
     *
     * @param string $index
     */
    public function setIndex(string $index)
    {
        $this->index = $index;
    }

    /**
     * This will create the new index.
     *
     * @return bool|array
     */
    public function createIndex()
    {
        try {
            $response = $this->client->indices()->create(['index' => $this->getIndex()]);
        } catch (\Exception $e) {
            return false;
        }

        return $response;
    }

    /**
     * This will delete the index.
     *
     * @return bool|array
     */
    public function deleteIndex()
    {
        try {
            $response = $this->client->indices()->delete(['index' => $this->getIndex()]);
        } catch (\Exception $e) {
            return false;
        }

        return $response;
    }

    /**
     * This will get the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * This will set the type.
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * This will set the mapping of a type.
     *
     * @param array $mapping
     */
    public function setMapping(array $mapping): array
    {
        $type = $this->getType();

        $params = [
            'index' => $this->getIndex(),
            'type' => $type,
            'body' => [
                $type => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => $mapping,
                ],
            ],
        ];

        return $this->client->indices()->putMapping($params);
    }

    /**
     * This will create a new document.
     *
     * @param array $data
     *
     * @return array
     */
    public function put(array $data): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $data['id'],
            'body' => $data,
        ];

        return $this->client->index($params);
    }

    /**
     * This will insert new documents in bulk.
     *
     * @param array $data
     *
     * @return array
     */
    public function bulk(array $data): array
    {
        foreach ($data as $param) {
            $parameters['body'][] = [
                'index' => [
                    '_index' => $this->getIndex(),
                    '_type' => $this->getType(),
                    '_id' => $param['id'],
                ],
            ];

            $parameters['body'][] = $param;
        }

        return $this->client->bulk($parameters);
    }

    /**
     * This will get documents.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function all($limit = 50, $offset = 0): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'from' => $offset,
            'size' => $limit,
            'body' => [
                'query' => [
                    'match_all' => [],
                ],
                'sort' => [
                    'id' => [
                        'order' => 'asc',
                    ],
                ],
            ],
        ];

        $this->refresh();
        $response = $this->client->search($params);

        $data['total'] = $response['hits']['total'];
        $data['data'] = $this->extract($response['hits']['hits']);

        return $data;
    }

    /**
     * This will get the document by id.
     *
     * @param int $id
     *
     * @return bool|array
     */
    public function get(int $id)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $id,
        ];

        try {
            $response = $this->client->get($params);
        } catch (\Exception $e) {
            return false;
        }

        return $response['_source'];
    }

    /**
     * This will update the document by id.
     *
     * @param int   $id
     * @param array $data
     *
     * @return array
     */
    public function update(int $id, array $data)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $id,
            'body' => [
                'doc' => $data,
            ],
        ];

        try {
            $response = $this->client->update($params);
        } catch (\Exception $e) {
            return false;
        }

        return $response;
    }

    /**
     * This will delete a document.
     *
     * @param int $id
     *
     * @return array
     */
    public function delete(int $id): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $id,
        ];

        return $this->client->delete($params);
    }

    /**
     * This will search from the field and will look for a specific document that matches the given value.
     *
     * @param string     $field
     * @param string|int $value
     * @param int        $limit
     * @param int        $offset
     *
     * @return array
     */
    public function match(string $field, $value, $limit = 50, $offset = 0): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'size' => $limit,
            'from' => $offset,
            'body' => [
                'query' => [
                    'match' => [
                        $field => $value,
                    ],
                ],
            ],
        ];

        $this->refresh();
        $response = $this->client->search($params);

        $data['total'] = $response['hits']['total'];
        $data['data'] = $this->extract($response['hits']['hits']);

        return $data;
    }

    /**
     * This will search from the field and will look for documents that matches the value given
     * in a wildcard form.
     *
     * @param string     $field
     * @param string|int $value
     * @param int        $limit
     * @param int        $offset
     *
     * @return array
     */
    public function matchAny(string $field, $value, $limit = 50, $offset = 0): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'size' => $limit,
            'from' => $offset,
            'body' => [
                'query' => [
                    'query_string' => [
                        'query' => $field.':'.$value.'*',
                        'allow_leading_wildcard' => false,
                    ],
                ],
            ],
        ];

        $this->refresh();
        $response = $this->client->search($params);

        $data['total'] = $response['hits']['total'];
        $data['data'] = $this->extract($response['hits']['hits']);

        return $data;
    }

    /**
     * This will search from the multiple fields supplied and will look for documents based on the given value.
     *
     * @param array      $fields
     * @param string|int $value
     * @param int        $limit
     * @param int        $offset
     *
     * @return array
     */
    public function multiMatch(array $fields, $value, $limit = 100, $offset = 0): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'size' => $limit,
            'from' => $offset,
            'body' => [
                'query' => [
                    'query_string' => [
                        'query' => $value,
                        'fields' => $fields,
                    ],
                ],
            ],
        ];

        $this->refresh();
        $response = $this->client->search($params);

        $data['total'] = $response['hits']['total'];
        $data['data'] = $this->extract($response['hits']['hits']);

        return $data;
    }

    /**
     * This will return the number of documents inside a specific type.
     *
     * @return int
     */
    public function count(): int
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
        ];

        $this->refresh();

        return $this->client->count($params)['count'];
    }

    /**
     * This will refresh the index.
     */
    private function refresh()
    {
        $this->client->indices()->refresh(['index' => $this->getIndex()]);
    }

    /**
     * Extract from the results to return only the _source.
     *
     * @param array $data
     *
     * @return array
     */
    private function extract(array $data): array
    {
        $documents = [];

        foreach ($data as $key => $value) {
            foreach ($value as $index => $document) {
                if ($index != '_source') {
                    continue;
                }

                array_push($documents, $document);
            }
        }

        return $documents;
    }
}
