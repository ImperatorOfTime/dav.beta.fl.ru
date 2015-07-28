<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


/**
 * ����� ������ � CAPTCHA
 *
 */
class captcha
{

    /**
     * �����������
     *
     * @var object
     */
    private $img;

    /**
     * ������ ��������
     *
     * @var integer
     */
	public $width = 130;

    /**
     * ������ ��������
     *
     * @var integer
     */
	public $height = 60;

    /**
     * ����������� ������ ������
     *
     * @var integer
     */
	public $font_size_min = 25;

    /**
     * ������������ ������ ������
     *
     * @var integer
     */
	public $font_size_max = 25;

    /**
     * ������, ������� ������������ ��� ������
     *
     * @var array
     */
    private $fonts = array();

    /**
     * ������������ �������� ������� �� ��������� 
     *
     * @var integer
     */
	public $y_offset = 15;

    /**
     * ������������ ���� �������� �������
     *
     * @var integer
     */
	public $angle = 2;

    /**
     * ���� ��� ��������� ����� �� ��������
     *
     * @var string
     */
	public $font_path;

    /**
     * ������� ������������ ��� ��������� ������
     *
     * @var string
     */
	public $characters = "3459ACEGHKMNPSUVXY";
    
    /**
     * ������� ������������ ��� ��������� ������ ��� ������ shui.ttf
     * ������ �������: G, S, U, V
     * @var string
     */
	public $characters_shui = "3459ACEHKMNPXY";
    /**
     * ������� ������������ ��� ��������� ������ ��� ������ bite.ttf
     * ������ �������: 9
     * @var string
     */
	public $characters_bite = "345ACEGHKMNPSUVXY";
    /**
     * ������� ������������ ��� ��������� ������ ��� ������ aman.ttf
     * ������ �������: U, V
     * @var string
     */
	public $characters_aman = "3459ACEGHKMNPSXY";

    /**
     * ���-�� �������� � ������
     *
     * @var integer
     */
	public $chars_count = 5;



    /**
     * ���� � ������ ��� �������� ����� CAPTCHA
     *
     * @var string
     */
    public $CAPTCHANUM = 'image_number';

    /**
     * ���� ����
     *
     * @var integer
     */
    public $bgcolor;

    /**
     * ���� ������
     *
     * @var integer
     */
    public $fgcolor;

    /**
     * ��������� �����
     *
     * @var array
     */
    public $colors = array(
                            0 => array(255,255,255),
                            1 => array(10,10,10),
                            2 => array(10,255,10)
                          );


    /**
     * ����������� ������
     *
     * @param string $num ������� ��� ����� ������ �� �������� �������� CAPTCHA
     * @param integer $bgcolor ����� ����� ���� (0 - �����, 1, 2 )
     * @param integer $fgcolor ����� ����� ������ (0 - �����, 1, 2)
     */
	public function __construct($num='', $bgcolor=0, $fgcolor=1) {
        if(!array_key_exists($bgcolor, $this->colors)) {
            $this->bgcolor = 0;
        } else {
            $this->bgcolor = $bgcolor;
        }
        if(!array_key_exists($fgcolor, $this->colors)) {
            $this->fgcolor = 1;
        } else {
            $this->fgcolor = $fgcolor;
        }
        $this->font_path = $_SERVER['DOCUMENT_ROOT'].'/fonts';

        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'shui.ttf';
        $this->fonts[] = 'bite.ttf';
        $this->fonts[] = 'jump.ttf';
        $this->fonts[] = 'aman.ttf';

        //$this->fonts[] = 'chr.ttf';
        //$this->fonts[] = 'bite.ttf';
        //$this->fonts[] = 'jump.ttf';
        //$this->fonts[] = 'shui.ttf';


        //$this->fonts[] = 'aman.ttf';
        //$this->fonts[] = 'sf.ttf';
        //$this->fonts[] = 'wishful.ttf';
        /*
		if (is_dir($this->font_path)) {
			if ($dh = opendir($this->font_path)) {
				while (($file = readdir($dh)) !== FALSE) {
					if (preg_match("/.ttf$/", $file)) $this->fonts[] = $file;
				}
			}
        }
        closedir($dh);
        */
        if($num) { $this->CAPTCHANUM = $this->CAPTCHANUM.$num; }
	}

    /**
    * ��������� �������
    *
    */
	public function multi_wave($type = 1) {
		
		// ��� ��������
		$width = $this->width;
		$height = $this->height;
		$img =& $this->img;
	
		$center = ($this->width - 10) / 2;

		//$fg = mt_rand(0, 100);
		//$bg = mt_rand(250, 255);
		
		//$foreground_color = array($fg, $fg, $fg);
		$foreground_color = array($this->colors[$this->fgcolor][0], $this->colors[$this->fgcolor][1], $this->colors[$this->fgcolor][2]);
		//$background_color = array($bg, $bg, $bg);
		$background_color = array($this->colors[$this->bgcolor][0], $this->colors[$this->bgcolor][1], $this->colors[$this->bgcolor][2]);


		$img2 = imagecreatetruecolor($this->width, $this->height);
		$foreground = imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$background = imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
		imagefilledrectangle($img2, 0, 0, $this->width - 1, $this->height - 1, $background);		

            // periods
            $rand1=mt_rand(750000,1200000)/11000000;
            $rand2=mt_rand(750000,1200000)/11000000;
            $rand3=mt_rand(750000,1200000)/11000000;
            $rand4=mt_rand(750000,1200000)/11000000;
            // phases
            $rand5=mt_rand(0,31415926)/13000000;
            $rand6=mt_rand(0,31415926)/13000000;
            $rand7=mt_rand(0,31415926)/13000000;
            $rand8=mt_rand(0,31415926)/13000000;
        if ($type === 1) {
            // amplitudes
            $rand9=mt_rand(330,420)/110;
            $rand10=mt_rand(330,450)/110;
        } elseif ($type === 2) {
            // amplitudes
            $rand9=mt_rand(250,330)/110;
            $rand10=mt_rand(250,330)/110;
        } elseif ($type === 3) {
            // amplitudes
            $rand9=mt_rand(220,300)/110;
            $rand10=mt_rand(220,300)/110;
        }

		//wave distortion
		for($x=0;$x<$width;$x++){
			for($y=0;$y<$height;$y++){
				$sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
				$sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

				if($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1){
					continue;
				}else{
					$color=imagecolorat($img, $sx, $sy) & 0xFF;
					$color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
					$color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
					$color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
				}

				if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255){
					continue;
				}else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0){
					$newred=$foreground_color[0];
					$newgreen=$foreground_color[1];
					$newblue=$foreground_color[2];
				}else{
					$frsx=$sx-floor($sx);
					$frsy=$sy-floor($sy);
					$frsx1=1-$frsx;
					$frsy1=1-$frsy;

					$newcolor=(
						$color*$frsx1*$frsy1+
						$color_x*$frsx*$frsy1+
						$color_y*$frsx1*$frsy+
						$color_xy*$frsx*$frsy);

					if($newcolor>255) $newcolor=255;
					$newcolor=$newcolor/255;
					$newcolor0=1-$newcolor;

					$newred=$newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
					$newgreen=$newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
					$newblue=$newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
				}

				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
			}
		}
		
		// save
		$this->img = $img2;
	}

    /**
     * ��������� ��������� ����� � ������
     * ���������� ������� ������ ����� � ������� �������, ����������� �������� � ������
     *
     * return void
     */
    function setNumber()
    {
		$count = 0;
		$result = '';
        mt_srand();
		while ($count++ < 10) {
            $width = 10;
			//echo $count;
			for ($i=0; $i<$this->chars_count; $i++) {
                // ����� �������� � ����������� �� ������
                if ($i === 1) {
                    $characters = $this->characters_shui;
                } elseif ($i === 2) {
                    $characters = $this->characters_bite;
                } elseif ($i === 4) {
                    $characters = $this->characters_aman;
                } else {
                    $characters = $this->characters;
                }
                
				$char = $characters{ mt_rand(0, strlen($characters)-1) };
                
                // ����� �� ���� ����� ���� ���������� �������
				if ( $i > 0 && $char == $result{$i-1} )	{
                    $i--;
                } else {
                    // ����� ������� �� �������� �� ������� �����
                    $font = $this->font_path . '/' . $this->fonts[$i];
                    $char_info = imagettfbbox($this->font_size_min, 0, $font, $char);
                    $charWidth = $char_info[2] - $char_info[0] + 2;
                    if ($width + $charWidth < $this->width) {
                        $result .= $char;
                        $width += $charWidth;
                    }
                }
			}
			if(preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/i', $result)) $result = ''; else break;
		}
        $_SESSION[$this->CAPTCHANUM] = $result;
    }



    /**
     * ��������� ����� ������������ ����� �����
     *
     * return string                     ����� �����
     */
    function getNumber()
    {
        return $_SESSION[$this->CAPTCHANUM];
    }



    /**
     * �������� ����� ����� �� ������������
     *
     * @param integer $num               ����� �����
     *
     * return boolean                    1 � ������ ������������, 0 � ������ �������
     */
    function checkNumber($num)
    {
        return ($this->getNumber() && strtolower($this->getNumber())==strtolower($num));
    }

    /**
    * ��������� ����� � �����������
    *
    * @param    string  $code   �����
    */
	public function draw_code($code) {
  		$this->img = imagecreatetruecolor($this->width, $this->height);
		$white = imagecolorallocate($this->img, 255, 255, 255);
		$font_color = imagecolorallocate($this->img, 50, 50, 50);
		imagefilledrectangle($this->img, 0, 0, $this->width - 1, $this->height - 1, $white);

		$x = 5;
		
		for ($i=0; $i<strlen($code); $i++) {
			$font_size = mt_rand($this->font_size_min, $this->font_size_max);
			//$font = $this->font_path . '/' . $this->fonts[ mt_rand(0, count($this->fonts)-1) ];
            $font = $this->font_path . '/' . $this->fonts[ $i ];
			$char_info = imagettfbbox($font_size, 0, $font, substr($code,$i,1));
			$char_line = abs($char_info[7] - $char_info[1]);	
			$char_line = $font_size + $this->y_offset;
            $char = substr($code,$i,1);
			imagettftext($this->img, $font_size, mt_rand(-$this->angle, $this->angle), $x, mt_rand($char_line - 5, $char_line + 5), $font_color, $font, $char);
            // ��������� ������ �� ���������� �������� � ��������������
            //$delta = ($i == 4 ? 0 : 3);
			$x += $char_info[2] - $char_info[0] + 2;
		}
	}

    /**
    * ������ ����� �� ����
    *
    */
	public function lines() {
        for ($i=0; $i<5; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 255), mt_rand(0, 200), mt_rand(0, 255));
            $a1 = 15;
            $a2 = mt_rand(10, 50);
            $a3 = 115;
            $a4 = mt_rand(10, 50);
            imageline($this->img, $a1, $a2, $a3, $a4, $color);
            //imageline($this->img, mt_rand(0, 20), mt_rand(1, 50), mt_rand(150, 180), mt_rand(1, 50), $color);
        }
	}

    /**
     * ��������� ����������� �����
     *
     * return image                      ������ �����������
     */
    function getImage()
    {
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        $this->lines();

		$this->multi_wave();
        //imagefilter($this->img, IMG_FILTER_SMOOTH, 10);

        return $this->img;
    }
    
    function getImage1()
    {
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

		$this->multi_wave();

        return $this->img;
    }
    function getImage2()
    {
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

		$this->multi_wave(2);

        return $this->img;
    }
    function getImage3()
    {
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

		$this->lines();

        return $this->img;
    }
    function getImage4()
    {
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        return $this->img;
    }
    function getImage5()
    {
        $this->fonts = array();
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'sf.ttf';
        $this->fonts[] = 'tahoma.ttf';
        $this->fonts[] = 'wishful.ttf';
        $this->fonts[] = 'bookantigua.ttf';
        
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        return $this->img;
    }
    function getImage6()
    {
        $this->fonts = array();
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'sf.ttf';
        $this->fonts[] = 'tahoma.ttf';
        $this->fonts[] = 'wishful.ttf';
        $this->fonts[] = 'bookantigua.ttf';
        
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        $this->multi_wave();
        
        return $this->img;
    }
    function getImage7()
    {
        $this->fonts = array();
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'sf.ttf';
        $this->fonts[] = 'tahoma.ttf';
        $this->fonts[] = 'wishful.ttf';
        $this->fonts[] = 'bookantigua.ttf';
        
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        $this->multi_wave(2);
        
        return $this->img;
    }
    function getImage8()
    {
        $this->fonts = array();
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        $this->multi_wave();
        
        return $this->img;
    }
    function getImage9()
    {
        $this->fonts = array();
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        $this->multi_wave(2);
        
        return $this->img;
    }
    function getImage10()
    {
        $this->fonts = array();
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        $this->multi_wave(3);
        
        return $this->img;
    }
    function getImage11()
    {
        $this->fonts = array();
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        $this->fonts[] = 'aman.ttf';
        $this->fonts[] = 'chr.ttf';
        
        $number = $this->getNumber();
        if(!$number) {
            $this->setnumber();
            $number = $this->getNumber();
        }
		$this->draw_code($number);

        return $this->img;
    }
}
