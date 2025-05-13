function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function P7_getHW(t,a) { //v2.03 by PVII
  var r,p,h=0,w=0;if((p=MM_findObj(a)) !=null){
  if(document.all || document.getElementById){h=parseInt(p.offsetHeight);w=parseInt(p.offsetWidth);
  if(!h){h=parseInt(p.style.pixelHeight);w=parseInt(p.style.pixelWidth);}
  }else if(document.layers){h=parseInt(p.clip.height);w=parseInt(p.clip.width);}}
  if(t=="width"){r=w;}else{r=h;}return r; 
}

function P7_MM1dwt() { //v2.03 by PVII
  var g,lh,sw,fr = false;
  if(!document.p7mc)return;
  for(var x=0;x<p7m1.length;x++){tl=p7m1[x].id;lh="P7CM1DWT0"+tl;
	if((g=MM_findObj(lh)) !=null){fr=true;sw=0;break;}
	lh="P7CM1DWT1"+tl;if((g=MM_findObj(lh)) !=null){fr=true;sw=1;break;}}
  if(fr){eval("P7_trigMenuMagic1('"+tl+"',"+sw+")");}
}

function P7_setMenuMagic1() { //v2.03 by PVII
  var s,d,g,g2,gg,ww,kx,th,tu,ts,nu,xx,k=0,pa=0;args=P7_setMenuMagic1.arguments;
  if((navigator.appName=="Netscape") && parseInt(navigator.appVersion)>4 && navigator.userAgent.indexOf("Opera")==-1){pa="px";}
  if(navigator.userAgent.indexOf("Opera")>-1){P7OperaW=window.innerWidth;P7OperaH=window.innerHeight;}
  if(!document.p7mc) {
   p7m=new Array();document.p7mc=true;p7ms=new Array();document.P7_imswap=new Array();document.P7_imswapo=new Array();
   p7m1=new Array();p7m2=new Array();p7mprop=new Object();p7mprop.p7offset=args[0];p7mprop.p7rate=args[1];
   p7mprop.p7delay=args[2];p7mprop.p7bottom=args[3];
   if(document.layers){p7mprop.p7pageh = document.height;}}
  for(var x=4;x<args.length;x+=3){if((g=MM_findObj(args[x])) !=null){
    p7m[k]=args[x];g.p7imname=args[x+2];g.p7sub=args[x+1];
    g2=MM_findObj(args[x+2]);tu=g2.src;ts=tu.lastIndexOf(".");
    nu=tu.substring(0,ts)+"_open"+tu.substring(ts,tu.length);
	nu2=tu.substring(0,ts)+"_over"+tu.substring(ts,tu.length);
    document.P7_imswap[k]=new Image();document.P7_imswap[k].src=nu;
	document.P7_imswapo[k]=new Image();document.P7_imswapo[k].src=nu2;k++;}}
  var lf=0;for (var j=0;j<p7m.length;j++){
   if((g=MM_findObj(p7m[j])) !=null){d=(document.layers)?g:g.style;p7m1[j]=g;g.p7waiting=false;
    if(j==0){lf=parseInt(d.left);th=parseInt(d.top);}
    if(j>0){d.left=(lf+pa);th+=P7_getHW('height',p7m[j-1]);d.top=(th+pa);}
    if((s=MM_findObj(g.p7sub)) !=null){p7m2[j]=s;ww=P7_getHW('width',g.p7sub);
     kx=lf-ww-20;dd=(document.layers)?s:s.style;
     dd.left=(kx+pa);dd.top=(th+pa);p7ms[j]=th;dd.visibility="visible";s.p7open=false;s.p7waiting=false;}}}
  if((g=MM_findObj(p7mprop.p7bottom)) !=null){d=(document.layers)?g:g.style;
   d.left=(lf+parseInt(args[0])+pa);th+=P7_getHW('height',p7m[p7m.length-1]);d.top=(th+pa);}
}

function P7_wait(a) { //v2.03 by PVII
  var ma,mb;if((mb=MM_findObj(a)) !=null){
   if(!mb.p7waiting || mb.p7waiting=="none"){return;}
    ma=mb.p7waiting;mb.p7waiting=false;eval(ma);}
}

function P7_trigMenuMagic1(a,sw) { //v2.03 by PVII
  var x,g,gg,d,dd,w,lp,tp,im,im2,ts,nu,e,pa=0;
  if(!document.p7mc)return;
  if((navigator.appName=="Netscape") && parseInt(navigator.appVersion)>4 && navigator.userAgent.indexOf("Opera")==-1){pa="px";}
  if(navigator.userAgent.indexOf("Opera")>-1){if( P7OperaW!=window.innerWidth || P7OperaH!=window.innerHeight)P7_setMenuMagic1();}
  var ofs=parseInt(p7mprop.p7offset),trt = parseInt(p7mprop.p7rate);
  var tdy=parseInt(p7mprop.p7delay),tsb,tlf,tst;
  for(x=0;x<p7m.length;x++){if(p7m[x]==a){
    d=p7m1[x];dd=(document.layers)?d:d.style;g=p7m2[x];gg=(document.layers)?g:g.style;
	e=MM_findObj(d.p7imname);im=e.src;ts=im.replace("_open","");ts=ts.replace("_over","");
	 if(!g.p7open){tst="closed";im2=ts.lastIndexOf(".");
	  nu=ts.substring(0,im2)+"_open"+ts.substring(im2,ts.length);ts = nu;
	 }else{tst="open"}break;}}
  for(j=0;j<p7m.length;j++){
   d=p7m1[j];dd=(document.layers)?d:d.style;g=p7m2[j];gg=(document.layers)?g:g.style;
   if(j==0){tlf=parseInt(dd.left);}
   if(g.p7open){w=P7_getHW('width',d.p7sub)+20;w-=parseInt(dd.left);w*=-1;d.p7waiting=false;
	eval("P7_BM1('"+d.p7sub+"',"+w+","+parseInt(gg.top)+","+trt+","+tdy+",0,0)");}
	d.p7waiting=false;g.p7open=false;
    if(parseInt(sw)==1){e=MM_findObj(d.p7imname);im=e.src;im2=im.replace("_open","");e.src=im2;}}
  var tnt=new Array();var df=0,tcd=0,tdl=p7m[0];
  for(j=0;j<p7m.length;j++){
   d=p7m1[j];dd=(document.layers)?d:d.style;g=p7m2[j];gg=(document.layers)?g:g.style;
   if(j==0){th=parseInt(dd.top);}tnt[j]=th;df=Math.abs(parseInt(dd.top)-th);
   if(df>tcd){tdl=p7m[j];tcd=df;}th+=P7_getHW('height',p7m[j]);
   if(x==j && tst=="closed"){tsb=th;th+=P7_getHW('height',d.p7sub);}p7ms[j]=th;}
   if(tst=="closed") {d=p7m1[x];dd=(document.layers)?d:d.style;
	g=p7m2[x];gg=(document.layers)?g:g.style;lp=tlf+ofs;
	gg.top=(tsb+pa);p7ms[x]=tsb;gg.visibility="visible";e=MM_findObj(d.p7imname);
	if(parseInt(sw)==1){e.src=ts;}var r;g.p7open=true;r=MM_findObj(tdl);		
	r.p7waiting="P7_BM1('"+d.p7sub+"',"+lp+","+tsb+","+trt+","+tdy+",0,0)" ;
   }else{d=p7m1[p7m1.length-1];d.p7waiting="none";}
  for(j=0;j<p7m.length;j++ ){eval("P7_BM1('"+p7m[j]+"',"+tlf+","+tnt[j]+","+trt+","+tdy+",0,0)");}
  if((g=MM_findObj(p7mprop.p7bottom)) !=null){d=(document.layers)?g:g.style;g.p7waiting=false;
   eval("P7_BM1('"+p7mprop.p7bottom+"',"+(tlf+ofs)+","+th+","+trt+","+tdy+",0,0)");
   th+=(document.layers)?P7_getHW('height',p7mprop.p7bottom):0;}
  if(document.layers){var tw2=document.width;
    if(document.height<th) {document.height=th;document.width=tw2;}}
}

function P7_rollCMenu1(ev,a,b) { //v2.02 by PVII
 var e,im,ts,j,nu,g,tev=ev.type;
 if(!document.p7mc)return;
 if(tev=="mouseover"){for(var x=0;x<p7m.length;x++){
 if(p7m[x]==a){g=p7m2[x];if(parseInt(b)==0 && g.p7open) {break;return;}
 e=MM_findObj(p7m1[x].p7imname);im=e.src;ts=im.replace("_open","");
 ts=ts.replace("_over","");j=ts.lastIndexOf(".");
 e.src=ts.substring(0,j)+"_over"+ts.substring(j,ts.length);break;}}
 }else if(tev=="mouseout"){for(var x=0;x<p7m.length;x++){
 if(p7m[x]==a){e=MM_findObj(d=p7m1[x].p7imname);im=e.src;
 g=p7m2[x];ts=im.replace("_open","");ts=ts.replace("_over","");
 if(g.p7open){j=ts.lastIndexOf(".");
 nu=ts.substring(0,j)+"_open"+ts.substring(j,ts.length);
 }else{nu=ts;}e.src=nu;break;}}}
}

function P7_BM1(el,x,y,a,b,c,s) { //v2.03 by PVII
 var g,elo=el,f="",m=false,d="";x=parseInt(x);y=parseInt(y);
 var t = 'g.p7BM = setTimeout("P7_BM1(\''+elo+'\','; 
 if ((g=MM_findObj(el))!=null) {d=(document.layers)?g:g.style;}else{return;}
 var xx=(parseInt(d.left))?parseInt(d.left):0;
 var yy=(parseInt(d.top))?parseInt(d.top):0;
 var i=parseInt(a);
  if (eval(g.moved)){clearTimeout(g.p7BM);}
  if (xx<x){xx+=i;m=true;if(xx>x){xx=x;}}
  if (xx>x){xx-=i;m=true;if(xx<x){xx=x;}}
  if (yy<y){yy+=i;m=true;if(yy>y){yy=y;}}
  if (yy>y){yy-=i;m=true;if(yy<y){yy=y;}}
 if (m) {if((navigator.appName=="Netscape") && parseInt(navigator.appVersion)>4 && navigator.userAgent.indexOf("Opera")==-1){
    xx+="px";yy+="px";}
  d.left=xx;d.top=yy;g.moved=true;eval(t+x+','+y+','+a+','+b+','+c+',0)",'+b+')');
  }else {g.moved=false;P7_wait(elo);}
}