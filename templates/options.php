<div class="wrap">
    <div>

        <div style="margin-top: 40px; font-size: 14px;width: 90%;">
            Please enter the information for a social-sign-in widget below, to enable the social sign-in functions for
            your WordPress site.
            You can apply for such a widget on our <a href="http://www.social-sign-in.com">web site</a>, or you can
            click the "Create a Widget Right Now" button below to have it created for this site immediately.
        </div>

        <form method="post" style="margin-top: 20px;">
            <style>
                table.admin_table tr{
                    height: 55px;
                    f
                }
            </style>
            <table class="admin_table">

                <?php foreach ($plugoptions as $value) {
                if ($value['type'] == "text") {
                    ?>

                    <tr valign="top">

                        <th scope="row" style="padding-top:5px;  "><?php echo $value['name']; ?>:</th>

                        <td><input name="<?php echo $value['id']; ?>"
                                   id="<?php echo $value['id']; ?>"
                                   type="<?php echo $value['type']; ?>"
                                   value="<?php
                                       if (!empty($ssi_recieved[$value['id']])) {
                                           echo $ssi_recieved[$value['id']];
                                       } elseif (get_option($value['id']) != "") {
                                           echo htmlspecialchars(
                                               get_option($value['id']));
                                       }  ?>"
                                   style="width: 300px;"
                            />

                            <br/>

                            <?php echo $value['desc']; ?>
                        </td>

                    </tr>
                    <?php
                } elseif ($value['type'] == "checkbox") {
                    if (get_option($value['id'])) {

                        $checked = "checked=\"checked\"";

                    } else {
                        $checked = "";
                    }

                    ?>
                    <tr valign="top">

                        <th scope="row"><?php echo $value['name']; ?>:</th>

                        <td>
                            <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"
                                   value="true"
                                <?php echo $checked; ?> />
                        </td>

                    </tr>
                    <?
                }
            } ?>

                <tr valign="top">

                    <th scope="row"> </th>

                    <td>
                        <?php if (!empty($ssi_recieved)) { ?>
                        <i style="color: red">Please, save new options of SSI plugin.</i>
                        <?php }?>
                        <h3><a href="https://social-sign-in.com/index.php/easycreate?returnurl=<?php echo $returnURL;?>"
                               id="api-key"
                            <?php echo"";/*href='javascript:window.open("http://ssi.sumilux.com/ssi/index.php/easycreate?returnurl=<?php echo $returnURL;?>", "SSI keys", "width=420,height=430,top=150, left=200, resizable=yes,scrollbars=yes,status=no, location=no")'*/?>>
                            Create a Widget Right Now.</a></h3>

                    </td>

                </tr>
                <tr valign="top">

                    <th scope="row"> </th>

                    <td>
                        <div class="submit" style="margin: 0px;">

                            <input name="save" type="submit" value="Save"/>
                            <input name="reset" type="submit" value="Reset"/>
                        </div>
                    </td>

                </tr>

            </table>

        </form>

    </div>
</div>