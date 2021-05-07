var ids = new Array();

$(document).ready(function(){
  $('#countSelected').html('0 elementi selezionati.');

  $('body').on('change','.selectionflag',function(){
    var checked = $(this).prop('checked');
    var id = $(this).attr('data-id');
    if(checked){
      if(!ids.includes(id))
      {      
        ids.push(id);
      }
    }
    else{
      var index = ids.indexOf(id);
      if(index != -1)
      {
        ids.splice(index,1);
      }
    }

    $('#countSelected').html(ids.length + ' elementi selezionati.');
    $('#ids').val(ids.join());
  });

  $('#dataGridExport').on('draw.dt',function(){
    $('#dataGridExport').find('input').each(function(){
      if(ids.indexOf($(this).attr('data-id')) > -1)
      {
        $(this).prop('checked',true);
      }
    });
  });

  $('#action').on('click',function(e){
    e.preventDefault();
    var formData = new FormData();
    formData.append('ids', ids);
    formData.append('exportAll', $('#exportAll').prop('checked'));
    formData.append('exportSelected', $('#exportSelected').prop('checked'));
    formData.append('exportTitle', $('#exportTitle').val());
    formData.append('exportFormat', $('#exportFormat').val());
    formData.append('exportAutBib', $('#exportAutBib').prop('checked'));
    formData.append('exportEmail', $('#exportEmail').val());
    formData.append('exportMets', $('#exportMets').val());
    formData.append('exportType', $('#exportType').val());

    var configFile = $('#configFile').prop('files');
    if (configFile !== undefined) {
      formData.append('configFile', configFile[0]);
    }

    $.ajax({
        url: Pinax.ajaxUrl + '&controllerName=metafad.gestioneDati.boards.controllers.ajax.Export',
        type: 'POST',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        success: function(data) {
            if(data.url!=null){
              location.href = data.url;
            }else{
              alert(data.msg);
            }
        },
        error: function() {
            alert('Si è verificato un problema.');
        }
    });

  });

});
