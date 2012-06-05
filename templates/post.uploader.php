
<!--
              <button type="submit" class="btn btn-primary start">
                  <i class="icon-upload icon-white"></i>
                  <span>Start upload</span>
              </button>
              <button type="reset" class="btn btn-warning cancel">
                  <i class="icon-ban-circle icon-white"></i>
                  <span>Cancel upload</span>
              </button>
              <button type="button" class="btn btn-danger delete">
                  <i class="icon-trash icon-white"></i>
                  <span>Delete</span>
              </button>
              <input type="checkbox" class="toggle">-->
          <!--<div class="span5 fileupload-progress fade">
              <div class="progress progress-success progress-striped active">
                  <div class="bar" style="width:0%;"></div>
              </div>
              <div class="progress-extended">&nbsp;</div>
          </div>-->
      <div class="fileupload-loading"></div>


<!-- modal-gallery is the modal dialog used for the image gallery -->
<div id="modal-gallery" class="modal modal-gallery hide fade" data-filter=":odd">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h3 class="modal-title"></h3>
    </div>
    <div class="modal-body"><div class="modal-image"></div></div>
    <div class="modal-footer">
        <a class="btn modal-download" target="_blank">
            <i class="icon-download"></i>
            <span>Download</span>
        </a>
        <a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000">
            <i class="icon-play icon-white"></i>
            <span>Slideshow</span>
        </a>
        <a class="btn btn-info modal-prev">
            <i class="icon-arrow-left icon-white"></i>
            <span>Previous</span>
        </a>
        <a class="btn btn-primary modal-next">
            <span>Next</span>
            <i class="icon-arrow-right icon-white"></i>
        </a>
    </div>
</div>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <div class="template-upload fade ultile">
        <div class="preview"><span class="fade"></span></div>
        <!--<div class="name"><span>{%=file.name%}</span></div>-->
        <div class="size"><span>{%=o.formatFileSize(file.size)%}</span></div>
        {% if (file.error) { %}
            <div class="error"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</div>
        {% } else if (o.files.valid && !i) { %}
            <div>
                <div class="progress progress-success progress-striped active"><div class="bar" style="width:0%;"></div></div>
            </div>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>{%=locale.fileupload.start%}</span>
                </button>
            {% } %}</td>
        {% } else { %}
            
        {% } %}
        <!--<div class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>{%=locale.fileupload.cancel%}</span>
            </button>
        {% } %}</div>-->
    </div>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <div class="template-download fade ultile">
        {% if (file.error) { %}
            <div class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</div>
        {% } else { %}
            <div class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</div>
            <!--<div class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
            </div>-->
            <div class="size" style="text-align: center"><span>{%=o.formatFileSize(file.size)%}</span></div>
        {% } %}
        <div class="delete" style="text-align: center" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
            <a>
                <span>delete</span>
            </a>
        </div>
    </div>
{% } %}
</script>
