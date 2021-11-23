let currdate = new Date();
let correctdate = currdate.getDate();
document.getElementById("date").innerHTML = currdate.toLocaleString();

let currmonth = currdate.getMonth();
let loopmonth = currdate.getMonth();
let n = 1;
let row = 1;
while (loopmonth == currmonth) {
    currdate.setDate(n);
    loopmonth = currdate.getMonth();
    for (i = 0;i < 7;i++) {
        if ( currdate.getDay() == i) {
            let dayElement = document.createElement("P");
            dayElement.style.gridColumn = String(i+1) + " / " + String(i+2);
            dayElement.style.gridRow = row + " / " + String(row+1);
            if (n == correctdate) dayElement.style.border = "2px solid red";
            document.getElementById("calendar").appendChild(dayElement);
            dayElement.innerHTML = n;
            if (i == 6) row++;
        }
    }
    n++;
}

/*
function drawcalgrid() {

}

function prevmonth() {

}

function nextmonth() {

}*/
