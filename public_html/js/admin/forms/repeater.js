LWDKExec(()=>One(".repeater-instance").repeater({
   initEmpty: !1,
   isFirstItemUndeletable: true,
   show: function (e){
	   let n,p;

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
}));
