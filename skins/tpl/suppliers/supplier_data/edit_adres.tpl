<table id="edit-client-adres">
    <tr>
        <td>Адрес</td>
        <td><span class="type_adress checked" data-type="office">офисa</span><span data-type="delivery" class="type_adress">доставки</span></td>
    </tr>
    <tr>
        <td>Город</td>
        <td>
            <table>
                <tr>
                    <td><span class="city_fast" data-city="Санкт-Петербург">СПб</span><span class="city_fast"  data-city="Москва">МСК</span></td>
                    <td><input type="text" name="city" value="<?php echo $arr_adres['city']; ?>"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="table_2">
                <tr>
                    <td><span id="type-msk">Дом</span></td>
                    <td><input type="text" name="house_number" value="<?php echo $arr_adres['house_number']; ?>"></td>
                    <td><span id="type-msk">Корпус</span></td>
                    <td><input type="text" name="korpus" value="<?php echo $arr_adres['korpus']; ?>"></td>
                    <td><span id="type-msk">Офис</span></td>
                    <td><input type="text" name="office" value="<?php echo $arr_adres['office']; ?>"></td>
                    <td><span id="type-msk">Литера</span></td>
                    <td><input type="text" name="liter" value="<?php echo $arr_adres['liter']; ?>"></td>
                    <td><span id="type-msk">Строение</span></td>
                    <td><input type="text" name="bilding" value="<?php echo $arr_adres['bilding']; ?>"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>Улица</td>
        <td><input type="text" name="street" value="<?php echo $street; ?>"></td>
    </tr>
    <tr>
        <td>Индекс</td>
        <td><input type="text" name="postal_code" value="<?php echo $postal_code; ?>"></td>
    </tr>
    <tr>
        <td>Прим.</td>

        <td><TEXTAREA name="note"><?php echo $note; ?></TEXTAREA></td>
    </tr>
</table>
<input type="hidden" name="adress_type" value="office">