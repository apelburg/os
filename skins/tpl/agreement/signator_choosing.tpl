<style> .main_menu_tbl{ display:none; } </style>
<script>
   function checkRadioBtns(element){
       // реализация на jquery
       // Выбираем активированные input(ы) находящиеся внутри елемента с id = radioBtnsContainer
	   // если возврашен хотябы один -  length != 0, то возвращаем true
       if($('input:checked', document.getElementById('radioBtnsContainer')).length) return true;
	   else{
	       alert('Выберите представителя от нашей компании');
	       return false;
	   }
	   // реализация на javaScript
	   /*  var form = element.form;
	   var inputs = form.getElementsByTagName('input');
	   var len = inputs.length;
	   for(var i =0 ;i<len;i++){
	       if(inputs[i].type=='radio' && inputs[i].checked == true) return true;	
	   }
	   alert('Выберите представителя от нашей компании');
	   return false;	*/
   }
</script>
<div class="agreement_setting_window" style="margin-top:150px;">
    <div style="width:650px;padding:30px 30px 70px 30px;margin:auto;border:1px solid #CCCCCC;box-shadow: 0 0 8px -1px #555555;-moz-box-shadow: 0 0 8px -1px #555555;-webkit-box-shadow: 0 0 8px -1px #555555;">
        <form method="GET">
        <!-- hidden -->
        <input type="hidden" name="query_num" value="<?php echo $_GET['query_num']; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
        <input type="hidden" name="address" value="<?php echo $_GET['address']; ?>">
        <input type="hidden" name="prepayment" value="<?php echo $_GET['prepayment']; ?>">
        <input type="hidden" name="dateDataObj" value="<?php echo htmlspecialchars($_GET['dateDataObj']); ?>">
        <input type="hidden" name="section" value="<?php echo $section; ?>">
        <?php if(isset($_GET['agreement_type'])) echo '<input type="hidden" name="agreement_type" value="'.$_GET['agreement_type'].'">'; ?>
        <?php if(isset($_GET['date'])) echo '<input type="hidden" name="date" value="'.$_GET['date'].'">'; ?>
        <?php if(isset($_GET['requisit_id'])) echo '<input type="hidden" name="requisit_id" value="'.$_GET['requisit_id'].'">'; ?>
        <?php if(isset($_GET['our_firm_id'])) echo '<input type="hidden" name="our_firm_id" value="'.$_GET['our_firm_id'].'">'; ?>
        <?php if(isset($_GET['agreement_id'])) echo '<input type="hidden" name="agreement_id" value="'.$_GET['agreement_id'].'">'; ?>
        <?php if(isset($_GET['agreement_exists'])) echo '<input type="hidden" name="agreement_exists" value="'.$_GET['agreement_exists'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_num'])) echo '<input type="hidden" name="existent_agreement_num" value="'.$_GET['existent_agreement_num'].'">'; ?>
        <?php if(isset($_GET['existent_client_agreement_num'])) echo '<input type="hidden" name="existent_client_agreement_num" value="'.$_GET['existent_client_agreement_num'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_date'])) echo '<input type="hidden" name="existent_agreement_date" value="'.$_GET['existent_agreement_date'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_expire_date'])) echo '<input type="hidden" name="existent_agreement_expire_date" value="'.$_GET['existent_agreement_expire_date'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_client_agreement'])) echo '<input type="hidden" name="existent_agreement_client_agreement" value="'.$_GET['existent_agreement_client_agreement'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_spec_num'])) echo '<input type="hidden" name="existent_agreement_spec_num" value="'.$_GET['existent_agreement_spec_num'].'">'; ?>
        
    
        <div class="cap">Кто директор</div>
        <hr /><br>
        <!-- -->
        <div id="radioBtnsContainer"><!-- dont delete the div - its important for jquery implementation -->
        <?php  echo implode('<br><br>',$top_managers); ?>
        </div>
        <!-- -->
        <div style="text-align:right;margin-top:10px;"><input type="submit" class="button" value="Далее" onclick="return checkRadioBtns(this);"></div>
        </form>
    </div>
</div>