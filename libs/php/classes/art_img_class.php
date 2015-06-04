<?php 

     class Art_Img{
	    public $big;
		public $small;
    
		function __construct($art){
			global $mysqli;
		    
			//$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `art` =$art  GROUP BY size ORDER BY id";
			//$result = $mysqli->query($query)or die($mysqli->error);
			$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `art` =?  GROUP BY size ORDER BY id";

			$stmt = $mysqli->prepare($query) or die($mysqli->error);
			$stmt->bind_param('s',$art) or die($mysqli->error);
			$stmt->execute() or die($mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			
			if($result->num_rows>0){
			    while($row=$result->fetch_assoc()){
				    if($row['size']=='big') $this->big = ($row['name'] !='')? $row['name']:'no_image.jpg';
					if($row['size']=='small') $this->small = ($row['name'] !='')? $row['name']:'no_image.jpg';
				}
			}
			else $this->big = $this->small = 'no_image.jpg';
		} 
    } 

?>