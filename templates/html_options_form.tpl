<div class="wrap">
    <form method="post">ddd
        <!-- Данные из формы получаем методом post -->
        <table class="admin_table">

            <?php foreach ($plugoptions as $value) {

if ($value['type'] == "title") { ?>

<tr valign="top">

<td colspan="2" class="head">
<h3 ><?php echo $value['name']; ?></h3>
</td>
</tr>

<!-- Флажок -->
<?php } elseif ($value['type'] == "checkbox") { ?>

<tr valign="top">

<th scope="row"><?php echo $value['name']; ?>:</th>

<td><? if( get_option($value['id']) ) {
              //используя WP-функцию get_option,
$checked = "checked=\"checked\"";
//считываем состояние флажка из БД
} else { $checked = ""; } ?>

<input type="checkbox" name="<?php echo $value['id']; ?>"
id="<?php echo $value['id']; ?>" value="true"
<?php echo $checked; ?> />

<br />

<?php echo $value['desc'] ; ?>
//выводим описание опции, не из базы
</td>

</tr>


<!-- Текстовое поле ввода -->
<?php } elseif ($value['type'] == "text") { ?>

<tr valign="top">

<th scope="row"><?php echo $value['name']; ?>:</th>

<td><input name="<?php echo $value['id']; ?>"
id="<?php echo $value['id']; ?>"
type="<?php echo $value['type']; ?>"
value="<?php if (get_option( $value['id'] ) != "") {
echo htmlspecialchars(
get_option( $value['id'] ) );
} else { echo $value['std']; } ?>" />

                   <br />

                   <?php echo $value['desc'] ; ?>
               </td>

            </tr>
        <?php
 } elseif ($value['type'] == "select"){?>
13133

    <?php}} ?>
    <!-- Конец цикла считывания данных -->

  </table>
ddwd
  <div class="submit">
    <!-- Кнопки действий -->
    <input name="save" type="submit" value="Сохранить" />
    <input name="reset" type="submit" value="Сбросить" />
  </div>

</form>
</div>