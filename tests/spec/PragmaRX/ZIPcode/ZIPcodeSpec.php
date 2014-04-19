<?php

namespace spec\PragmaRX\ZIPcode;

use PhpSpec\ObjectBehavior;
use PragmaRX\ZIPcode\Support\WebService;
use Prophecy\Argument;
use PragmaRX\ZIPcode\Support\Http;

class ZIPCodeSpec extends ObjectBehavior
{
	private $numberOfWebServicesAvailable = 5;

	private $webServicesExample = [

		'zip_length' => 8,

		'web_services' => [
			[
				'name' => 'testwebService',

				'url' => 'testwebService',

				'query' => '',

				'result_type' => 'json',

				'zip_format' => '99999999',

				'_check_resultado' => '1',

				'fields' => [
					'zip' => 'zip',
					'state_id' => 'uf',
					'state_name' => null,
					'city' => 'cidade',
					'neighborhood' => 'bairro',
					'street_kind' => 'tipo_logradouro',
					'street_name' => 'logradouro',
					'missing_field' => 'whatever',
				],

				'mandatory_fields' => [
					'state_id'
				],
			],
		],

	];

	private $wrongWebServiceExample = [
		'name' => 'testwebService',
		'url' => 'testwebService',
		'query' => '',
		'result_type' => 'json',
		'zip_format' => '99999999',
		'_check_resultado' => '1',
	];

	private $resultExample = [
		'resultado' => '1',
		'zip' => '20250030',
		'uf' => 'RJ',
		'cidade' => 'Rio de Janeiro',
		'bairro' => 'Estácio',
		'tipo_logradouro' => 'Rua',
		'logradouro' => 'Professor Quintino do Vale',
		'country_id' => 'BR',
		'web_service' => 'testwebService',
	];

	private $missingFieldError = ["Result field 'missing_field' was not found."];

	public function let(Http $http)
	{
		$this->beConstructedWith($http);
	}

    public function it_is_initializable()
    {
        $this->shouldHaveType('PragmaRX\ZIPcode\ZIPcode');
    }

    public function it_knows_valid_zips()
    {
    	$this->validateZip('20.250-030')->shouldBe('20250030');

    	$this->validateZip('2.0.2.5.0-0.3.0')->shouldBe('20250030');

    	$this->validateZip('20250030')->shouldBe('20250030');
    }

	public function it_know_how_to_clear_a_zip_string()
	{
		$this->clearZip('2.0.2.5.0-0.3.0')->shouldBe('20250030');
	}

    public function it_throws_on_invalid_zips()
    {
	    $this->shouldThrow('PragmaRX\ZIPcode\Exceptions\InvalidZipCode')->duringValidateZip('2');

	    $this->getCountry()->setCountryData($this->webServicesExample);

	    $this->shouldThrow('PragmaRX\ZIPcode\Exceptions\InvalidZipCode')->duringValidateZip('a');

	    $this->shouldThrow('PragmaRX\ZIPcode\Exceptions\InvalidZipCode')->duringValidateZip('2025003333');
    }

    public function it_has_webServices()
    {
    	$this->getWebServices()->shouldHaveType('PragmaRX\ZIPcode\Support\WebServices');
    }

	public function it_can_reach_zip_webServices($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->ping('testwebService')->willReturn(true);

		$this->checkZipWebServices()->shouldBe(true);
	}

	public function it_can_find_a_zip($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->consume('testwebService')->willReturn($this->resultExample);

		$this->find('20250030')->shouldHaveType('PragmaRX\ZIPcode\Support\Result');
	}

	public function it_can_change_a_country_and_load_webservices()
	{
		$this->setCountry('US');

		$this->getWebServices()->shouldHaveType('PragmaRX\ZIPcode\Support\WebServices');
	}

	public function it_throws_on_unavailable_country()
	{
		$this->shouldThrow('PragmaRX\ZIPcode\Exceptions\WebServicesNotFound')->duringSetCountry('ZZ');
	}

	public function it_correctly_get_an_results()
	{
		$this->getResult()->shouldHaveType('PragmaRX\ZIPcode\Support\Result');
	}

	public function it_gets_a_null_zip_after_instantiation()
	{
		$this->getZip()->shouldBe(null);
	}

	public function it_gets_a_correct_zip_after_search($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->consume('testwebService')->willReturn($this->resultExample);

		$this->find('20250030');

		$this->getZip()->shouldBe('20250030');
	}

	public function it_gets_an_empty_list_of_errors_on_success($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->consume('testwebService')->willReturn($this->resultExample);

		$this->find('20250030');

		$this->getErrors()->shouldBe($this->missingFieldError);
	}

	public function it_successfully_clear_webservices_list()
	{
		$this->clearWebServicesList();

		$this->getWebServices()->shouldHaveCount(0);
	}

	public function it_formats_zip_correctly()
	{
		$this->formatZip('20250030', '99999-999')->shouldBe('20250-030');

		$this->formatZip('20250030', '99999999')->shouldBe('20250030');

		$this->formatZip('99750', '99999999')->shouldBe('99750');

		$this->formatZip('99750', '99.999')->shouldBe('99.750');

		$this->formatZip('123456', '9.9\9/9-9#9')->shouldBe('1.2\3/4-5#6');

		$this->formatZip('A1A1A1', '999 999')->shouldBe('A1A 1A1');
	}

	public function it_throws_on_invalid_webservice()
	{
		$this->shouldThrow('PragmaRX\ZIPcode\Exceptions\WebServicesNotFound')->duringGetWebServiceByName('ZZ');
	}

	public function it_can_find_a_webservice_by_name()
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$this->getWebServiceByName('testwebService')->shouldHaveType('PragmaRX\ZIPcode\Support\WebService');
	}

	public function it_can_set_a_zip()
	{
		$this->setZip('20.123-456');

		$this->getZip()->shouldReturn('20123456');
	}

	public function it_can_gather_information_from_zip($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$webService = $this->getWebServiceByName('testwebService');

		$http->consume('testwebService')->willReturn($this->resultExample); // returns an empty result

		$this->gatherInformationFromZip('20250-030', $webService)->shouldBe($this->resultExample);
	}

	public function it_can_set_a_country()
	{
		$this->setCountry('CA');

		$this->getCountry()->getId()->shouldBe('CA');
	}

	public function it_can_set_a_user_agent($http)
	{
		$http->setUserAgent("CA")->willReturn(null);

		$http->getUserAgent()->willReturn('CA');

		$this->setUserAgent('CA');

		$this->getUserAgent()->shouldBe('CA');
	}

	public function it_can_find_zip_by_web_service_name($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->consume('testwebService')->willReturn($this->resultExample);

		$this->find('20250030', 'testwebService')->shouldHaveType('PragmaRX\ZIPcode\Support\Result');
	}

	public function it_can_find_zip_on_specific_web_service($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->consume('testwebService')->willReturn($this->resultExample);

		$this->find('20250030', $this->getWebServiceByName('testwebService'))->shouldHaveType('PragmaRX\ZIPcode\Support\Result');
	}

	public function it_returns_non_empty_result($http)
	{
		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->consume('testwebService')->willReturn($this->resultExample);

		$this->find('20250030', $this->getWebServiceByName('testwebService'))->isEmpty()->shouldBe(false);
	}

	public function it_returns_empty_result_on_missing_mandatory_fields($http)
	{
		unset($this->resultExample['uf']);

		$this->getCountry()->setCountryData($this->webServicesExample);

		$http->consume('testwebService')->willReturn($this->resultExample);

		$this->find('20250030', $this->getWebServiceByName('testwebService'))->isEmpty()->shouldBe(true);
	}

}