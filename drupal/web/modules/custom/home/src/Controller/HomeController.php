<?php
/*
2] Controller decides:
- what module to use
- what templates to use
- what variables to send
*/

namespace Drupal\home\Controller;

use Symfony\Component\Dotenv\Dotenv;

class HomeController {

	public function showHome() {

		// directory of images for home folder
		$imgDir = "modules/custom/home/img";
		$carouselimg = '/carousel/';
		$goalimg = "/goal/";

		// read json file for carousel data
		$jsonDir = 'modules/custom/home/json';
		$jsonFile = $jsonDir . '/data.json';
		$jsonData = file_get_contents($jsonFile);
		$data = json_decode($jsonData);
		$carouselMetadata = $data->carousel;

		// append data into $carousel
		$carousel = [];    
		foreach ($carouselMetadata as $carouselRow) {
			array_push($carousel, [
			'src' => $imgDir . $carouselimg . $carouselRow->name,
			'alt' => $carouselRow->alt,
			'title' => $carouselRow->title,
			'subtitle' => $carouselRow->subtitle,
			'href' => $carouselRow->href
			]);
		}

		// generate list of goals
		$goalurl = "/goal/";
		$goalNames = ["strongpartnerships", "resilientcommunities", "cleanwaters", "healthyecosystem"];
		$goalsList = [
			"title" => "CCMP 2020 Focuses on Four Goals",
			"goals" => [
			['src' => $imgDir . $goalimg . '1strongpartnerships.png',
			'alt' => "Strong Partnerships",
			'code' => $goalNames[0],
			'href' => $goalurl . $goalNames[0]],
			['src' => $imgDir . $goalimg . '2resilientcommunities.png',
			'alt' => "Resilient Communities",
			'code' => $goalNames[1],
			'href' => $goalurl . $goalNames[1]],
			['src' => $imgDir . $goalimg . '3cleanwaters.png',
			'alt' => "Clean Waters",
			'code' => $goalNames[2],
			'href' => $goalurl . $goalNames[2]],
			['src' => $imgDir . $goalimg . '4healthyecosystem.png',
			'alt' => "Healthy Ecosystem",
			'code' => $goalNames[3],
			'href' => $goalurl . $goalNames[3]],
			],
		];

		(new Dotenv())->usePutenv()->bootEnv('/var/www/html/.env', 'dev', ['test'], true);        
		$api_url = $_ENV["GOOGLE_SHEETS_API_URL"];
		$api_key = $_ENV["GOOGLE_SHEETS_API_KEY"];
		
		$json_data = file_get_contents($api_url . "?key=" . $api_key);
		$data = json_decode($json_data);
		$sheet_data = $data->values;

		$stats = [];

		for ($i = 1; $i < sizeof($sheet_data); $i++) {
			if (strlen($sheet_data[$i][0]) !== 0) { 
				array_push($stats, [
					"objectives" => 0,
					"actions" => 0,
					"projects" => 0,
					"subprojects" => 0,
					"projectCompletion" => 0
				]);
			}
			
			
			$statsMap = ["", "objectives", "actions", "projects", "subprojects"];
			for ($j = 1; $j <= 4; $j++) {
				if (strlen($sheet_data[$i][$j]) > 0) {
					$stats[sizeof($stats)-1][$statsMap[$j]]++;

					// check if project is completed
					if ($j === 3 &&  strtolower($sheet_data[$i][5]) === "complete") {
						$stats[sizeof($stats)-1]["projectCompletion"]++;
					}
				}
			}
		}

		for ($i = 0; $i < sizeof($stats); $i++) {
			$stats[$i]["projectCompletion"] = round($stats[$i]["projectCompletion"]*100 / $stats[$i]["projects"]);
			$goalsList["goals"][$i]["stats"] = $stats[$i];
		}

		return [
			'#theme' => 'home_view',  # Receives initialized variables from home.module

			'#carousel' => $carousel, # Assignins variables and passes them to 'render'
			'#goalsList' => $goalsList, # twig file with the same theme name, 'home_view'
		];
	}
}
