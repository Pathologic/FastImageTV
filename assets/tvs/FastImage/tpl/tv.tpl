[+js+]
[+css+]
<input style="display:none;" id="tv[+tv_id+]" name="tv[+tv_id+]" value="[+tv_value+]">
<div id="FastImage[+tv_id+]" class="fi-placeholder">
    <input type="file" name="image" class="fi-upload-input">
    <img class="fi-image" src="[+image+]" alt="">
    <div class="fi-progress"></div>
    <div class="fi-actions">
        <a class="fi-btn fi-upload" href="#"><i class="fa fa-upload"></i></a>
        <a class="fi-btn fi-delete[+disabled+]" href="#"><i class="fa fa-trash"></i></a>
    </div>
</div>
<script type="text/javascript">
    (function($) {
        $('#FastImage[+tv_id+]').FastImageTV([+settings+]);
    })(jQuery)
</script>