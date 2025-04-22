<?php

namespace Drupal\project\Controller;

use Symfony\Component\Dotenv\Dotenv;

class ProjectController {

    public function getData($getGoal, $getObj) {
        // load .env
        (new Dotenv())->usePutenv()->bootEnv('/var/www/html/.env', 'dev', ['test'], true);        
        $api_url = $_ENV["GOOGLE_SHEETS_API_URL"];
        $api_key = $_ENV["GOOGLE_SHEETS_API_KEY"];
        
        $json_data = file_get_contents($api_url . "?key=" . $api_key);
        $data = json_decode($json_data);
        $sheet_data = $data->values;
    
        $project = [
            "id" => 1,
            "title" => "PEP CCMP Tracking",
            "goals" => []
        ];
        
        $goal_id = 1;
        $objective_id = "A";
        $action_id = 1;
        $measure_id = 1;
        $subproject_id = 1;
    
        $i = 1;
        $sheet_size = sizeof($sheet_data, 0);
        while ($i < $sheet_size) {
            // Parsed a goal
            if (strlen($sheet_data[$i][0]) != 0) {
                $goal = [
                    "id" => $goal_id++,
                    "title" => $sheet_data[$i][0],
                    "objectives" => []
                ];
                
                // Parsing the objectives (while the next goal hasnt been parsed)
                $numGoalsToParse = 2;
                while ($numGoalsToParse > 0) {
                    if ($i >= $sheet_size) break;
                    if (strlen($sheet_data[$i][0]) != 0) {
                        if (--$numGoalsToParse <= 0) break;
                    }

                    // Parsed an objective
                    if (strlen($sheet_data[$i][1] != 0)) {
                        // p($sheet_data[$i][1], 1);
                        $objective = [
                            "id" => $objective_id++,
                            "title" => "",
                            "description" => $sheet_data[$i][1],
                            "actions" => []
                        ];
    
                        // Parsing the actions (while the next objective hasnt been parsed)
                        $numObjectivesToParse = 2;
                        while ($numObjectivesToParse > 0) {
                            if ($i >= $sheet_size) break;
                            if (strlen($sheet_data[$i][1]) != 0) {
                                if (--$numObjectivesToParse <= 0) break;
                            }
    
                            // Parsed an action
                            if (strlen($sheet_data[$i][2] != 0)) {
                                // p($sheet_data[$i][2], 2);
                                $action = [
                                    "id" => $action_id++,
                                    "title" => $sheet_data[$i][2],
                                    "status" => "unexecuted",
                                    "measures" => [],

                                    "links" => [],
                                    // "num_links" => 0,
                                    // "num_maps" => 0,
                                    // "num_graphs" => 0
                                ];
    
                                // Parsing the performance measures (while the next action hasnt been parsed)
                                $numActionsToParse = 2;
                                while ($numActionsToParse > 0) {
                                    if ($i >= $sheet_size) break;
                                    if (strlen($sheet_data[$i][2]) != 0) {
                                        if (--$numActionsToParse <= 0) break;
                                    }

                                    if (strcmp($getGoal, strtolower(str_replace(" ", "", $goal["title"]))) === 0) {
                                        $measure = [
                                            "id" => $measure_id++,
                                            "title" => $sheet_data[$i][3],
                                            "status" => str_replace("-", "", strtolower($sheet_data[$i][5])),
                                            "organization" => "",
                                            "subprojects" => [],
                                
                                            "format" => $this->setFormat($sheet_data[$i][8]),
                                            "links" => [], // this was changed
                                            "links_map" => [],
                                            "links_graph" => [],
                                            // "num_links" => 0,
                                            // "num_maps" => 0,
                                            // "num_graphs" => 0,
                                        ];
                                        $measure = $this->getMeasureLinks($measure, $sheet_data, $i);
                                        $i++;

                                        if ($i < sizeof($sheet_data) && strlen($sheet_data[$i][4]) > 0) {
                                            
                                            // Parsing the sub projects
                                            $numMeasuresToParse = 1;
                                            while ($numMeasuresToParse > 0) {
                                                if ($i >= $sheet_size) break;
                                                if (strlen($sheet_data[$i][3]) != 0) {
                                                    if (--$numMeasuresToParse <= 0) break;
                                                }

                                                if (strcmp($getObj, $objective["id"]) === 0) {
                                                    $subproject = [
                                                        "id" => $subproject_id++,
                                                        "title" => $sheet_data[$i][4],
                                                        "status" => str_replace("-", "", strtolower($sheet_data[$i][5])),
                                                        "organization" => $sheet_data[$i][6] === null ? "" : $sheet_data[$i][6],
                                                        
                                                        "format" => $this->setFormat($sheet_data[$i][8]),    
                                                        "links" => [],
                                                        "links_map" => [],
                                                        "links_graph" => [],
                                                        // "num_links" => 0,
                                                        // "num_maps" => 0,
                                                        // "num_graphs" => 0,
                                                    ];
                                                    $subproject = $this->getSubprojectLinks($subproject, $sheet_data, $i);

                                                    $measure["num_links"] += $subproject["num_links"];
                                                    $measure["num_maps"] += $subproject["num_maps"];
                                                    $measure["num_graphs"] += $subproject["num_graphs"];
                                                    array_push($measure["subprojects"], $subproject);
                                                }
                                                $i++;
                                            }
                                        }
                                        $action["num_links"] += $measure["num_links"];
                                        $action["num_maps"] += $measure["num_maps"];
                                        $action["num_graphs"] += $measure["num_graphs"];
                                        array_push($action["measures"], $measure);
                                    } else {
                                        $i++;
                                    }
                                }                  
    
                                // Parses performance measures to set status of action [not measure]
                                $action = $this->setActionStatus($action);
                                array_push($objective["actions"], $action);
                            }
                        }
                        array_push($goal["objectives"], $objective);
                    }
                }
                array_push($project["goals"], $goal);
            }
        }
        return $project;
    }

    public function setActionStatus($action) {
        $count_completed = 0;
        $count_ongoing = 0;
        $count_inprogress = 0;
        $count_total = sizeof($action["measures"]);
        foreach ($action["measures"] as $m) {
            if ($m["status"] == "") {
                $count_total--;
            } else if ($m["status"] == "ongoing") {
                $count_ongoing++;
                //$action["status"] = "ongoing";
                //break;
            } else if ($m["status"] == "inprogress") {
                $count_inprogress++;
                //$action["status"] = "inprogress";
                //break;
            } else if ($m["status"] == "complete") {
                $count_completed++;
            }
        }
        if (0 < $count_ongoing && $count_inprogress == 0 && $count_completed < $count_total) {  // if there is ONE measure in progrsss  set status -> inprogress
            $action["status"] = "ongoing";
        } else if (0 < $count_inprogress && $count_completed < $count_total) {  // if there is ONE measure in progrsss  set status -> inprogress
            $action["status"] = "inprogress";
        } else if ($count_completed == $count_total) {                  // if all measures are completed        set status -> complete
            $action["status"] = "complete";
        } return $action;                                               // by default, status -> unexecuted (by creation of action)
    }

    // Also called project
    public function getMeasureLinks($measure, $sheet_data, $i) {

        // parse links
        foreach (explode("\n", $sheet_data[$i][7]) as $url) {
            if (strlen($url) == 0) continue;
            $linkObj = [
                "title" => $this->getTitle($url, $sheet_data[$i][8]),
                "url" => $url
            ];
            array_push($measure["links"], $linkObj);
        }

        // parse links_map
        foreach (explode("\n", $sheet_data[$i][9]) as $url) {
            if (strlen($url) == 0) continue;
            $page = file_get_contents($url);
            $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $page, $match) ? $match[1] : "";
            $linkObj = [
                "url" => $url,
                "title" => $title,
            ];
            array_push($measure["links_map"], $linkObj);
        }
        
        // parse links_graph
        foreach (explode("\n", $sheet_data[$i][10]) as $url) {
            if (strlen($url) == 0) continue;
            $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $page, $match) ? $match[1] : "";
            $linkObj = [
                "url" => $url,
                "title" => $title,
            ];
            array_push($measure["links_graph"], $linkObj);
        }

        $measure["num_links"] = sizeof($measure["links"]);
        $measure["num_maps"] = sizeof($measure["links_map"]);
        $measure["num_graphs"] = sizeof($measure["links_graph"]);
        $measure["organization"] = $sheet_data[$i][6] == null ? "N/A" : $sheet_data[$i][6];
        return $measure;
    }

    public function getSubprojectLinks($subproject, $sheet_data, $i) {

        // parse links
        foreach (explode("\n", $sheet_data[$i][7]) as $url) {
            if (strlen($url) == 0) continue;
            $linkObj = [
                "title" => $this->getTitle($url, $sheet_data[$i][8]),
                "url" => $url
            ];
            array_push($subproject["links"], $linkObj);
        }

        // parse links_map
        foreach (explode("\n", $sheet_data[$i][9]) as $url) {
            if (strlen($url) == 0) continue;
            $page = file_get_contents($url);
            $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $page, $match) ? $match[1] : "";
            $linkObj = [
                "url" => $url,
                "title" => $title,
            ];
            array_push($subproject["links_map"], $linkObj);
        }
        
        // parse links_graph
        foreach (explode("\n", $sheet_data[$i][10]) as $url) {
            if (strlen($url) == 0) continue;
            $linkObj = [
                "url" => $url
            ];
            array_push($subproject["links_graph"], $linkObj);
        }
        
        $subproject["num_links"] = sizeof($subproject["links"]);
        $subproject["num_maps"] = sizeof($subproject["links_map"]);
        $subproject["num_graphs"] = sizeof($subproject["links_graph"]);
        return $subproject;
    }

    public function setFormat($str) {
        $map = [
            "Web page" => "bi bi-filetype-html",
            "YouTube, video" => "bi bi-play-btn",
            "Link to PEP data hub" => "bi bi-folder-symlink",
            ".pdf" => "bi bi-filetype-pdf",
            ".zip" => "bi bi-file-earmark-zip",
            ".xls, .xlsx" => "bi bi-file-earmark-spreadsheet",
            ".doc, .docx" => "bi bi-file-earmark-word",
            ".ppt, .pptx" => "bi bi-file-earmark-slides",
        ];
        return $map[$str];
    }

    public function getTitle($url, $format) {
        $web_app = array("Web page", "YouTube, video", "Link to PEP data hub");

        if ($format == $web_app[0] || $format == $web_app[1] || $format == $web_app[2]) {
            $page = file_get_contents($url);
            $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $page, $match) ? $match[1] : "";
            return html_entity_decode($title, ENT_NOQUOTES, 'UTF-8');
        } else {
            $title = basename($url);
            return $title;
        }
    }

    // ==== function related to getData() are above - others are below =====

    // 3] function gets called
    public function showGoal($goal){ // parameter from URL gets used here
        $project = $this->getData($goal, "");
        $url = "/goal/";
        $goalNames = ["strongpartnerships", "resilientcommunities", "cleanwaters", "healthyecosystem"];
        $processedGoalName = strtolower(str_replace(" ", "", $goal));
        $goalsList = $this->getPictures($url, $goalNames);

        // strong -> "Strong partnersips" -> "strongpartnerships"
        if (!in_array($processedGoalName, $goalNames)) {
            return [
                '#theme' => 'not_found',
            ];
        }
        
        $totalActions = 0;
        $totalProjects = 0;
        foreach ($project["goals"] as $goalObj) {
            if (strcmp(strtolower(str_replace(" ", "", $goalObj["title"])), $goal) != 0) {
                continue;
            }
            foreach($goalObj["objectives"] as $objectiveObj) {
                foreach ($objectiveObj["actions"] as $actionObj) {
                    $totalActions++;
                    $totalProjects += count($actionObj["measures"]);
                }
            }
        }

        $returnedGoal = null;
        foreach ($project["goals"] as $goalObj) {
            if (strcmp(strtolower(str_replace(" ", "", $goalObj["title"])), $goal) == 0) {
                $returnedGoal = $goalObj;
                break;
            }
        }

        date_default_timezone_set('America/New_York');
        $lastUpdated = date('m/d/Y', time());

        return [
            '#theme' => 'goal',            
            '#goal' => $returnedGoal,
            '#goalList' => $goalsList,
            '#goalTheme' => $processedGoalName,
            '#numActions' => $totalActions,
            '#numProjects' => $totalProjects,
            '#lastUpdated' => $lastUpdated,
        ];
    }  
    
    public function showObjective($goal, $obj){
        $project = $this->getData($goal, $obj);
        $url = "/goal/";
        $goalNames = ["strongpartnerships", "resilientcommunities", "cleanwaters", "healthyecosystem"];
        $processedGoalName = strtolower(str_replace(" ", "", $goal));
        $goalsList = $this->getPictures($url, $goalNames);

        // strong -> "Strong partnersips" -> "strongpartnerships"
        if (!in_array($processedGoalName, $goalNames)) {
            return [
                '#theme' => 'not_found',
            ];
        }

        $totalActions = 0;
        $totalProjects = 0;
        foreach ($project["goals"] as $goalObj) {
            if (strcmp(strtolower(str_replace(" ", "", $goalObj["title"])), $goal) != 0) {
                continue;
            }
            foreach($goalObj["objectives"] as $objectiveObj) {
                if ($obj == $objectiveObj["id"]){
                    foreach ($objectiveObj["actions"] as $actionObj) {
                        $totalActions++;
                        $totalProjects += count($actionObj["measures"]);
                    }
                }
            }
        }

        $returnedGoal = null;
        foreach ($project["goals"] as $goalObj) {
            if (strcmp(strtolower(str_replace(" ", "", $goalObj["title"])), $goal) == 0) {
                $returnedGoal = $goalObj;
                break;
            }
        }

        $returnedObjective = null;
        foreach ($returnedGoal["objectives"] as $objectiveObj) {
            if (strcmp($objectiveObj["id"], $obj) == 0) {
                $returnedObjective = $objectiveObj;
                break;
            }
        }
        
        // var_dump($returnedObjective);

        if ($returnedObjective == null) {
            return [
                '#theme' => 'not_found',
            ];
        }

        date_default_timezone_set('America/New_York');
        $lastUpdated = date('m/d/Y', time());

        return [
            '#theme' => 'objective',
            '#goal' => $returnedGoal,
            '#goalList' => $goalsList,
            '#objective' => $returnedObjective,
            '#goalTheme' => $processedGoalName,
            '#numActions' => $totalActions,
            '#numProjects' => $totalProjects,
            '#lastUpdated' => $lastUpdated,
        ];
    }

    public function getPictures($url, $goalNames) {    
        $imgDir = "/modules/custom/project/img";
        $goalimg = '/goal';
        return [
            "title" => "Goals",
            "goals" => [
                ['src' => $imgDir . $goalimg . '/1strongpartnershipsLogo.png',
                'alt' => "Strong Partnerships",
                'code' => $goalNames[0],
                'href' => $url . $goalNames[0]],
                ['src' => $imgDir . $goalimg . '/2resilientcommunitiesLogo.png',
                'alt' => "Resilient Communities",
                'code' => $goalNames[1],
                'href' => $url . $goalNames[1]],
                ['src' => $imgDir . $goalimg . '/3cleanwatersLogo.png',
                'alt' => "Clean Waters",
                'code' => $goalNames[2],
                'href' => $url . $goalNames[2]],
                ['src' => $imgDir . $goalimg . '/4healthyecosystemLogo.png',
                'alt' => "Healthy Ecosystem",
                'code' => $goalNames[3],
                'href' => $url . $goalNames[3]],
              ],
        ];
    }
}