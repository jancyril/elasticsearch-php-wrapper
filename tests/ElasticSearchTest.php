<?php

use Janitor\ElasticSearch;
use Elasticsearch\ClientBuilder;

class ElasticSearchTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_can_get_the_index_to_be_used()
    {
        $elastic = new ElasticSearch;
        $elastic->setIndex('testing');

        $this->assertEquals('testing', $elastic->getIndex());
    }

    /** @test **/
    public function it_can_create_the_index_to_be_used()
    {
        $elastic = new ElasticSearch;
        $elastic->setIndex('testing');

        $this->assertArrayHasKey('acknowledged', $elastic->createIndex());
    }

    /** @test **/
    public function it_will_return_false_when_creating_an_index_that_already_exists()
    {
        $elastic = new ElasticSearch;
        $elastic->setIndex('testing');
        $elastic->createIndex();

        $this->assertFalse($elastic->createIndex());
    }

    /** @test **/
    public function it_can_delete_an_index()
    {
        $elastic = new ElasticSearch;
        $elastic->setIndex('testing');
        $elastic->createIndex();

        $this->assertArrayHasKey('acknowledged', $elastic->deleteIndex());
    }

    /** @test **/
    public function it_will_return_false_when_deleting_an_index_that_does_not_exists()
    {
        $elastic = new ElasticSearch;
        $elastic->setIndex('testing');
        $elastic->createIndex();
        $elastic->deleteIndex();

        $this->assertFalse($elastic->deleteIndex());
    }

    /** @test **/
    public function it_can_get_the_type_to_be_used()
    {
        $elastic = new ElasticSearch;
        $elastic->setType('users');

        $this->assertEquals('users', $elastic->getType());
    }

    /** @test **/
    public function it_can_setup_the_mapping()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);

        $this->assertArrayHasKey('acknowledged', $elastic->setMapping($this->mapping()));
    }

    /** @test **/
    public function it_can_create_a_new_document()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $data = ['id' => 1, 'username' => 'jancyril'];

        $this->assertArrayHasKey('created', $elastic->put($data));
    }

    /** @test **/
    public function it_can_create_dcouments_in_bulk()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $response = $elastic->bulk($this->sampleData());

        $this->assertFalse($response['errors']);
    }

    /** @test **/
    public function it_can_get_all_documents()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());
        $elastic->bulk($this->sampleData());
        $response = $elastic->all();

        $this->assertEquals(4, $response['total']);
    }

    /** @test **/
    public function it_can_get_all_documents_with_limit()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());
        $elastic->bulk($this->sampleData());
        $response = $elastic->all(2);

        $this->assertEquals(2, count($response['data']));
    }

    /** @test **/
    public function it_can_get_all_documents_with_offset()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());
        $elastic->bulk($this->sampleData());
        $response = $elastic->all(1, 1);

        $this->assertEquals(1, count($response['data']));
        $this->assertEquals(2, $response['data'][0]['id']);
    }

    /** @test **/
    public function it_can_get_a_document_by_id()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $response = $elastic->get(3);

        $this->assertEquals(3, $response['id']);
    }

    /** @test **/
    public function it_will_return_false_on_get_document_if_id_is_non_existing()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $this->assertFalse($elastic->get(5));
    }

    /** @test **/
    public function it_can_delete_a_document()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $response = $elastic->delete(3);

        $this->assertTrue($response['found']);
        $this->assertFalse($elastic->get(3));
    }

    /** @test **/
    public function it_can_update_a_document()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $data = ['username' => 'JC'];

        $elastic->update(1, $data);
        $response = $elastic->get(1);

        $this->assertEquals($data['username'], $response['username']);
    }

    /** @test **/
    public function it_will_return_false_on_updating_a_non_existing_document()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $data = ['username' => 'JC'];

        $elastic->update(5, $data);
        $this->assertFalse($elastic->get(5));
    }

    /** @test **/
    public function it_can_search_a_document_with_specific_field_and_value()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $response = $elastic->match('username', 'aceraven777');

        $this->assertEquals('aceraven777', $response['data'][0]['username']);
    }

    /** @test **/
    public function it_can_search_a_document_in_wildcard()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $response = $elastic->matchAny('username', 'fox');

        $this->assertEquals('foxlance', $response['data'][0]['username']);
    }

    /** @test **/
    public function it_will_return_empty_if_no_data_is_found_by_match_any()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $response = $elastic->matchAny('username', 'nels');

        $this->assertEmpty($response['data']);
    }

    /** @test **/
    public function it_can_search_a_document_from_multiple_fields()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $response = $elastic->multiMatch(['username', 'role'], 'admin');

        $this->assertEquals(2, $response['total']);
    }

    /** @test **/
    public function it_can_count_the_total_number_of_documents()
    {
        $elastic = new ElasticSearch;
        $this->set($elastic);
        $elastic->setMapping($this->mapping());

        $elastic->bulk($this->sampleData());

        $this->assertEquals(4, $elastic->count());
    }

    protected function tearDown()
    {
        $elastic = new ElasticSearch;
        $elastic->setIndex('testing');
        $elastic->deleteIndex();
    }

    private function set($instance)
    {
        $instance->setIndex('testing');
        $instance->createIndex();
        $instance->setType('users');
    }

    private function mapping()
    {
        return [
            'id' => [
                'type' => 'long'
            ],
            'username' => [
                'type' => 'string',
                'analyzer' => 'standard'
            ],
            'role' => [
                'type' => 'string',
                'analyzer' => 'standard'
            ]
        ];
    }

    private function sampleData()
    {
        return [
            ['id' => 1, 'username' => 'jancyril', 'role' => 'admin'],
            ['id' => 2, 'username' => 'foxlance', 'role' => 'developer'],
            ['id' => 3, 'username' => 'aceraven777', 'role' => 'developer'],
            ['id' => 4, 'username' => 'admin', 'role' => 'developer']
        ];
    }
}
