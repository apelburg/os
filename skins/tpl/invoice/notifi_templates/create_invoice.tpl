<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="width:100%; margin: 0;
            padding: 0;
            font-family: Helvetica, Verdana, Arial, sans-serif;
            background: #f1f1f1;">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempo-Responsive Email Template</title>

</head>
<body style=" margin: 0;color:#393939;
            padding: 0;
            width: 100%;
            font-family: Helvetica, Verdana, Arial, sans-serif;
            background: #f1f1f1;">
    <div id="main" style="padding: 25px;
            background: #f1f1f1;
            width:calc(100% - 50px);
            min-width: 580px;">

        <div id="submain" style="border: 2px solid rgba(146, 183, 62, 0.5);
            background: #fff;    min-width: 639px;
            padding: 25px;
            max-width: 854px;
            margin: 0 auto;
            left:50%">
            <table id="head_tbl" style="font-size: 16px;">
                <tr>
                    <td id="logotip" style="width:200px;padding: 0 5px;background-color: #fff; background-image: url('http://www.apelburg.ru/os/skins/images/img_design/mess_tpl/mess_tpl_logo.png'); background-repeat: no-repeat;
                    width: 216px;
                    min-width: 216px;
                    height: 50px;
                    background-size: 200px;
                    background-position: 0;"><a style="width: 100%;
            height: 100%;
            float: left;" href="http://www.apelburg.ru/"></a></td>
                    <td  id="head" style="background: url('http://www.apelburg.ru/os/skins/images/img_design/mess_tpl/mess_tpl_line_bg.png') repeat;
                    background-size: 2pt 2pt;
                    background-repeat: repeat-x;
                    background-position: 0 60%;">
                        <div id="call_as" style="color: #000;   min-width: 230px;
            padding-left: 25px;
            background-image: url('http://www.apelburg.ru/os/skins/images/img_design/mess_tpl/mess_tpl_call_as.png');
                        background-repeat: no-repeat;
                        background-size: 25px;
                        width: 100%;
                        padding-right: 160px;
                        padding-bottom: 25px;
                        position: relative;">Бесплатный звонок по всей России <b>8-800-250-70-12</b><div id="small_logo" style="color: #000;
            padding-left: 25px;
            background-image: url('http://www.apelburg.ru/os/skins/images/img_design/mess_tpl/mess_tpl_small_logo.png');
                        background-repeat: no-repeat;
                        background-size: 125px;
                        width: 150px;
                        height: 57px;
                        background-position: 1 1;
                        float: right;
                        position: absolute;
                        right: 0;
                        text-align: center;
                        top: 3px;"><a style="width: 100%;
            height: 100%;
            float: left;" href="http://www.apelburg.ru/"></a></div></div>
                    </td>
                </tr>
            </table>
            <div id="body" style="padding: 0 6px;">
                <?php
                if($userName != ''){
                    $userName = '&nbsp;&nbsp;'.$userName;
                }
                ?>
                <div id="hi" style="padding: 10px 0"><b>Здравствуйте<?=$userName?>!</b></div>
                <div id="message" style="padding: 25px 0;">

                    <div><?=$message?>
                    </div>



                <?php
                    if(isset($href) && $href != '' && $href!='#'){
                        ?><div id="button_div" style="padding: 45px 0 25px 0;"><a style="font-family: Arial;
                            font-weight: normal;
                            font-size: 16px;
                            color: #ffffff;
                            text-decoration: none;
                            white-space: nowrap;
                            border-radius: 3px;
                            display: inline-block;
                            text-align: center;
                            margin-top: 0px;
                            margin-right: 0px;
                            margin-bottom: 0px;
                            margin-left: 0px;
                            padding-top: 11px;
                            padding-right: 14px;
                            padding-bottom: 9px;
                            padding-left: 12px;
                            background-color: #95B45A;" href="<?=$href?>">Узнать подробности </a></div><?php
                    }
                ?>

                    <div id="podpis" style="padding-top: 25px;">С уважением,<br>
                        Команда Апельбург.</div>
                </div>
            </div>

            <div id="footer" style="background: url('http://www.apelburg.ru/os/skins/images/img_design/mess_tpl/mess_tpl_line_bg.png') repeat;
            background-size: 2pt 2pt;
            background-repeat: repeat-x;
            background-position: 0 70%;margin-top: 25px;
            padding: 10px 0 0 0;
            text-align: center;">
                <span style="background: #fff;
            padding: 0 10px;"><b style="font-size: 14px;">Санкт-Петербургб ул. Чугунная, 14</b> </span>
            </div>
        </div>
        <div id="subfooter" style="color: grey;
            padding: 10px 16px;
            max-width: 800px;
            margin: 0 auto;
            left: 50%;
            font-size: 12px;">
            Не отвечайте на это письмо. Если у Вас возникли вопросы, обратитесь в <a href="mailto:admin@apelburg.ru?subject=Вопрос из письма ОС">службу поддержки</a> .<br>
            Это обязательное уведомление о важных изменениях в Вашем онлайн- сервисе.
        </div>
    </div>
</body>
</html>