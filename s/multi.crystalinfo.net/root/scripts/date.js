var months=new Array(12)
	months[0]="Ocak"
	months[1]="�ubat"
	months[2]="Mart"
	months[3]="Nisan"
	months[4]="May�s"
	months[5]="Haziran"
	months[6]="Temmuz"
	months[7]="A�ustos"
	months[8]="Eyl�l"
	months[9]="Ekim"
	months[10]="Kas�m"
	months[11]="Aral�k"

var days=new Array(7)
	days[0]="Pazar"
	days[1]="Pazartesi"
	days[2]="Sal�"
	days[3]="�ar�amba"
	days[4]="Per�embe"
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