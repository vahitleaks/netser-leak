var weekend = [0,6];
var weekendColor = "#e0e0e0";
var fontface = "Verdana";
var fontsize = 2;


var gNow = new Date();
var ggWinCal;
isNav = (navigator.appName.indexOf("Netscape") != -1) ? true : false;
isIE = (navigator.appName.indexOf("Microsoft") != -1) ? true : false;
isChrome = (navigator.appName.indexOf("Google") != -1) ? true : false;
isFirefox = (navigator.appName.indexOf("Mozilla") != -1) ? true : false;

Calendar.Months = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
Calendar.Months = ["Ocak", "Þubat", "Mart", "Nisan", "Mayýs", "Haziran","Temmuz", "Aðustos", "Eylül", "Ekim", "Kasým", "Aralýk"];
// Non-Leap year Month days..
Calendar.DOMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
// Leap year Month days..
Calendar.lDOMonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

function Calendar(p_item, p_WinCal, p_month, p_year, p_format) {
	if ((p_month == null) && (p_year == null))	return;

	if (p_WinCal == null)
		this.gWinCal = ggWinCal;
	else
		this.gWinCal = p_WinCal;
	
	if (p_month == null) {
		this.gMonthName = null;
		this.gMonth = null;
		this.gYearly = true;
	} else {
		this.gMonthName = Calendar.get_month(p_month);
		this.gMonth = new Number(p_month);
		this.gYearly = false;
	}
  this.gWtime = false;
	this.gYear = p_year;
	this.gFormat = p_format;
	this.gBGColor = "#F9F9F9";
	this.gFGColor = "black";
	this.gTextColor = "black";
	this.gHeaderColor = "black";
	this.gReturnItem = p_item;
}

Calendar.get_month = Calendar_get_month;
Calendar.get_daysofmonth = Calendar_get_daysofmonth;
Calendar.calc_month_year = Calendar_calc_month_year;
Calendar.print = Calendar_print;

function Calendar_get_month(monthNo) {
	return Calendar.Months[monthNo];
}

function Calendar_get_daysofmonth(monthNo, p_year) {
	/* 
	Check for leap year ..
	1.Years evenly divisible by four are normally leap years, except for... 
	2.Years also evenly divisible by 100 are not leap years, except for... 
	3.Years also evenly divisible by 400 are leap years. 
	*/
	if ((p_year % 4) == 0) {
		if ((p_year % 100) == 0 && (p_year % 400) != 0)
			return Calendar.DOMonth[monthNo];
	
		return Calendar.lDOMonth[monthNo];
	} else
		return Calendar.DOMonth[monthNo];
}

function Calendar_calc_month_year(p_Month, p_Year, incr) {
	/* 
	Will return an 1-D array with 1st element being the calculated month 
	and second being the calculated year 
	after applying the month increment/decrement as specified by 'incr' parameter.
	'incr' will normally have 1/-1 to navigate thru the months.
	*/
	var ret_arr = new Array();
	
	if (incr == -1) {
		// B A C K W A R D
		if (p_Month == 0) {
			ret_arr[0] = 11;
			ret_arr[1] = parseInt(p_Year) - 1;
		}
		else {
			ret_arr[0] = parseInt(p_Month) - 1;
			ret_arr[1] = parseInt(p_Year);
		}
	} else if (incr == 1) {
		// F O R W A R D
		if (p_Month == 11) {
			ret_arr[0] = 0;
			ret_arr[1] = parseInt(p_Year) + 1;
		}
		else {
			ret_arr[0] = parseInt(p_Month) + 1;
			ret_arr[1] = parseInt(p_Year);
		}
	}
	
	return ret_arr;
}

function Calendar_print() {
	ggWinCal.print();
}

function Calendar_calc_month_year(p_Month, p_Year, incr) {
	/* 
	Will return an 1-D array with 1st element being the calculated month 
	and second being the calculated year 
	after applying the month increment/decrement as specified by 'incr' parameter.
	'incr' will normally have 1/-1 to navigate thru the months.
	*/
	var ret_arr = new Array();
	
	if (incr == -1) {
		// B A C K W A R D
		if (p_Month == 0) {
			ret_arr[0] = 11;
			ret_arr[1] = parseInt(p_Year) - 1;
		}
		else {
			ret_arr[0] = parseInt(p_Month) - 1;
			ret_arr[1] = parseInt(p_Year);
		}
	} else if (incr == 1) {
		// F O R W A R D
		if (p_Month == 11) {
			ret_arr[0] = 0;
			ret_arr[1] = parseInt(p_Year) + 1;
		}
		else {
			ret_arr[0] = parseInt(p_Month) + 1;
			ret_arr[1] = parseInt(p_Year);
		}
	}
	
	return ret_arr;
}

// This is for compatibility with Navigator 3, we have to create and discard one object before the prototype object exists.
new Calendar();

Calendar.prototype.getMonthlyCalendarCode = function() {
	var vCode = "";
	var vHeader_Code = "";
	var vData_Code = "";
	
	// Begin Table Drawing code here..cellspacing="1"
	vCode = vCode + "<TABLE width=\"100%\" height=\"75%\" cellpadding=0 cellspacing=1 BORDER=0 BGCOLOR=\"" + this.gBGColor + "\" >";
	
	vHeader_Code = this.cal_header();
	vData_Code = this.cal_data();
	vCode = vCode + vHeader_Code + vData_Code;
	
	return vCode;
}

Calendar.prototype.show = function() {
	var vCode = "";
	
	this.gWinCal.document.open();

	// Setup the page...
	this.wwrite("<html>");
	this.wwrite("<head><title>Takvim</title>");
	this.wwrite("</head>");
    this.wwrite("<script>self.focus()</script>");
	this.wwrite("<CENTER><body bgcolor='#FFFFFF' topmargin=0 leftmargin=0 " + 
		"link=\"" + this.gLinkColor + "\" " + 
		"vlink=\"" + this.gLinkColor + "\" " +
		"alink=\"" + this.gLinkColor + "\" " +
		"text=\"" + this.gTextColor + "\">");
	this.wwrite("<STYLE>");
	this.wwrite(".turuncu1 {  font-family: verdana; font-size: 10px; font-weight: bold; color: #8A5600; background-image: url(/images/takvim02.gif); text-align: center; height: 22px; text-decoration: none}");
	this.wwrite(".input2 { font-family: verdana; font-size: 10px; color: #003399; height: 18px; width: 50px}");
	this.wwrite(".input3 { font-family: verdana; font-size: 10px; color: #003399; height: 18px; width: 60px}");
	this.wwrite(".turuncu2 { font-family: verdana; font-size: 10px; font-weight: bold; color: #8A5600; text-align: center; height: 12px ; background-color: #FFCE7D; text-decoration: none}");
	this.wwrite(".turuncu3 { font-family: verdana; font-size: 10px; font-weight: bold; color: #8A5600; text-align: center; height: 12px ;  text-decoration: none}");
	this.wwrite(".turuncu4 { font-family: verdana; font-size: 10px; font-weight: bold; color: #8A5600; text-align: center; height: 12px ; background-color: #FFEDCF; text-decoration: none}");
	this.wwrite(".turuncu5 { font-family: verdana; font-size: 10px; font-weight: bold; color: #FF0000; text-align: center; height: 12px ; text-decoration: none}");
	this.wwrite("</STYLE>");
			
	this.wwriteA("<table width=\"170\" border=0 cellspacing=0 cellpadding=0>\n");
	this.wwriteA("<tr>\n"); 
	this.wwriteA("<td width=6><img src=\"/images/takvim01.gif\" width=6 height=22></td>\n");
	this.wwriteA("<td class=\"turuncu1\">\n");	
	this.wwriteA(this.gMonthName + " " + this.gYear);
	this.wwriteA("</td>\n");
	this.wwriteA("<td width=5><img src=\"/images/takvim03.gif\" width=6 height=22></td>\n");
	this.wwriteA("</tr>\n");
	this.wwriteA("<tr>\n"); 
	this.wwriteA("<td background=\"/images/takvim04.gif\" rowspan=2><img src=\"/images/takvim04.gif\" width=6 height=2></td>\n");
	this.wwriteA("<td bgcolor=\"FFE3B3\">\n");

	// Show navigation buttons
	var prevMMYYYY = Calendar.calc_month_year(this.gMonth, this.gYear, -1);
	var prevMM = prevMMYYYY[0];
	var prevYYYY = prevMMYYYY[1];

	var nextMMYYYY = Calendar.calc_month_year(this.gMonth, this.gYear, 1);
	var nextMM = nextMMYYYY[0];
	var nextYYYY = nextMMYYYY[1];
	
	this.wwrite("<TABLE WIDTH='100%' BORDER=0 CELLSPACING=0 CELLPADDING=0><TR><TD ALIGN=center >");
	
	
	this.wwrite("<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnItem + "', '" + this.gMonth + "', '" + (parseInt(this.gYear)-1) + "', '" + this.gFormat + "'," + this.gWtime +
		");" +
		"\"><img src=/images/takvim09.gif border=0><\/A></TD><TD ALIGN=center>");

	this.wwrite("<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnItem + "', '" + prevMM + "', '" + prevYYYY + "', '" + this.gFormat + "'," + this.gWtime +
		");" +
		"\"><img src=/images/takvim10.gif border=0><\/A></TD><TD ALIGN=center>");

//SEYKAY
   var d, s = "Today's date is: ";           //Declare variables.
   d = new Date();                           //Create Date object.
   this.wwrite("<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnItem + "', '" + d.getMonth() + "', '" + (parseInt(d.getFullYear())) + "', '" + this.gFormat + "'," + this.gWtime +
		");" +
		"\"><img src=/images/takvim11.gif border=0><\/A></TD><TD ALIGN=center>");
//SEYKAY
	
	
	this.wwrite("<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnItem + "', '" + nextMM + "', '" + nextYYYY + "', '" + this.gFormat + "'," + this.gWtime +
		");" +
		"\"><img src=/images/takvim12.gif border=0><\/A></TD><TD ALIGN=center>");
	this.wwrite("<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnItem + "', '" + this.gMonth + "', '" + (parseInt(this.gYear)+1) + "', '" + this.gFormat + "'," + this.gWtime +
		");" +
		"\"><img src=/images/takvim13.gif border=0><\/A></TD><TD ALIGN=center>");
	
	// SEYKAY

   if (this.gWtime==true)
           this.wwrite("<SELECT class=input2 NAME=tMonth onchange=\"javascript:window.opener.Build('" + this.gReturnItem + "', this.value, document.all('tYear').value,'" + this.gFormat + "',true)\" style=\"font-size: 10px\">");
	else if(this.gWtime!=true)
       this.wwrite("<SELECT class=input2 NAME=tMonth onchange=\"javascript:window.opener.Build('" + this.gReturnItem + "', this.value, document.all('tYear').value,'" + this.gFormat + "',false)\" style=\"font-size: 10px\">");


	//alert(this.gMonth);
	for (k=0; k<12 ; k++){
	   if (k == this.gMonth) {
	      this.wwrite("<OPTION selected value=" + k + " >" + (k+1) + "</OPTION>");
	   }else
	      this.wwrite("<OPTION value=" + k + " >" + (k+1) + "</OPTION>");
	}
	  this.wwrite("</SELECT></TD><TD ALIGN=center>");
  //SEYKAY

	//SEYKAY	  

   if (this.gWtime==true)
	   this.wwrite("<SELECT class=input1 NAME=tYear onchange=\"javascript:window.opener.Build('" + this.gReturnItem + "', document.all('tMonth').value, this.value ,'" + this.gFormat + "',true)\" style=\"font-size: 10px\">");
	else if(this.gWtime!=true)
	   this.wwrite("<SELECT class=input1 NAME=tYear onchange=\"javascript:window.opener.Build('" + this.gReturnItem + "', document.all('tMonth').value, this.value ,'" + this.gFormat + "',false)\" style=\"font-size: 10px\">");
    var maxYearObj = new Date();
   // OKOC Maximum yýl içinde bulunduðumuz yýlýn 3 fazlasý olacak þekilde ayarlanmýþtýr.
	for (k=1980; k<=(maxYearObj.getFullYear())+3 ; k++){
	   if (k == this.gYear) {
	      this.wwrite("<OPTION selected value=" + k + " >" + k + "</OPTION>");
	   }else
	      this.wwrite("<OPTION value=" + k + " >" + k + "</OPTION>");
	}
	  this.wwrite("</SELECT>");
	  
	//SEYKAY

  this.wwrite("</TD><td xbackground=\"/images/takvim05.gif\" rowspan=3><imgz src=\"images/takvim05.gif\" width=6 height=2></td>");   
	this.wwrite("</tr></table>");
	
	this.wwriteA("</td><td background=\"/images/takvim04.gif\" rowspan=2><img src=\"/images/takvim04.gif\" width=6 height=2></td>\n");
	this.wwriteA("</tr>\n");

	// Get the complete calendar code for the month..
	 vCode = this.getMonthlyCalendarCode();
	
   this.wwriteA("<tr>\n"); 
	 this.wwriteA("<td bgcolor=\"FFE3B3\">\n");
	
	 this.wwrite(vCode);

   this.wwriteA("</td></tr>\n");	
    //javascript function added by SEYKAY
   this.wwriteA("<tr>\n"); 
	 this.wwriteA("<td background=\"/images/takvim04.gif\" ><img src=\"/images/takvim04.gif\" width=6 height=2></td>\n");
	 this.wwriteA("<td bgcolor=\"FFE3B3\">\n");
 
   if (this.gWtime==true)
       this.wwrite("<TABLE><TR><TD class=turuncu2 colspan=7>Saat:<Input type=text  id=\"mhour\" name=\"mhour\" size=\"1\" style=\"font-size:7pt; width=22\" maxlength=\"2\"><Input type=text  id=\"mminute\" name=\"mminute\" size=\"1\" style=\"font-size:7pt; width=22\" maxlength=\"2\">(15:30 gibi)</td></tr><tr><td class=turuncu2 colspan=7><font color=red>Saati girdikten sonra, güne týklayýnýz!</font></td></tr></TABLE>")	
	 else if(this.gWtime!=true)
       this.wwrite("<TABLE><TR style=\"display:none\"><TD colspan=7>Saat:<Input type=text  id=\"mhour\" name=\"mhour\" size=\"1\" style=\"font-size:7pt;\" maxlength=\"2\"><Input type=text  id=\"mminute\" name=\"mminute\" style=\"font-size:7pt; width=5\" maxlength=\"2\">(15:30 gibi)</td></tr><tr><td class=turuncu2 colspan=7><font color=red>Saati girdikten sonra, güne týklayýnýz!</font></td></tr></TABLE>");

	 this.wwriteA("</td><td background=\"/images/takvim04.gif\" ><img src=\"/images/takvim04.gif\" width=6 height=2></td>\n");
	 this.wwriteA("</tr>\n");


	 this.wwrite("<tr>"); 
	 this.wwrite("    <td background=\"/images/takvim04.gif\" ><img src=\"/images/takvim06.gif\" width=6 height=5></td>");
	 this.wwrite("    <td bgcolor=\"FFE3B3\" background=\"/images/takvim08.gif\"><img src=\"/images/takvim08.gif\" width=2 height=5></td>");
	 this.wwrite("    <td background=\"/images/takvim05.gif\"><img src=\"/images/takvim07.gif\" width=6 height=5></td>");
	 this.wwrite("  </tr>");
	 this.wwrite("</table>");

	
	
    this.wwrite("<script language=\"javascript\">\n"+
                "function SendDateTime(dateval){\n" +
                "   var cHour   = document.all(\"mhour\").value;\n"+
                "   var cMinute = document.all(\"mminute\").value;\n"+
                "   var statu=false; \n"+
                "  if ((cHour >= 0 && cHour < 25) && (cMinute >= 0 && cMinute < 61)) \n" +
                "     statu=true; \n" +              
                "  if (cHour!=\"\" && cMinute!=\"\"){ \n" + 
                "      if(statu==true){ \n "+
                "        self.opener.document.all('" + this.gReturnItem + "').value = dateval + \" \" + cHour + \":\" + cMinute +\":00\"; \n" +
                "      }else{ \n" +
                "        alert('Girdiðiniz Zaman Formatý Yanlýþ');return}\n"+    
                "  }else{ " +
                "   self.opener.document.all('" + this.gReturnItem + "').value=dateval;} \n" +
                " }\n" +
                "</script>");

	this.wwrite("</font></body></html>");
	this.gWinCal.document.close();
}

Calendar.prototype.showY = function() {
	var vCode = "";
	var i;
	var vr, vc, vx, vy;		// Row, Column, X-coord, Y-coord
	var vxf = 285;			// X-Factor
	var vyf = 200;			// Y-Factor
	var vxm = 10;			// X-margin
	var vym;				// Y-margin
	if (isIE)	vym = 75;
	else if (isNav)	vym = 25;
	
	this.gWinCal.document.open();

	this.wwrite("<html>");
	this.wwrite("<head><title>Calendar</title>");
	this.wwrite("<style type='text/css'><!--");
	for (i=0; i<12; i++) {
		vc = i % 3;
		if (i>=0 && i<= 2)	vr = 0;
		if (i>=3 && i<= 5)	vr = 1;
		if (i>=6 && i<= 8)	vr = 2;
		if (i>=9 && i<= 11)	vr = 3;
		
		vx = parseInt(vxf * vc) + vxm;
		vy = parseInt(vyf * vr) + vym;

		this.wwrite(".lclass" + i + " {position:absolute;top:" + vy + ";left:" + vx + ";}");
	}
	this.wwrite("-->\n</style>");
	this.wwrite("</head>");

	this.wwrite(" <body " + 
		"link=\"" + this.gLinkColor + "\" " + 
		"vlink=\"" + this.gLinkColor + "\" " +
		"alink=\"" + this.gLinkColor + "\" " +
		"text=\"" + this.gTextColor + "\">");
	this.wwrite(" <center><FONT FACE='" + fontface + "' SIZE=2><B>");
	this.wwrite("Year : " + this.gYear);
	this.wwrite("</B>");

	// Show navigation buttons
	var prevYYYY = parseInt(this.gYear) - 1;
	var nextYYYY = parseInt(this.gYear) + 1;
	
	this.wwrite("<CENTER><TABLE WIDTH='' BORDER=0 CELLSPACING=0 CELLPADDING=0 ><TR><TD ALIGN=center>");
	this.wwrite("[<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnItem + "', null, '" + prevYYYY + "', '" + this.gFormat + "'" +
		");" +
	    "\" alt='Prev Year'><img src=prev_year.gif><\/A>]</TD><TD ALIGN=center>");
	this.wwrite("[<A HREF=\"javascript:window.print();\">Print</A>]</TD><TD ALIGN=center>");
	this.wwrite("[<A HREF=\"" +
		"javascript:window.opener.Build(" + 
		"'" + this.gReturnItem + "', null, '" + nextYYYY + "', '" + this.gFormat + "'" +
		");" +
		"\">>><\/A>]</TD></TR>"+
        "</TABLE></CENTER>");

	// Get the complete calendar code for each month..
	var j;
	for (i=11; i>=0; i--) {
		if (isIE)
			this.wwrite("<DIV ID=\"layer" + i + "\" CLASS=\"lclass" + i + "\">");
		else if (isNav)
			this.wwrite("<LAYER ID=\"layer" + i + "\" CLASS=\"lclass" + i + "\">");

		this.gMonth = i;
		this.gMonthName = Calendar.get_month(this.gMonth);
		vCode = this.getMonthlyCalendarCode();
		this.wwrite(this.gMonthName + "/" + this.gYear );
		this.wwrite(vCode);

		if (isIE)
			this.wwrite("</DIV>");
		else if (isNav)
			this.wwrite("</LAYER>");
	}

	this.wwrite("</font></body></html>");
	this.gWinCal.document.close();
}

Calendar.prototype.wwrite = function(wtext) {
	this.gWinCal.document.writeln(wtext);
}

Calendar.prototype.wwriteA = function(wtext) {
	this.gWinCal.document.write(wtext);
}

Calendar.prototype.cal_header = function() {
	var vCode = "";
	vCode = vCode + "<TR class=turuncu2>";
	vCode = vCode + "<TD HEIGHT='16'><B>Paz</B></FONT></TD>";
	vCode = vCode + "<TD HEIGHT='16'><B>P</B></FONT></TD>";
	vCode = vCode + "<TD HEIGHT='16'><B>S</B></FONT></TD>";
	vCode = vCode + "<TD HEIGHT='16'><B>Ç</B></FONT></TD>";
	vCode = vCode + "<TD HEIGHT='16'><B>P</B></FONT></TD>";
	vCode = vCode + "<TD HEIGHT='16'><B>C</B></FONT></TD>";
	vCode = vCode + "<TD HEIGHT='16'><B>Cts</B></FONT></TD>";
	vCode = vCode + "</TR>";
	
	return vCode;
}

Calendar.prototype.cal_data = function() {
	var vDate = new Date();
	vDate.setDate(1);
	vDate.setMonth(this.gMonth);
	vDate.setFullYear(this.gYear);

	var vFirstDay=vDate.getDay();
	var vDay=1;
	var vLastDay=Calendar.get_daysofmonth(this.gMonth, this.gYear);
	var vOnLastDay=0;
	var vCode = "";

	/*
	Get day for the 1st of the requested month/year..
	Place as many blank cells before the 1st day of the month as necessary. 
	*/

	vCode = vCode + "<TR>";
	for (i=0; i<vFirstDay; i++) {
		vCode = vCode + "<TD class=turuncu2 WIDTH=''" + this.write_weekend_string(i) + "></TD>";
	}

	// Write rest of the 1st week
	for (j=vFirstDay; j<7; j++) { 
        
		vCode = vCode + "<TD WIDTH=''" + this.write_weekend_string(j) + " class=turuncu3>" + "\n" + 
			"<A HREF='#' class=turuncu3 " + 
				"onClick=\"SendDateTime('" + 
				this.format_data(vDay)+ 
				"');window.close();self.close();\">" + 
				this.format_day(vDay) + 
			"</A>" + "\n" + 
			"</TD>" + "\n" ;
		vDay=vDay + 1;
	}
	vCode = vCode + "</TR>" + "\n" ;

	// Write the rest of the weeks
	for (k=2; k<7; k++) {
		vCode = vCode + "<TR>";

		for (j=0; j<7; j++) {
			vCode = vCode + "<TD WIDTH=''" + this.write_weekend_string(j) + " >" + "\n" + 
				"<A HREF='#' class=turuncu3 " + 
					"onClick=\"SendDateTime('" + 
					this.format_data(vDay)  + 
					"');window.close();\">" + 
				this.format_day(vDay) + 
				"</A>" + "\n"+ 
				"</TD>"+ "\n" ;
			vDay=vDay + 1;

			if (vDay > vLastDay) {
				vOnLastDay = 1;
				break;
			}
		}

		if (j == 6)
			vCode = vCode + "</TR>";
		if (vOnLastDay == 1)
			break;
	}
	
	// Fill up the rest of last week with proper blanks, so that we get proper square blocks
	for (m=1; m<(7-j); m++) {
	  var mTime;
    	if (this.gYearly)
			vCode = vCode + "<TD class=\"turuncu2\" WIDTH='14%'" + this.write_weekend_string(j+m) + 
			"></TD>";
		else
			vCode = vCode + "<TD class=\"turuncu2\" WIDTH='14%'" + this.write_weekend_string(j+m) + 
			">" + m + "</TD>";
	}
	 vCode = vCode + "	</tr></table>"; 

	return (vCode);
}

Calendar.prototype.format_day = function(vday) {
	var vNowDay = gNow.getDate();
	var vNowMonth = gNow.getMonth();
	var vNowYear = gNow.getFullYear();

	if (vday == vNowDay && this.gMonth == vNowMonth && this.gYear == vNowYear)
		return ("<FONT COLOR=\"RED\"><B>" + vday + "</B></FONT>");
	else
		return (vday);
}

Calendar.prototype.write_weekend_string = function(vday) {
	var i;

	// Return special formatting for the weekend day.
	for (i=0; i<weekend.length; i++) {
		if (vday == weekend[i])
			return (" class=turuncu4 " );
	}
	
	return "";
}

Calendar.prototype.format_data = function(p_day) {
	var vData;
	var vMonth = 1 + this.gMonth;
	vMonth = (vMonth.toString().length < 2) ? "0" + vMonth : vMonth;
	var vMon = Calendar.get_month(this.gMonth).substr(0,3).toUpperCase();
	var vFMon = Calendar.get_month(this.gMonth).toUpperCase();
	var vY4 = new String(this.gYear);
	//var vY2 = new String(this.gYear.substr(2,2));
	var vDD = (p_day.toString().length < 2) ? "0" + p_day : p_day;

	switch (this.gFormat) {
		case "MM\/DD\/YYYY" :
			vData = vMonth + "\/" + vDD + "\/" + vY4;
			break;
		case "MM\/DD\/YY" :
			vData = vMonth + "\/" + vDD + "\/" + vY2;
			break;
		case "MM-DD-YYYY" :
			vData = vMonth + "-" + vDD + "-" + vY4;
			break;
		case "MM-DD-YY" :
			vData = vMonth + "-" + vDD + "-" + vY2;
			break;

		case "DD\/MON\/YYYY" :
			vData = vDD + "\/" + vMon + "\/" + vY4;
			break;
		case "DD\/MON\/YY" :
			vData = vDD + "\/" + vMon + "\/" + vY2;
			break;
		case "DD-MON-YYYY" :
			vData = vDD + "-" + vMon + "-" + vY4;
			break;
		case "DD-MON-YY" :
			vData = vDD + "-" + vMon + "-" + vY2;
			break;

		case "DD\/MONTH\/YYYY" :
			vData = vDD + "\/" + vFMon + "\/" + vY4;
			break;
		case "DD\/MONTH\/YY" :
			vData = vDD + "\/" + vFMon + "\/" + vY2;
			break;
		case "DD-MONTH-YYYY" :
			vData = vDD + "-" + vFMon + "-" + vY4;
			break;
		case "DD-MONTH-YY" :
			vData = vDD + "-" + vFMon + "-" + vY2;
			break;

		case "DD\/MM\/YYYY" :
			vData = vDD + "\/" + vMonth + "\/" + vY4;
			break;
		case "DD\/MM\/YY" :
			vData = vDD + "\/" + vMonth + "\/" + vY2;
			break;
		case "DD-MM-YYYY" :
			vData = vDD + "-" + vMonth + "-" + vY4;
			break;

		case "YYYY-MM-DD" :
			vData = vY4 + "-" + vMonth + "-" + vDD;
			break;

		case "DD-MM-YY" :
			vData = vDD + "-" + vMonth + "-" + vY2;
			break;

		default :
			vData = vMonth + "\/" + vDD + "\/" + vY4;
	}
    
	return vData;
}

function Build(p_item, p_month, p_year, p_format,pWtime) {
  
	var p_WinCal = ggWinCal;
    var maxYearObj = new Date();
   // OKOC Maximum yýl içinde bulunduðumuz yýlýn 1 fazlasý olacak þekilde ayarlanmýþtýr.
	if (p_year > (maxYearObj.getFullYear())+3) 
	   p_year = (maxYearObj.getFullYear())+3 ;
	else if (p_year < 1980)
	 	 p_year = 1980;

	gCal = new Calendar(p_item, p_WinCal, p_month, p_year, p_format);

	// Customize your Calendar here..
    gCal.gWtime = pWtime;
	gCal.gBGColor="#FFFFFF";
	gCal.gLinkColor="black";
	gCal.gTextColor="black";
	gCal.gHeaderColor="white";

	// Choose appropriate show function
	if (gCal.gYearly)	gCal.showY();
	else	gCal.show();
}

function show_calendar() {

    var Wtime ;
    self.focus();
	p_item = arguments[0];
	if (arguments[1] == null)
		p_month = new String(gNow.getMonth());
	else
		p_month = arguments[1];
	if (arguments[2] == "" || arguments[2] == null)
		p_year = new String(gNow.getFullYear().toString());
	else
		p_year = arguments[2];
	if (arguments[3] == null)
		p_format = "DD/MM/YYYY";
	else
		p_format = arguments[3];
 
   myX = arguments[4];
   myY = arguments[5];
   Wtime = arguments[6];   
    
 if (!Wtime){
    Wtime = false ;
    vWinCal = window.open("", "SatTakvim", 
		"width=170,height=179,status=no,resizable=no,top=" + (myY+15) + ",left=" + (myX-100) );
 }else{
    Wtime = true ;
    vWinCal = window.open("", "SatTakvim", 
		"width=170,height=225,status=no,resizable=no,top=" + (myY+15) + ",left=" + (myX-100) );
 }
    
	vWinCal.opener = self;
	ggWinCal = vWinCal;

	Build(p_item, p_month, p_year, p_format,Wtime);
}


function show_yearly_calendar(p_item, p_year, p_format) {
	// Load the defaults..
	if (p_year == null || p_year == "")
		p_year = new String(gNow.getFullYear().toString());
	if (p_format == null || p_format == "")
		p_format = "MM/DD/YYYY";

	var vWinCal = window.open("", "SatTakvim", "scrollbars=yes");
	vWinCal.opener = self;
	ggWinCal = vWinCal;

	Build(p_item, null, p_year, p_format);
}


