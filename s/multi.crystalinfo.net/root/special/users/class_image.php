<?
class image_class {
var $image_location = "";
var $image_save_location = "";
var $standart_width=800;
var $standart_height=600;
var $quality=100;
var $string_to_write;
function modify_image()
{
  if ($this->image_location==""){return;}
  $im_size = GetImageSize($this->image_location);
  $current_width = $im_size[0];
  $current_height = $im_size[1];
  if (!($current_width <= $this->standart_width && $current_height <= $this->standart_height)){
    if ($current_width/$current_height > $this->standart_width / $this->standart_height){
      $modified_width=$this->standart_width;
      $modified_height=($this->standart_width/$current_width)*$current_height;
      }
    else{
      $modified_height=$this->standart_height;
      $modified_width=($this->standart_height/$current_height)*$current_width;
      }
  }
  else{
    $modified_width=$current_width;$modified_height=$current_height;
  }
  $tempImage = ImageCreateFromJPEG($this->image_location);
  $resultImage = imageCreate( $modified_width, $modified_height );

  if (is_array($this->string_to_write)){
    $font_color_temp = ImageColorAllocate( $resultImage, 0,0,0);
    $font_color = ImageColorAllocate( $resultImage, 255,255,255);
  }
  ImageCopyResized($resultImage,$tempImage,0,0,0,0,$modified_width,$modified_height,$current_width,$current_height);
  imagedestroy($tempImage);
  if (is_array($this->string_to_write)){
    $start_position=$modified_height-(count($this->string_to_write)*20+20);
    foreach ($this->string_to_write as $satir){
      imagestring( $resultImage, 4, 15, $start_position, $satir ,$font_color);
      $start_position += 20;
    }
  }
//echo  $this->image_save_location;
    
  if ($this->image_save_location != ""){
    $final_img=imagejPEG($resultImage,$this->image_save_location,$this->quality);
        
//    echo ("SAVED SUCCESSFULLY");
  }
  else{
    header("Content-type: image/jpeg");
    $final_img=imagejPEG($resultImage,'',$this->quality);
    imagedestroy($resultImage);
  }
    return $final_img;

}
}
?>
