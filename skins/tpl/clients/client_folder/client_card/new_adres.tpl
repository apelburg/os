<table id="edit-client-adres">
    <tr>
        <td>Адрес</td>
        <td><span class="type_adress  checked" data-type="office">офисa</span><span data-type="delivery" class="type_adress">доставки</span></td>
    </tr>
    <tr>
        <td>Город</td>
        <td>
            <table>
                <tr>
                    <td><span class="city_fast" data-city="Санкт-Петербург">СПб</span><span class="city_fast"  data-city="Москва">МСК</span></td>
                    <td><input type="text" name="city" value=""></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="table_2">
                <tr>
                    <td><span id="type-msk">Дом</span></td>
                    <td><input type="text" name="house_number" value=""></td>
                    <td><span id="type-msk">Корпус</span></td>
                    <td><input type="text" name="korpus" value=""></td>
                    <td><span id="type-msk">Офис</span></td>
                    <td><input type="text" name="office" value=""></td>
                    <td><span id="type-msk">Литера</span></td>
                    <td><input type="text" name="liter" value=""></td>
                    <td><span id="type-msk">Строение</span></td>
                    <td><input type="text" name="bilding" value=""></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>Улица</td>
        <td><input type="text" name="street" value=""></td>
    </tr>
    <tr>
        <td>Индекс</td>
        <td><input type="text" name="postal_code" value=""></td>
    </tr>
    <tr>
        <td>Прим.</td>

        <td><textarea name="note"></textarea></td>
    </tr>
</table>
<input type="hidden" name="adress_type" value="office">