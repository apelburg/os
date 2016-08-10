<!-- <?php echo __FILE__; ?> -- START-->
	<div id="articulusImages" 
        <?php 
            if($this->position['show_img'] == 0){
                echo ' style="width: 0px;display:none;"';
                echo ' class="hidden"';
                echo ' data-width="277px"';
            }
        ?>
                >
	    <?php 
                

	    	$alt = $this->position['art'];
            $h1  = $this->position['name'];
	    ?>
        <div id="articulusImagesPrevBigImg">
            <?php echo $images_data['previews_block']; ?>
        </div>
	    <div id="articulusImagesBigImg">

            <div class="showImagegallery"></div>
	            <img id='image_add' onclick="new galleryWindow(<?=(int)$_GET['id']?>)" src='<?=$images_data['main_img_src'];?>' itemprop="image"  alt='
		        <?php echo $alt; ?>' title="Открыть галерею" style='max-width: 277px;
		max-height: 300px;'>
		    </div>
        <div>
        <?=$this->color_variants_block;?>

            <!-- загрузка изображения на сервер -->
            <div id="status_r2" style="width:90%; display:none; margin-bottom:10px; margin-top:15px; background-color:#FF9091; color:#fff; text-align:center"></div>
            <div id="upload_more_images" style="width:100%; margin:15px 0; display:none">

   		</div>
   		<!--// загрузка изображения на сервер -->               
        </div>
    </div>
<!-- <?php echo __FILE__; ?> -- END-->