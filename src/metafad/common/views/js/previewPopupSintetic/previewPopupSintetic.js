jQuery(document).ready(function () {
  this.modalDivId = 'modalDiv-preview';
  this.modalIFrameId = 'modalIFrame-preview';

  function openModal(id)
  {
    var ajaxUrl = Pinax.ajaxUrl;
    var pageId = ajaxUrl.replace("ajax.php?pageId=","");
    pageId = pageId.replace("&ajaxTarget=Page&action=","");

    var w = Math.min($(window).width() - 50, 1100);

    Pinax.openIFrameDialog(
      '',
      'index.php?pageId=' + pageId +'_preview' + '&id=' + id + '&action=showPreview',
      w,
      $(window).width() / 4,
      $(window).height() / 4,
      this.openDialogCallback
    );

    $('.ui-dialog-titlebar-close').click(function () {
      $('.ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.ui-draggable').remove();
    });

    $('.ui-dialog-title').html('Anteprima');
    $('.ui-dialog-title').css('font-size', '24px');
    $('.ui-dialog-title').css('color', '#00c0ef');
    $('.ui-dialog-title').css('float', 'none');
    $('.ui-dialog-title').parent().css('text-align', 'center');

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

  $('.table').on('click', '.js-pinaxcms-preview', function () {
    console.log(window.location.href);
    openModal($(this).attr('id'));
  });

  window.addEventListener("close", this.close, false);

});
