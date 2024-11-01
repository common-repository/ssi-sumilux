

<link media="all" type="text/css" href="<?php echo SSI_PLUGIN_URL;?>/css/login.css" rel="stylesheet">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<?php echo($html_block['html-head-code']);?>



<script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {
    <?php $code = str_replace('"', '\"', $html_block['html-body-code']);
            $code = str_replace("\n", " ", $code);

    ?>
        var $ssi = "<?php echo($code);?>";
        var $errors = "<?php echo SsiUser::$errors;?>";

        var target = function(){
            if($("#loginform").length > 0) {
                return $("#loginform");
            }

            if($("#registerform").length > 0) {
                return $("#registerform");
            }

            return $("#lostpasswordform");
        }();

        if ($errors != 0){
            $("#login h1").after("<div id=\"login_error\">" + $errors + "</div>\n");
        }



        if($("#setupform").length>0 && $(".mu_register").length>0) {
            $("#setupform").after("<div class='ssi-signup-wrap'><div class='or-wrap'><div class='or-text'>OR</div></div><div id='componentDiv'></div></div>");
        }

        (function(elem){
            elem.wrap("<div class='login-panel login-panel-wp'></div>")
                .after($("#nav")).parent()
                .after("<div class='login-panel login-panel-ssi'><form id='componentDiv'><h2 style='margin: 40px 0px 20px 0px; display: block;'>Sign in with any of the following</h2>" +$ssi + "</form><div id=\"powered-ssi\">Powered by <a href=\"http://ssi.sumilux.com/ssi\" title=\"version: <?php echo SSI_VERSION?>\">Sumilux SSI</a></div>")
                .after("<div class='login-sep-text float-left'><h3>OR</h3></div>");

        <?php if(ssi_IS_3_2): ?>
            $("#nav").after($("#backtoblog"));
            <?php endif;?>

        }(target));


    });
    //]]>
</script>