$(document).ready(
  
  
  function(){



  if( !window.location.href.includes("editMassive"))
    return;
   //debugger; :)0
    
  var pathname = window.location.pathname;
  var buttons ="<i class='btn btn-light btn-flat fa fa-trash svuota disable' name='svuota'></i>";
      buttons = buttons + "<i class='btn btn-light btn-flat fa fa-pencil sostituisci disable' name='sostituisci'></i>";
      buttons = buttons + "<i class='btn btn-light btn-flat fa fa-search trovaesost disable' name='trovaesost'></i>";
      buttons = "<div class='pulsanti'>"+buttons+"</div>";
    //<input style='margin-left:1em'type='text'>
    
    var idslist = $('#__id').val();
    var model   = $('#__model').val();
    var formId = 'editForm';
    if( pathname.includes("editMassive")){
        
        
        $(buttons).insertBefore('#'+formId+' input[name]:not( [type="button"], [type="submit"], [type="reset"],[type="hidden"],[id="groupName"]), '+
        '#'+formId+' textarea[name], '+
        '#'+formId+' select[name]');
        
        $('#'+formId+' input[name]:not( [type="button"], [type="submit"], [type="reset"],[id="groupName"]), '+
          '#'+formId+' textarea[name], '+
          '#'+formId+' select[name]').hide();

        /*$('#innerTabs_content').bind("DOMSubtreeModified", function() {
            
            var asd="<i class='btn btn-light btn-flat' ></i>";
            
            $('#'+formId+" input[value='Aggiungi']").val("Modifica");
            $(asd).insertAfter('.GFEAddRow');
            
        });*/
        
        
        $('#editForm [data-type="FormEditSelectMandatory"]').each(function () {

            var father = $(this).parent();
            
            $(father).find('.trovaesost').attr("disabled",true);

        });

        $('#editForm [data-type="FormEditSelectMassive"]').each(function () {

            var father = $(this).parent();
            
            $(father).find('.trovaesost').attr("disabled",true);

        });
    }


     addcomponente = function(elemento){

        var componente;
        if( elemento.attributes['name'].nodeValue.localeCompare('svuota')==0){

            componente = "<p name='svuotac1'>il valore verrà cancellato</p>"
            
        }else if(elemento.attributes['name'].nodeValue.localeCompare('sostituisci')==0){

            componente = "<p name='sostituiscic2'> il valore impostato sarà</p>";
            elemento.parentElement.nextSibling.style.display="block";

        }
        else if(elemento.attributes['name'].nodeValue.localeCompare('trovaesost')==0){

            componente = "<p name='trovaesostc3'> il valore <input style='margin-left:1em'type='text'> presente sarà sostituito con</p>";
            elemento.parentElement.nextSibling.style.display="block";

        }
        $(componente).insertAfter(elemento.parentElement.lastChild);
        


    };

     removecomponente = function( elemento){

        if( elemento.parentElement.lastChild.attributes['name'].nodeValue.localeCompare('svuotac1')==0){

            elemento.parentElement.lastChild.remove();

        }else if(elemento.parentElement.lastChild.attributes['name'].nodeValue.localeCompare('sostituiscic2')==0){

            elemento.parentElement.lastChild.remove();
            $(elemento.parentElement.nextSibling).hide();
        }
        else if(elemento.parentElement.lastChild.attributes['name'].nodeValue.localeCompare('trovaesostc3')==0){

            elemento.parentElement.lastChild.remove();
            $(elemento.parentElement.nextSibling).hide();
        }

     };

    $(".disable").click( function(){

                
        if( this.className.includes('btn-info')){
            
            this.className = this.className.replace('btn-info','btn-light');
            removecomponente(this);
        }    
        else{

            this.className = this.className.replace('btn-light','btn-info')
            var nodi = this.parentElement.childNodes;
            
            
            for (i = 0; i < nodi.length; i++) {
                
                if( nodi[i].attributes['name'].nodeValue.localeCompare(this.attributes['name'].nodeValue)==0) continue;

                nodi[i].className = nodi[i].className.replace('btn-info','btn-light');
                removecomponente(nodi[i]);
            } 
            addcomponente(this);



        }


    });


    

  } );



