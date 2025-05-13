var months=new Array(12)
	months[0]="Ocak"
	months[1]="Þubat"
	months[2]="Mart"
	months[3]="Nisan"
	months[4]="Mayýs"
	months[5]="Haziran"
	months[6]="Temmuz"
	months[7]="Aðustos"
	months[8]="Eylül"
	months[9]="Ekim"
	months[10]="Kasým"
	months[11]="Aralýk"

var days=new Array(7)
	days[0]="Pazar"
	days[1]="Pazartesi"
	days[2]="Salý"
	days[3]="Çarþamba"
	days[4]="Perþembe"
	days[5]="Cuma"
	days[6]="Cumartesi"

var time = new Date()
var lmonth = months[time.getMonth()]
var lday = days[time.getDay()]
var date = time.getDate()
var year = time.getFullYear()
function writeDate()
	{
	document.write(date + " " + lmonth + " " + year + ", " + lday)	
	}