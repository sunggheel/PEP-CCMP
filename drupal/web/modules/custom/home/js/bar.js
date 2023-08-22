// /* globals Chart:false, feather:false */
// (async () => {
//     'use strict'

//     feather.replace({
//         'aria-hidden': 'true'
//     })

//     window.onload = () => {
//         console.log($(".goal-link"))
//     }

//     $(document).ready(function() {
//         console.log('jQuery is ready');
//         $('#my-element').tooltip({html: true});
//         $('#my-element2').tooltip({
//             html: true,
//             content: function() {
//               return $('<canvas id="homeChart" width="200" height="100"></canvas>');
//             }
//           });
//         console.log('Tooltip is initialized');
//     });

//     // A] Read Google Sheet to read goals, objectives, and actions
//     const endpoint = "https://sheets.googleapis.com/v4/spreadsheets/15wjNsshu8CIjNJFh1iWsj4qjPmXcFonEa-sI1p3NRwc/values/A:I?key=AIzaSyChgqkxrIRSedYuWb_UAIHfqfKDpC-daxk";

//     // Array of rows
//     let res = await fetch(endpoint);
//     let sheetData = (await res.json()).values;

//     let project = {
//         "id": 1,
//         "title": "PEP CCMP Tracking",
//         "goals": []
//     };

//     let goalId = 1;
//     let objectiveId = "A";
//     let actionId = 1;
//     let measureId = 1;
//     let subprojectId = 1;

//     let i = 1;
//     let sheetSize = sheetData.length;
//     while (i < sheetSize) {
//         // Parsed a goal
//         if (sheetData[i][0].length > 0) {
//             let goal = {
//                 "id" : goalId++,
//                 "title" : sheetData[i][0],
//                 "objectives" : []
//             };

//             let numGoalsToParse = 2;

//             // Parsing the objectives (while the next goal hasnt been parsed)
//             while (numGoalsToParse > 0) {
//                 if (i >= sheetSize) break;

//                 if (sheetData[i][0].length != 0) {
//                     if (--numGoalsToParse <= 0) break;
//                 }

//                 // Parsed an objective
//                 if (sheetData[i][1].length != 0) {
//                     let objective = {
//                         "id" : objectiveId,
//                         "title" : "",
//                         "description" : sheetData[i][1],
//                         "actions" : []
//                     };
//                     // next objectiveId
//                     objectiveId = String.fromCharCode(objectiveId.charCodeAt(0) + 1);

//                     let numObjectivesToParse = 2;

//                     // Parsing the actions (while the next objective hasnt been parsed)
//                     while (numObjectivesToParse > 0) {
//                         if (i >= sheetSize) break;

//                         if (sheetData[i][1].length != 0) {
//                             if (--numObjectivesToParse <= 0) break;
//                         }

//                         // Parsed an action
//                         if (sheetData[i][2].length != 0) {
//                             let action = {
//                                 "id" : actionId++,
//                                 "title" : sheetData[i][2],
//                                 "measures" : [],
//                                 "status" : "unexecuted",
//                             };

//                             let numActionsToParse = 2;

//                             // Parsing the performance measures (while the next action hasnt been parsed)
//                             while (numActionsToParse > 0) {
//                                 if (i >= sheetSize) break;

//                                 if (sheetData[i][2].length != 0) {
//                                     if (--numActionsToParse <= 0) break;
//                                 }

//                                 let measureStatus = sheetData[i][5] === undefined ? "" : sheetData[i][5].replace("-", "").toLowerCase();

//                                 let measure = {
//                                     "id" : measureId++,
//                                     "title" : sheetData[i][3],
//                                     "status" : measureStatus,
//                                     "organization" : sheetData[i][6],
//                                     "subprojects" : []
//                                 };

//                                 if (measure["organization"] == null){
//                                     measure["organization"] = "N/A";    
//                                 }

//                                 i++;
//                                 if (i < sheetSize && sheetData[i][4].length > 0) {
//                                     let numMeasuresToParse = 1;
//                                     while (numMeasuresToParse > 0) {
//                                         if (i >= sheetSize) break;
    
//                                         if (sheetData[i][3].length != 0) {
//                                             if (--numMeasuresToParse <= 0) break;
//                                         }
    
//                                         let subproject = {
//                                             "id" : subprojectId++,
//                                             "title" : sheetData[i][4],
//                                             "status" : sheetData[i][5].replace("-", "").toLowerCase(),
//                                             "organization" : sheetData[i][6] === null ? "" : sheetData[i][6]
//                                         };
                                        

                                        
//                                         measure["subprojects"].push(subproject);
//                                         i++;
//                                     }
//                                 }
                                
//                                 action["measures"].push(measure);
//                             }

//                             let count_completed = 0;
//                             let count_total = action["measures"].length;
//                             for (let m of action["measures"]) {
//                                 // if there is a blank... it doesn't count
//                                 // if there is ONE measure in progrsss  set status -> inprogress
//                                 // if all measures are completed        set status -> complete
//                                 if (m["status"] == ""){
//                                     count_total--;
//                                 } else if (m["status"] == "inprogress"){
//                                     action["status"] = "inprogress"; break;
//                                 } else if (m["status"] == "complete"){
//                                     count_completed++;
//                                 }
//                             }
//                             if (0 < count_completed && count_completed < count_total){
//                                 action["status"] = "inprogress";
//                             } else if (count_completed == count_total){
//                                 action["status"] = "complete";
//                             }
                            
//                             objective["actions"].push(action);
//                         }
//                     }
                    
//                     goal["objectives"].push(objective);
//                 }
//             }
            
//             project.goals.push(goal);
//         }
//     }

//     console.log(project)

//     let pieColors = ["#198754", "#ffc107", "#dc3545"];

//     // collect data for that goal
//     for (let goal of project["goals"]) {
//         let numObjectives = 0;
//         let numActions = 0;
//         let numMeasures = 0;
//         for (let objective of goal["objectives"]) {
//             numObjectives++;
//             for (let action of objective["actions"]) {
//                 numActions++;
//                 for (let measure of action["measures"]) {
//                     numMeasures++;
//                 }
//             }
//         }

//         // document.getElementById("goal-numObjectives").innerHTML = numObjectives;
//         // document.getElementById("goal-numActions").innerHTML = numActions;
//         // document.getElementById("goal-numMeasures").innerHTML = numMeasures;

//         let goalName = goal["title"];
//         let chartID = "homeChart"// + goalName.replace("-", "").replace(" ", "").toLowerCase()
//         if (document.getElementById(chartID) === null) continue;

//         let actionPieData = [0, 0, 0];

//         // collect data for that goal
//         for (let objective of goal["objectives"]) {
//             for (let action of objective["actions"]) {
//                 if (action["status"] === "complete") {
//                     actionPieData[0]++;
//                 } else if (action["status"] === "inprogress") {
//                     actionPieData[1]++;
//                 } else if (action["status"] === "unexecuted") {
//                     actionPieData[2]++;
//                 }
//             }
//         }
        
//         const ctx = document.getElementById(chartID).getContext("2d");
//         console.log(ctx)
//         const goalChart = new Chart(ctx, {
//             type: 'pie',
//             data: {
//                 labels: ["completed", "in-progress", "unexecuted"],
//                 datasets: [{
//                     data: actionPieData,
//                     lineTension: 0,
//                     backgroundColor: pieColors
//                 }],
//                 hoverOffset: 4
//             },
//             options: {
//                 legend: {
//                    display: false
//                 }
//             }
//         });

//         break;
//     }
// })()