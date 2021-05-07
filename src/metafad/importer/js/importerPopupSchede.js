$(document).ready(function(){
    
  
    window["openModal"]= function openModal($data) {  
        
      this.modalDivId = 'modalDiv-preview';
      this.modalIFrameId = 'modalIFrame-preview';
    
      function openModal(lista)
      {
        var ajaxUrl = Pinax.ajaxUrl;
        var pageId = ajaxUrl.replace("ajax.php?pageId=","");
        pageId = pageId.replace("&ajaxTarget=Page&action=","");
    
        var w = Math.min($(window).width() - 50, 1100);
    
        Pinax.openIFrameDialog(
          '',
          'index.php?pageId=' + 'Import_preview',
          w,
          $(window).width() / 4,
          $(window).height() / 4,
          this.openDialogCallback
        );
    
        $('.ui-dialog-titlebar-close').click(function () {
          $('.ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.ui-draggable').remove();
        });
    
        $('.ui-dialog-title').html('Import');
        $('.ui-dialog-title').css('font-size', '24px');
        $('.ui-dialog-title').css('color', '#00c0ef');
        $('.ui-dialog-title').css('float', 'none');
        $('.ui-dialog-title').parent().css('text-align', 'center');
        $('.modal-body').show();
      }
    
      function close(event)
      {
        $('.ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.ui-draggable').remove();
      }
    
      function receiveMessage(event)
      {
        Pinax.closeIFrameDialog(true);
        var d = event.data;
      }
    
      
      openModal($data.data);
     
    
      window.addEventListener("close", this.close, false);
      
  
    }
    
  });