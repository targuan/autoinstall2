<div class="partition">
<pre style="padding:10px; white-space: pre-wrap;word-wrap: break-word;">
<?php echo $content ?>
</pre>
</div>

<script>
function update() {
    $.get(window.location.pathname,
          "",
          function(data){
            $('pre')[0].innerHTML = $(data).children('pre')[0].innerHTML
            setTimeout(function() {
                $('body').scrollTop($('body').height())
                }, 300)
          })
}
setInterval(update,3000)
</script>
