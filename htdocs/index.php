<?php

    //定数読み込み
    require_once("../data/define.php");

    /**
    * ヴィトーリアグループ 第一回ハッカソン [4]画像モザイク処理--中-上級向け！　ライブラリなし
    * モザイク処理
    */
    class hackImageLibrary {
        private $point; //計算点数
        private $mosicSize; //モザイクサイズ

        function __construct($point,$mosicSize) {
            $this->point = $point;
            $this->mosicSize = $mosicSize;
        }

        /**
        * フォーマットにあった画像を読み込む
        *
        * @param string $filepath 対象の画像ファイルパス
        * @return GdImage|false 読み込んだ画像
        */
        public function imageCreateFromAny($filepath) {

            $type = getimagesize($filepath);
            switch ($type[2]) {
                case IMAGETYPE_JPEG:
                    $original_image = imagecreatefromjpeg($filepath);
                    break;
                case IMAGETYPE_PNG:
                    $original_image = imagecreatefrompng($filepath);
                    break;
                case IMAGETYPE_GIF:
                    $original_image = imagecreatefromgif($filepath);
                    break;
                case IMAGETYPE_BMP;
                    $original_image = imageCreateFromBmp($filepath);
                    break;
                default:
                    return false;
            }
            return $original_image; 
        }

        /**
        * 画像にモザイク処理をして書き出す。
        *
        * @param string $filepath 対象の画像ファイルパス
        * @return image|false 読み込んだ画像
        */
        public function mosic($inputImg,$outputImg)
        {
            $img = $this->imageCreateFromAny($inputImg);
            if($img !== false) {
                //モザイク処理
                if($this->convertToAvg($img,$this->mosicSize)){
                    //画像の書き出し処理
                    imagejpeg($img, $outputImg, 75);

                    //画像をメモリから開放します
                    imagedestroy($img);
                } else {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        }

        /**
        * 平均化フィルタ
        * @param GdImage $img 対象の画像
        * @param int $size モザイクマス
        * @return boolean
        */
        function convertToAvg($img, $size) {
            $size = (int)$size;
            $sizeX = imagesx($img);
            $sizeY = imagesy($img);
            
            //小さすぎると意味がないので処理しない。
            if($sizeX < 10 && $sizeX < 10) {
              return false;
            }
            
            
            for($i = 0;$i < $sizeX; $i += $size) {  //横
              for($j = 0;$j < $sizeY; $j += $size) {  //縦
                $colors = Array('a' => 0, 'r' => 0, 'g' => 0, 'b' => 0, 't' => 0);

                //5点だけで平均をとる。
                $avg = $this->point;
                $centerSize = round($size/$avg);
                $centerx = array($centerSize*3,$centerSize*3,$centerSize*3,0,$size);//中、上,下,左,右
                $centery = array($centerSize*3,0,$size,$centerSize*3,$centerSize*3);//中、上,下,左,右

                for($k = 0; $k < $avg; ++$k) {
                    for($l = 0; $l < $avg; ++$l) {
                        
                        //画像外は計算しない
                        if($i + $centerx[$k] >= $sizeX || $j + $centerx[$l] >= $sizeY) {
                            continue;
                        }
                        
                        //ピクセルの色のインデックスを取得する
                        $color = imagecolorat($img, $i + $centerx[$k], $j + $centery[$l]);
                        
                        //イメージの色リソースを開放する
                        imagecolordeallocate($img, $color); 

                        //byteからintへ変更して加算する
                        $colors['a'] += ($color >> 24) & 0xFF;
                        $colors['r'] += ($color >> 16) & 0xFF;
                        $colors['g'] += ($color >> 8) & 0xFF;
                        $colors['b'] += $color & 0xFF;
                        ++$colors['t'];
                    }
                }

                // 画像で使用する色を透過度を指定して作成する
                $color = imagecolorallocatealpha(
                    $img,  
                    $colors['r'] / $colors['t'],  
                    $colors['g'] / $colors['t'],  
                    $colors['b'] / $colors['t'],  
                    $colors['a'] / $colors['t']
                );

                // 塗りつぶした矩形を描画する
                imagefilledrectangle($img, $i, $j, ($i + $size - 1), ($j + $size - 1), $color);
              }
            }
            return true;
        }
    }


    //インスタンスの生成
    $hack = new hackImageLibrary(5,30);

    //モザイク処理
    if($hack->mosic(MATERIAL,CONVERT_MATERIAL)){
        echo "モザイク処理成功";
    } else {
        echo "モザイク処理失敗";
    }

?>
