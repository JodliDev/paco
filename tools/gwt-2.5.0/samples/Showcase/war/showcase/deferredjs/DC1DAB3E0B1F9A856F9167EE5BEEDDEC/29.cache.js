function fkb(a){this.a=a}
function ckb(a,b){this.a=a;this.b=b}
function $jb(a){var b,c,d;b=kdc(hr(a.a.cb,qvc));c=kdc(hr(a.b.cb,qvc));d=kdc(hr(a.c.cb,qvc));fRb(a.d,"User '"+b+"' has security clearance '"+c+"' and cannot access '"+d+trc)}
function Zjb(a){var b,c,d,e,f,g;d=new RTb;b=DH(d.j,99);d.o[xtc]=5;g=F3(qR);e=new CMb(g);kj(e,new ckb(a,g),($w(),$w(),Zw));f=new HVb;f.e[xtc]=3;EVb(f,new oRb(lyc));EVb(f,e);LTb(d,0,0,f);$Tb(b,0)[pvc]=2;ITb(d,1,0,myc);ITb(d,1,1,"User '{0}' has security clearance '{1}' and cannot access '{2}'");a.a=new oZb;eZb(a.a,'amelie');ITb(d,2,0,nyc);LTb(d,2,1,a.a);a.b=new oZb;eZb(a.b,'guest');ITb(d,3,0,oyc);LTb(d,3,1,a.b);a.c=new oZb;eZb(a.c,'/secure/blueprints.xml');ITb(d,4,0,pyc);LTb(d,4,1,a.c);a.d=new mRb;ITb(d,5,0,vyc);LTb(d,5,1,a.d);dUb(b,5,0,(bVb(),aVb));c=new fkb(a);kj(a.a,c,(Kx(),Kx(),Jx));kj(a.b,c,Jx);kj(a.c,c,Jx);$jb(a);return d}
o1(643,1,Enc,ckb);_.Dc=function dkb(a){y3(this.a,this.b+tyc)};_.a=null;_.b=null;o1(644,1,pnc,fkb);_.Fc=function gkb(a){$jb(this.a)};_.a=null;o1(645,1,Hnc);_.lc=function kkb(){Y3(this.b,Zjb(this.a))};var qR=Zbc(uuc,'ErrorMessages'),dR=Xbc(uuc,'CwMessagesExample$1',643),eR=Xbc(uuc,'CwMessagesExample$2',644);uoc(wn)(29);