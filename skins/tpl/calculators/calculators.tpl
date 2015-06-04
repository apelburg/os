<table class="calculator_body_tbl">
  <tr>
    <td colspan="2" align="right">
       <div style="position:absolute;left:50px;cursor:pointer;" onclick="aplCalculators.process_data();">посчитать</div>
       <div style="position:absolute;left:150px;cursor:pointer;" onclick="aplCalculators.pull_base_price();">посчитать2</div>
       <div class="close_btn" onclick="aplCalculators.close_box();">&#215;</div>
    </td>
  </tr>
  <tr>
    <td width="150">
       <div class="menu_block">
          <div>Шелкография</div>
             <div class="sub" onclick="aplCalculators.set_type('shelk_textil');">текстиль</div>
             <div class="sub" onclick="aplCalculators.set_type('shelk_pvd');">пакеты ПВД</div>
             <div class="sub" onclick="aplCalculators.set_type('shelk_plast_papki');">папки пласт.</div>
             <div class="sub" onclick="aplCalculators.set_type('shelk_converti');">конверты</div>
             <div class="sub" onclick="aplCalculators.set_type('shelk_bum_paketi');">пакеты бум.</div>
             <div class="sub" onclick="aplCalculators.set_type('shelk_vizitki');">визитки</div>
          <div onclick="aplCalculators.set_type('tampo');">Тампопечать</div>
          
          <div>Лазер</div>
             <div class="sub" onclick="aplCalculators.set_type('laser_suv');">сувениры</div>
             <div class="sub" onclick="aplCalculators.set_type('laser_shild');">шильды</div>
          <div onclick="aplCalculators.set_type('tisnenie');">Тиснение</div>
          <div onclick="aplCalculators.set_type('decol');">Деколь</div>
          <div onclick="aplCalculators.set_type('vishivka');">Вышивка</div>
          <div onclick="aplCalculators.set_type('drugoe');">Другое</div>
       </div>
    </td>
    <td>
        <div id="tirag_and_itog_part">
            <table id="tirag_and_itog_part_tbl" class="tirag_and_itog_part_tbl">
               <tr>
                  <td>
                     <div>Тираж</div>
                  </td>
                  <td>
                     <div><input type="text" name="col" onkeyup="aplCalculators.process_data();" value="100"/></div>
                  </td>
                  <td>&nbsp;
                     
                  </td>
                  <td>
                     <div>Итого</div>
                  </td>
                  <td>
                     <div><span name="common_price_per_item_display">0000,00</span> руб</div>
                  </td>
                  <td>
                     <div><span name="common_itog_display">000000,00</span> руб</div>
                  </td>
              </tr>
            </table>
        </div>
        <div id="incoming_data_tpls_container">
          <div name="shelk_textil" class="data_input_part">
              <div name="incoming_data_box" style="border-bottom:1px solid #BBB;">
                  <div onclick="aplCalculators.copy_incoming_data_box(this);" style="float:left;cursor:pointer;margin:5px 2px;">копировать</div>
                  <div onclick="aplCalculators.add_incoming_data_box();" style="float:left;cursor:pointer;margin:5px 2px;">добавить</div>
                  <div style="float:right;margin:5px 20px;"><span name="itog_display">0000,00</span> руб</div>
                  <div style="float:right;margin:5px 20px;"><span name="price_per_item_display">0000,00</span> руб</div>
                  <div class="clear_div"></div>
                  <input type="text" name="print_col" vtype="color_num" onkeyup="aplCalculators.process_data();" value="3">количество цветов<br>
                  <input type="text" name="color" vtype="summ_coeff" onkeyup="aplCalculators.process_data();" value="1.0">color<br>
                  <input type="text" name="print_type" vtype="summ_coeff" onkeyup="aplCalculators.process_data();" value="1.0">print_type<br>
                  <input type="text" name="material" vtype="summ_coeff" onkeyup="aplCalculators.process_data();" value="1.0">material<br>
                  
              </div>
              <!--<div name="incoming_data_box">
                  <input type="text" name="print_col" vtype="color_num" onkeyup="aplCalculators.process_data();" value="5">количество цветов<br>
                  <input type="text" name="color" vtype="summ_coeff" onkeyup="aplCalculators.process_data();" value="1.2">color<br>
                  <input type="text" name="print_type" vtype="summ_coeff" onkeyup="aplCalculators.process_data();" value="1.1">print_type<br>
                  <input type="text" name="material" vtype="summ_coeff" onkeyup="aplCalculators.process_data();" value="1.3">material<br>
                  <div><span name="price_per_item_display">0000,00</span> руб</div>
                  <div><span name="itog_display">0000,00</span> руб</div>
              </div>-->
              <table name="base_prices_tbl" style="display:none;">         
                   <tr>
                     <td>Тираж/цвет</td>
                     <td>10</td>
                     <td>30</td>
                     <td>50</td>
                     <td>100</td>
                     <td>300</td>
                     <td>500</td>
                     <td>1000</td>
                     <td>5000</td>
                     <td>10000</td>
                   </tr>
                   <tr>
                     <td>1</td>
                     <td>114.80</td>
                     <td>48.60</td>
                     <td>35.10</td>
                     <td>28.40</td>
                     <td>20.30</td>
                     <td>18.90</td>
                     <td>17.60</td>
                     <td>14.90</td>
                     <td>14.20</td>
                   </tr>
                   <tr>
                     <td>2</td>
                     <td>155.90</td>
                     <td>62.10</td>
                     <td>43.20</td>
                     <td>35.10</td>
                     <td>23.00</td>
                     <td>21.60</td>
                     <td>19.60</td>
                     <td>15.50</td>
                     <td>14.90</td>
                   </tr>
                   <tr>
                     <td>3</td>
                     <td>197.10</td>
                     <td>75.60</td>
                     <td>51.30</td>
                     <td>43.20</td>
                     <td>24.30</td>
                     <td>23.00</td>
                     <td>20.30</td>
                     <td>16.90</td>
                     <td>15.50</td>
                   </tr>
                   <tr>
                     <td>4</td>
                     <td>239.00</td>
                     <td>90.50</td>
                     <td>60.80</td>
                     <td>50.00</td>
                     <td>27.00</td>
                     <td>26.30</td>
                     <td>21.60</td>
                     <td>17.60</td>
                     <td>16.90</td>
                   </tr>
                   <tr>
                     <td>5</td>
                     <td>298.75</td>
                     <td>113.13</td>
                     <td>76.00</td>
                     <td>62.50</td>
                     <td>33.75</td>
                     <td>32.88</td>
                     <td>27.00</td>         
                     <td>0</td>
                     <td>0</td>
                   </tr>
                   <tr>
                     <td>6</td>
                     <td>358.50</td>
                     <td>135.75</td>
                     <td>91.20</td>
                     <td>75.00</td>
                     <td>40.50</td>
                     <td>39.45</td>
                     <td>32.40</td>
                     <td>0</td>
                     <td>0</td>
                   </tr>
                 </table>
          </div>
          <div id="shelk_pvd" class="data_input_part">пакеты ПВД</div>
          <div id="shelk_plast_papki" class="data_input_part">папки пласт.</div>
          <div id="shelk_converti" class="data_input_part">конверты</div>
          <div id="shelk_bum_paketi" class="data_input_part">пакеты бум.</div>
          <div id="shelk_vizitki" class="data_input_part">визитки</div>
          <div id="laser_suv" class="data_input_part">сувениры</div>
          <div id="laser_shild" class="data_input_part">шильды</div>
          <div id="tisnenie" class="data_input_part">Тиснение</div>
          <div id="decol" class="data_input_part">Деколь</div>
          <div id="vishivka" class="data_input_part">Вышивка</div>
          <div id="drugoe" class="data_input_part">Другое</div>
        </div>
    </td>
  </tr>
</table>
