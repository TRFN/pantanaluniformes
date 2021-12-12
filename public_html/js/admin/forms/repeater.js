LWDKExec(()=>$(".repeater-instance").each(function(){One(this).repeater({
   initEmpty: !1,
   isFirstItemUndeletable: true,
   show: function (e){
	   let n,p;


	   String($(".money").length) != "0" && $(".money").inputmask('decimal', {
		   'alias': 'numeric',
		   'groupSeparator': '.',
		   'autoGroup': true,
		   'digits': 2,
		   'radixPoint': ",",
		   'digitsOptional': false,
		   'allowMinus': false,
		   'prefix': 'R$ ',
		   'placeholder': '0,00'
	   });


	   if((n=(p=$(this).closest(".repeater-instance")).data("max-repeat")) !== "undefined" && $(this).closest(".repeater-instance").find('[data-repeater-item]').length > n){
		   $(this).closest('[data-repeater-item]').find("[data-repeater-delete]")[0].click();
		   return swal.fire("",p.data("max-repeat-msg"), "error")&&false;
	   }

       if($(this).find('.bootstrap-select').length>0){
           $(this).find('.bootstrap-select').replaceWith($(this).find('.bootstrap-select select').removeClass("_mod")[0].outerHTML);
       }

	   $(this).find('button').removeClass("m--hide");

	   $(this).closest('[data-repeater-item]').find("span.index").text($(this).closest(".repeater-instance").find('[data-repeater-item]').length);

	   setTimeout(()=>$(this).slideDown(),300);

	   setTimeout(()=>$(this).find('.m_selectpicker').each(function(){
	   	One(this).selectpicker();
	   }),100);
   },
   hide: function (e) {
       $(this).slideUp(e);
   }
})}));

window.repeaterGetData = function(_class,primary=false){
	let els = "[data-name]" + _class;
	let data = (MapEl(els, function(data){return [$(this).data("name"),this.value];}, false, false)).chunk($(els).closest("[data-repeater-item]").first().find(els).length * 2);

	let ndata = primary===false?[]:{};
	// console.log(data);
	for(let i = 0; i < data.length; i++){
		data[i] = MapKeyAssign(data[i]);
		primary===false
			? ndata.push(data[i])
			: ((typeof ndata[data[i][primary]] == 'undefined' && (ndata[data[i][primary]] = [])), ndata[data[i][primary]].push(data[i]));
	}

	return ndata;
}
