// type = hidetoggle
function hidetoggle(cls){
     jQuery('.'+cls).toggle();
}

// type = emptyonempty
function emptyonempty(el,id){
     var value = jQuery(el).val();
     //console.log('emptyonempty: '+value);
     //if(value=='' || value==undefined || value==null){
     if(!value){
          //console.log('ok');
          var firstch = jQuery('#'+id+' option:nth-child(1)').clone();
          jQuery('#'+id).empty();
          jQuery('#'+id).append(firstch);
     }
}

// type = emptyonfull
function emptyonfull(el,id){
     var value = jQuery(el).val();
     if(value){
          var firstch = jQuery('#'+id+' option:nth-child(1)').clone();
          jQuery('#'+id).empty();
          jQuery('#'+id).append(firstch);
     }
}

// type = disableonempty
function disableonempty(el,id){
     var value = jQuery(el).val();
     //console.log('disableonempty: '+value);
     //if(value=='' || value==undefined || value==null){
     if(!value){
          //console.log('ok');
          jQuery('#'+id).prop('disabled',true);
     }
}

// type = disableonfull
function disableonfull(el,id){
     var value = jQuery(el).val();
     if(value) jQuery('#'+id).prop('disabled',true);
}

// type = enableonfull
function enableonfull(el,id){
     var value = jQuery(el).val();
     //console.log('enableonfull: '+value);
     //console.log(value);
     //console.log(value=='');
     //if(value!='' || value!=undefined || value!=null){
     if(value){
          //console.log('ok');
          jQuery('#'+id).prop('disabled',false);
     }
}

// type = ajaxload
function loaddata(el,data,id){ 
     
     var firstch = jQuery('#'+id+' option:nth-child(1)').clone();
     jQuery('#'+id).empty();
     jQuery('#'+id).prop('disabled',true);
     jQuery('#'+id).append('<option>Načítám...</option>');

     jQuery.ajax({
          type: "POST",
          url: DOKU_BASE+'lib/exe/ajax.php',
          data: {
               call: 'piratiform',
               id: JSINFO.id,
               data: data,
               value: jQuery(el).val()
          },
          success: function(d){
               //
               jQuery('#'+id).empty();
               jQuery('#'+id).append(firstch);

               //
               if(jQuery.isArray(d)){
                    jQuery.each(d,function(i,el){
                         jQuery('#'+id).append(jQuery('<option value="'+el.value+'"'+(el.disabled=='disabled'?'disabled="disabled"':'')+'>'+el.name+'</option>'));
                    });
               }
               jQuery('#'+id).prop('disabled',false);
               //jQuery('#'+id).trigger('change');
          },
          dataType: 'json'
     });
}

// type = typeaheadload
function loadtypeahead(el,data,id){ 

     jQuery.ajax({
          type: "POST",
          url: DOKU_BASE+'lib/exe/ajax.php',
          data: {
               call: 'piratiform',
               id: JSINFO.id,
               data: data,
               value: jQuery(el).val()
          },
          success: function(d){
               //
               //if(jQuery.isArray(d)){
                 //   jQuery.each(d,function(i,el){
                   //      jQuery('#'+id).append(jQuery('<option value="'+el.value+'"'+(el.disabled=='disabled'?'disabled="disabled"':'')+'>'+el.name+'</option>'));
               //     });
               //}
               //

               var parent = jQuery('#'+id).parent();
               var html = parent.html();
               jQuery('#'+id).remove();
               parent.html(html);

               jQuery('#'+id).typeahead({ source: d });
          },
          dataType: 'json'
     });

}

// type = required
function piratiform_required(el,cls){
     jQuery('select,input',jQuery('.'+cls)).prop('required',function(index,old){
          //if(old==undefined) return true;
          //else return false;
          if(old){
               //console.log(jQuery(this).next());
               jQuery(this).parent().contents().filter(function(){ return this.nodeType === 3; }).remove();
               jQuery('span.label-important',jQuery(this).parent()).remove();
          } else {
               jQuery(this).after('&nbsp;<span class="label label-important" title="Povinný údaj">!</span>'); 
          }
          
          return !old;
     });
}


jQuery(document).ready(function(){
     jQuery('.piratiform_form').submit(function(){
          jQuery('button.piratiform').button('loading');
     });
});

