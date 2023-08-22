let goals = ["strongpartnerships", "resilientcommunities", "cleanwaters", "healthyecosystem"];

function showInfo(id) {
    // console.log("show img-" + goals[id]);
    document.getElementById("img-" + goals[id]).classList.add("hide");
    document.getElementById("info-" + goals[id]).classList.remove("hide");
}

function hideInfo(id) {
    // console.log("hide img-" + goals[id]);
    document.getElementById("info-" + goals[id]).classList.add("hide");
    document.getElementById("img-" + goals[id]).classList.remove("hide");
}

// let box = 0;
// let hover = false;
// let updateInterval = 5000;
// let resetTimeout = 5000;

// // interval to rotate text
// let update = setInterval(updatePointer, updateInterval);
// function updatePointer() {
// 	// console.log(box);
//     document.getElementById("board" + box).classList.add("hide");
//     box = ++box % 4;
//     document.getElementById("board" + box).classList.remove("hide");
// }

// // onmouseout handler
// function rotateText() {
// 	// console.log("Hover out");
//     hover = false;
    
//     setTimeout(() => { 
//       if (!hover && update === null) {
//       	// console.log("Reset interval!");
//       	update = setInterval(updatePointer, updateInterval);
//       }
//     }, resetTimeout)
    
// }

// // onmouseover handler
// function setText(x) {
// 	// console.log("Hovering!");
//     hover = true;
    
//     if (update){
//     	// console.log("Cleared interval!");
// 		clearInterval(update);
//         update = null;
//     }
    
//     document.getElementById("board" + box).classList.add("hide");
//     box = x;
//     document.getElementById("board" + box).classList.remove("hide");
// }