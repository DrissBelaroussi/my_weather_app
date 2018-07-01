<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response ;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\RequestException ;
use GuzzleHttp\Exception\TransferException ;
use Symfony\Component\Config\FileLocator ; 
use GuzzleHttp\Client ;


class WeatherController extends Controller
{

	public function trie($element, $liste2)
	{
		foreach($liste2 as $element2)
		{
			if ($element["country"] == $element2["country"] && round($element["lon"]) == round($element2["lon"]) && round($element["lat"]) == round($element2["lat"]))
			{
				return 0;
			}
		}
		return 1;
	}

    /**
     * @Route("/weather", name="weather")
     */
    public function weather(Request $request)
    {
    	$form = $this->createFormBuilder()
    	->add('city' , textType::class , array(
    		'attr' => [ 
    			'placeholder' => 'Select a city...',
    			'class' => " form-control"]	
    		))
    	->add('Submit' , SubmitType::class, [
    		'attr' => [
    			'class' => "btn btn-default width-button  LightBlue margin"]])
    	->getForm();

    	$form->handleRequest($request);

    	if ($form->isSubmitted() && $form->isValid()) {
    		$data = $form->getData();
    		$ville = $data['city'] ;
    		$client = new Client();
    		$locator = array(__DIR__.'\..\..\public\js');
    		$file_locator = new FileLocator($locator);
    		$citiesLocator = $file_locator->locate('city.list.json', null, false);
    		$cities = json_decode(file_get_contents($citiesLocator[0]) );
    		$result = array();
    		$i = 0 ;
    		$tabcities = array();
    		$tabfinal = array();

    		foreach($cities as $city){
				#var_dump($city->country );
    			if(strtolower($city->name) == strtolower($ville)){
    				$tabcities[$i]["id"] = ($city->id) ;
    				$tabcities[$i]["name"] = ($city->name) ;
    				$tabcities[$i]["country"] = $city->country ;							
    				$tabcities[$i]["lon"] = $city->coord->lon ;
    				$tabcities[$i]["lat"] = $city->coord->lat ;
    				$i++;				
    			} 
    		}

    		if(count($tabcities) > 0){
    			foreach($tabcities as $city){
    				if($this->trie($city, $tabfinal)){
    					try {
    						$resultWeather = $client->request('GET', 'http://api.openweathermap.org/data/2.5/weather?id='.  $city["id"] . '&appid=e3572a8d161a5f8a2d6f25ce711baee0&units=metric', [
    							'headers' => [
    								'Accept' => 'application/json',
    								'Content-type' => 'application/json'
    							]]);
    						$result[$i] = json_decode($resultWeather->getBody(), true );
    						$city["temp"] = round($result[$i]["main"]["temp"]) ;
    						$city["description"] = $result[$i]["weather"][0]["description"];
    						$city["img"] = $result[$i]["weather"][0]["icon"];
    						array_push($tabfinal, $city);
    						$i++ ;
    					} catch ( RequestException $e ){
    						return $this->render('weather/weather.html.twig', [
    							'controller_name' => 'WeatherController',
    							'formMeteo' => $form->createView(), 
    							'exception' => $e->getMessage() 
    						]);
    					}	
    				}
    			}

    		} else {
    			return $this->render('weather/weather.html.twig', [
    				'controller_name' => 'WeatherController',
    				'formMeteo' => $form->createView(), 
    				'exception' => "City not found." 
    			]);
    		}
    		return $this->render('weather/weather.html.twig', [
    			'controller_name' => 'WeatherController',
    			'formMeteo' => $form->createView(),
    			'result' => $tabfinal,
    		]);
    	}
    	return $this->render('weather/weather.html.twig', [
    		'controller_name' => 'WeatherController',
    		'formMeteo' => $form->createView()
    	]);
    }
}
