<?php
    class __dinamicJS {
        static function ajaxCore(){
			$ajaxLoadingImageWidth = "48";
		return 'const LWDKExec = window.LWDKExec = function LWDKExec(fn){ typeof FormCreateAction !== "undefined" ? fn():document.addEventListener("DOMContentLoaded", fn, false); return 0; };const animateCSS=(n,e,a="animate__")=>new Promise((t,i)=>{const o=`${a}${e}`,s=document.querySelector(n);s.classList.add(`${a}animated`,o,"animate__faster"),s.addEventListener("animationend",function(n){n.stopPropagation(),s.classList.remove(`${a}animated`,o),t("Animation ended")},{once:!0})});const LWDKSelectElements = sel => Array.from(document.querySelectorAll(sel));function LWDKLinks(){var a = LWDKSelectElements("a[ajax=on]"), i; for( i = 0; i < a.length; i++){a[i].setAttribute("ajax","off"); a[i].href = "{mydomain}" + ("{URLPrefix}" + a[i].href).split("{mydomain}").join(""); /*a[i].addEventListener("click",function(evt){evt.stopPropagation(); evt.preventDefault(); LWDKLoadPage(this.href, LWDKLinks); return false;})*/}} function LWDKLoadPage(page,fn){var ajax={};ajax.x=function(){if("undefined"!=typeof XMLHttpRequest)return new XMLHttpRequest;for(var e,t=["MSXML2.XmlHttp.6.0","MSXML2.XmlHttp.5.0","MSXML2.XmlHttp.4.0","MSXML2.XmlHttp.3.0","MSXML2.XmlHttp.2.0","Microsoft.XmlHttp"],n=0;n<t.length;n++)try{e=new ActiveXObject(t[n]);break}catch(e){}return e},ajax.send=function(e,t,n,o,a){void 0===a&&(a=!0);var r=ajax.x();return(r.open(n,e,a),r.onreadystatechange=function(){4==r.readyState&&t(r.responseText)},"POST"==n&&r.setRequestHeader("Content-type","application/x-www-form-urlencoded"),r.send(o))},ajax.get=function(e,t,n,o){var a=[];for(var r in t)a.push(encodeURIComponent(r)+"="+encodeURIComponent(t[r]));ajax.send(e+(a.length?"?"+a.join("&"):""),n,"GET",null,o)},ajax.post=function(e,t,n,o){var a=[];for(var r in t)a.push(encodeURIComponent(r)+"="+encodeURIComponent(t[r]));return ajax.send(e,n,"POST",a.join("&"),o)||true;};typeof swal != "undefined" && (Swal.fire({customClass:"loading-page py-0",position: "bottom-end",html: "<img width=' . $ajaxLoadingImageWidth . ' style=\'margin-top: 0px; margin-left: -16px;\' src=\'/images/loading.gif\' />",width: "' . $ajaxLoadingImageWidth . 'px",showConfirmButton: false,allowOutsideClick: false}));return true&&ajax.get( page + "?ajax=1",{},function(data){(e=document.getElementById("page_content")).innerHTML=data;animateCSS("#page_content","fadeIn");fn();let scripts = LWDKSelectElements("script[lwdk-addons]"), i; for( i = 0; i < scripts.length; i++){eval(scripts[i].innerText);} LWDKLocal=page;history.pushState("", "", page);setTimeout(()=>{LWDKInitFunction.exec();setTimeout(()=>swal.close(),600);},600);})}document.addEventListener("DOMContentLoaded",LWDKLinks,true);window.LWDKLocal=location.href;setInterval(function(){LWDKLocal!==location.href&&LWDKLoadPage(LWDKLocal=location.href, LWDKLinks);},600)';
        }

        static function initScripts(){
            return 'LWDKInitFunction = window.LWDKInitFunction = ({
				functions: [],
				addFN: function(toAdd){
					this.functions.push(toAdd);
				},
				exec: function(){
					for(let i in this.functions){
						this.functions[i]();
					}
				}
			});

            document.addEventListener("DOMContentLoaded",()=>LWDKInitFunction.exec(),true);

            // window.onkeydown=function(e){
            //     if(e.keyCode == 116){
            //         refresh();
            //         return false;
            //     }
            //     return true;
            // };
            ';
        }
    }
