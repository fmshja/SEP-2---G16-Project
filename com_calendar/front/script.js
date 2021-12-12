let loopdate = new Date();
let correctdate = loopdate.getDate();
let correctmonth = loopdate.getMonth();
let correctyear = loopdate.getFullYear();
let monthdifference = 0;


function prevmonth() {
    document.getElementById("calendar").innerHTML = "";
    monthdifference--;
    loopdate.setFullYear(correctyear);
    loopdate.setMonth(correctmonth + monthdifference);
    drawcalgrid();
}


function nextmonth() {
    document.getElementById("calendar").innerHTML = "";
    monthdifference++;
    loopdate.setFullYear(correctyear);
    loopdate.setMonth(correctmonth + monthdifference);
    drawcalgrid();
}


function drawcalgrid() {
    let loopmonth = loopdate.getMonth();
    let loopmonth2 = loopdate.getMonth();
    let loopyear = loopdate.getFullYear();
    let n = 1;
    let row = 2;
    loopdate.setDate(n);
    createweekdayrow();
    while (loopmonth == loopmonth2) {
        for (let i = 0;i < 7;i++) {
            if ( loopdate.getDay() == i) {
                let dayElement = document.createElement("P");
                dayElement.style.gridColumn = String(i+1) + " / " + String(i+2);
                dayElement.style.gridRow = row + " / " + String(row+1);
                if (n == correctdate && monthdifference == 0) dayElement.style.outline = "4px solid red";
                document.getElementById("calendar").appendChild(dayElement);
                dayElement.id = String(n);
                dayElement.className = String(loopmonth2);
                dayElement.onclick = function(day, month, year)
                    {calendarclickevent(dayElement.getAttribute("id"), dayElement.getAttribute("class"), loopyear)};
                layoutfunction(dayElement, n, loopmonth2, db, loopyear);
                if (i == 6) row++;
            }
        }
        n++;
        loopdate.setDate(n);
        loopmonth = loopdate.getMonth();
    }
    monthout(loopmonth2);
}


function monthout(loopmonth2) {
    let yearshown = new Date();
    yearshown.setMonth(correctmonth + monthdifference);
    const month = new Array();
    month[0] = "January";
    month[1] = "February";
    month[2] = "March";
    month[3] = "April";
    month[4] = "May";
    month[5] = "June";
    month[6] = "July";
    month[7] = "August";
    month[8] = "September";
    month[9] = "October";
    month[10] = "November";
    month[11] = "December";
    document.getElementById("month-shown").innerHTML = month[loopmonth2] + ", " + yearshown.getFullYear();
}


function createweekdayrow() {
    let d = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    for (let x in d) {
        let weekDays = document.createElement("H2");
        weekDays.style.gridRow = "1 / 2";
        document.getElementById("calendar").appendChild(weekDays);
        weekDays.innerHTML = d[x];
    }
}

function fixDateformat (n, loopmonth2, loopyear) {
    let x = String(n);
    if (x.length == 1) {
        x = "0" + x;
    }
    if (loopmonth2.length == 1) {
        loopmonth2 = "0" + loopmonth2;
    }
    loopmonth2 = Number(loopmonth2) + 1;
    return loopyear + "-" + String(loopmonth2) + "-" + x;
}


function layoutfunction(dayElement, n, loopmonth2, db, loopyear) {
    let test = fixDateformat(n, loopmonth2, loopyear);
    dayElement.innerHTML = n;
    for (let a of db) {
        if (a[3] == test) {
            let additionalElement = document.createElement("P");
            additionalElement.id = "inner";
            dayElement.appendChild(additionalElement);
            additionalElement.innerHTML = a[1];
        }
    }
}


