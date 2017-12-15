<?php
/**
 *  BarcodeStream
 */

require_once "Image/Barcode.php";

class BarcodeStream {
   var $position;
   var $barcode;

   function stream_open($path, $mode, $options, &$opened_path)
   {
       $url = parse_url($path);

       ob_start();
       Image_Barcode::draw(substr($url["path"],1), $url["host"]);
       $this->barcode = ob_get_contents();
       ob_clean();

       $this->position = 0;
       return true;
   }
   function stream_read($count)
   {
       $ret = substr($this->barcode, $this->position, $count);
       $this->position += strlen($ret);
       return $ret;
   }

   function stream_write($data)
   {
       $left = substr($this->barcode, 0, $this->position);
       $right = substr($this->barcode, $this->position + strlen($data));
       $this->barcode = $left . $data . $right;
       $this->position += strlen($data);
       return strlen($data);
   }

   function stream_tell()
   {
       return $this->position;
   }

   function stream_eof()
   {
       return $this->position >= strlen($this->barcode);
   }

   function stream_seek($offset, $whence)
   {
       switch($whence) {
           case SEEK_SET:
               if ($offset < strlen($this->barcode) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
               } else {
                     return false;
               }
               break;

           case SEEK_CUR:
               if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
               } else {
                     return false;
               }
               break;

           case SEEK_END:
               if (strlen($this->barcode) + $offset >= 0) {
                     $this->position = strlen($this->barcode) + $offset;
                     return true;
               } else {
                     return false;
               }
               break;

           default:
               return false;
       }
   }
}

?>
