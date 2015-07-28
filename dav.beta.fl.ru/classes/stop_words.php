<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );

/**
 * ������ � ��������������� ������� � ������������ �����������
 *
 * @author Max 'BlackHawk' Yastrembovich
 * @todo ��������� ��� ��� ��������, �� ������ ��� �� ��������. ��� ��� ��������� ������ ����� �� ��������
 */
class stop_words {    
    /**
     * ������ ��������� ����-����� ��� ����������
     */
    const STOP_WORDS = '<span style="font-weight:bold; color: #cc4642;">$1</span>';
    
    /**
     * ������ ��������� ����������� ��������� ��� ����������
     */
    const STOP_REGEX = '<span style="color: #cc4642;">[������: %s]</span>';
    
    /**
     * ������ ��������� ����������� ��������� ��� ���������� ��� HTML
     */
    const STOP_REGEX_PLAIN = '[������: %s]';
    
    /**
     * ��� ��� ������ ������
     */
    const STOP_LINK_CODE   = 'MY_SW_L';
    
    /**
     * ��� ��� ������ ����������� ���������
     */
    const STOP_REGEX_CODE  = 'MY_SW_C';


    /**
     * ���������� �������� $site
     * 
     * @var array
     */
    static $site_allow = array( 'words', 'regex' );
    
    /**
     * ����������� ������������ �������� � ������ ��������������
     * 
     * @var bool
     */
    private $admin_mode = false;
    
    /**
     * ����� ������: 'html', 'plain'
     * 
     * @var string
     */
    private $replace_mode = 'html';
    
    /**
     * ������ ���� ��� ��������� ��������������� ���������� � ������������� ���������
     * 
     * @var array 
     */
    private $stop_words = array();
    
    /**
     * ������ ����������� ��������� � ���������� �������������
     * 
     * @var array 
     */
    private $stop_regex = array(
        '#([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}#i', // email
        '#([a-z�-���-ߨ0-9_-]+\.)*[a-z�-���-ߨ0-9_-]+@[a-z�-���-ߨ0-9_-]+(\.[a-z�-���-ߨ0-9_-]+)*\.(��|rf)#i', // email � ��������� � ������� ��
        '#\+?(?:[1-9](?:(?:[\(\)]*\d){10,12})|0(?:[\(\)]*\d){9})#', // ��������� ��������
        '#\d{3}-\d{2}-\d{2}#U', //'#(?:[ \-\(\)]*\d+){7}#', // ��������� ��������
        '#[GREZUBYCD]+[\d]{12}#i' // �������� �������
    );
    
    /**
     * ������ ����������� ��������� ��� �������� �������������.
     * ����������� ���� ���� ������ �� ��������� �� �������������,
     * ������� ��� ������ ��� ���������� ������� � ������� �� $stop_regex
     * 
     * @var array 
     */
    private $profile_stop_regex = array(
        '#([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}#i', // email
        '#([a-z�-���-ߨ0-9_-]+\.)*[a-z�-���-ߨ0-9_-]+@[a-z�-���-ߨ0-9_-]+(\.[a-z�-���-ߨ0-9_-]+)*\.(��|rf)#i', // email � ��������� � ������� ��
        '#\b[GREZUBYCD]+[\d]{12}\b#i' // �������� �������
    );
    
    /**
     * ������ ����������� ��������� ��� ������� ������� suspicious_contacts ����������� ������� ������ �� ������� ���������
     * ����� �������������� - ����������� ��� ������
     * 
     * @var array
     */
    private $suspect_stop_regex = array(
        '~(?:[a-z0-9_-]+\.)*[a-z0-9_-]+\s*(@|#)\s*[a-z0-9_-]+(?:\.[a-z0-9_-]+)*\s*\.\s*[a-z]{2,4}~i', 
        '~([a-z�-���-ߨ0-9_-]+\.)*[a-z�-���-ߨ0-9_-]+\s*(@|#)\s*[a-z�-���-ߨ0-9_-]+(\.[a-z�-���-ߨ0-9_-]+)*\s*\.\s*(��|rf)~i', 
        '#\+?(?:[\(\)\s-]*\d){5,}#', 
        '#\d{3}-\d{2}-\d{2}#', 
        '#(?i)[GREZUBYCD]+[\d]{12}#', 
        '#\d{16}#', 
        '#\d{4}\s+\d{4}\s+\d{4}\s+\d{4}#', 
        '#4100\d{10}#'
    );
    
    /**
     * ��������� ������� �������������� ����. ��� �������.
     * 
     * @var string
     */
    private static $stop_words_table = 'user_content_stop_words';
    
    /**
     * ��������� ������� �������������� ����. ��� - �����
     * 
     * @var int
     */
    private static $stop_words_words = 1;
    
    /**
     * ��������� ������� �������������� ����. ��� - ���������� ���������
     * 
     * @var int
     */
    private static $stop_words_regex = 2;
    
    /**
     * ����� �������� ���� ����. ������� ������ ���������
     * 
     * @var string 
     */
    private $calculate_regex_prefix  = 'suspect';
    
    /**
     * ����� �������� ���� ����. ����� �� ������� ���� �����
     * 
     * @var bool 
     */
    private $calculate_words         = true;
    
    /**
     * ������ ������ ��� ������ � �������������� � _linkReplace � _linkRestore
     * 
     * @var array 
     */
    private $links                   = array();
    
    /**
     * ������ �������� ��� ������ � �������������� � _callbackReplace � _restore
     * 
     * @var array 
     */
    private $censored                = array();
    
    /**
     * ����������� ������
     * 
     * @param bool $admin_mode ����������� ������������ �������� � ������ ��������������
     */
    function __construct( $admin_mode = false ) {
        $this->admin_mode = $admin_mode;
        
        // ������ �� ����� �� ���� - ������������ ����
        //$this->stop_regex = $this->getAdminStopRegex();
        
        $this->stop_words = $this->getAdminStopWords();

        $this->_prepareStopWords();
        $this->_prepareStopRegex();
    }

    /**
     * ������������� ���������� ������ ��� �������������� ����
     * 
     * @param  string $sWords ����� �� ������� ����� �������, ��� ���� ���� ����� �� ����
     */
    function setStopWords( $sWords = null ) {
        $this->stop_words = array();
        
        if ( is_null($sWords) ) {
            $this->stop_words = $this->getAdminStopWords();
        }
        elseif ( !empty($sWords) ) {
            $this->stop_words = explode(',', $sWords);
            $this->stop_words = array_map( 'trim', $this->stop_words );
            $this->stop_words = array_unique( $this->stop_words );
        }
        
        $this->_prepareStopWords();
    }
    
    /**
     * ��������������� ������� ��� ���������� �������������� ����
     */
    private function _prepareStopWords() {
        if ( $this->stop_words ) {
            usort( $this->stop_words, array('stop_words', '_cmp_len') );

            $this->stop_words = array_map( array('stop_words', '_pattern'), $this->stop_words );
        }
    }
    
    /**
     * ��������������� ������� ��� ���������� �������������� ����
     */
    function _cmp_len($a, $b) {
        return strlen($a) > strlen($b) ? -1 : ( strlen($a) < strlen($b) ? 1 : 0 );
    }
    
    /**
     * ��������������� ������� ��� ���������� �������������� ����
     */
    function _pattern($s) {
        return '/('. preg_quote($s, '/') .')/i';
    }
    
    /**
     * ������������� ���������� ������ ��� �������������� ����
     * 
     * @param  string $sRegex ����� � �����������: ���� ������ - ���� ���������
     */
    function setStopRegex( $sRegex = null ) {
        $this->stop_regex = array();
        
        if ( is_null($sRegex) ) {
            $this->stop_regex = $this->getAdminStopRegex();
        }
        elseif ( !empty($sRegex) ) {
            $this->stop_regex = explode("\n", $sRegex);
            $this->stop_regex = array_map( 'trim', $this->stop_regex );
            $this->stop_regex = array_unique( $this->stop_regex );
        }
        
        $this->_prepareStopRegex();
    }

    /**
     * ��������������� ������� ��� ���������� ����������� ���������
     */
    private function _prepareStopRegex() {
        $aRegex = array( 'stop_regex', 'profile_stop_regex', 'suspect_stop_regex' );
        foreach ( $aRegex as $sFld ) {
            if ( $this->$sFld ) {
                $aTmp = $this->$sFld;
                foreach ( $aTmp as $sKey => $sVal ) {
                    $sFirst  = preg_quote( $sVal[0], '/');
                    $sSearch = '/^(['. $sFirst .']{1})(.+)(['. $sFirst .']{1})([\w]*)?$/';
                    $aTmp[$sKey] = preg_replace( $sSearch, '$1($2)$3$4', $sVal );
                }
                
                $this->$sFld = $aTmp;
            }
        }
    }

    /**
     * �������� ���� ����� � ������.
     * 
     * � ������ �������������� �������������� ����� � ����������� ���������
     * �������������� ������ � ������.
     * � ������ ������������ ����������� ��������� ���������� �� CENSORED (@see globals.php)
     * 
     * @param  string $sText �������� �����
     * @param  string $replace_mode ����� ������: 'html', 'plain'
     * @param  bool $admin_mode �����������. ���������� ����� ��������������, �������� �� $this->admin_mode
     * @return string
     */
    function replace( $sText = '', $replace_mode = 'html', $admin_mode = null, $regex_prefix = '' ) {
        return $sText;

        if ( !empty($sText) ) {
            setlocale(LC_ALL, 'ru_RU.CP1251');
            
            $sRegexFld  = $regex_prefix . '_stop_regex';
            $aStopRegex = ( $regex_prefix && isset($this->$sRegexFld) ) ? $this->$sRegexFld : $this->stop_regex;
            $admin_mode = is_null( $admin_mode ) ? $this->admin_mode : $admin_mode;
            $this->replace_mode = $replace_mode;
            
            // ������������ ������ �������� ����� ������ �� �������� ������ ��� �����������
            $sText = $this->_linkReplace( $sText );
            
            if ( $admin_mode ) {
                if( $aStopRegex ) {
                    $sText = preg_replace_callback( $aStopRegex, array('stop_words', '_callbackReplace'), $sText );
                }
                
                if ( $this->stop_words && $replace_mode == 'html' ) {
                    $sText = preg_replace( $this->stop_words, self::STOP_WORDS, $sText );
                }
                
                $sText = $this->_restore( $sText, self::STOP_REGEX_CODE, $this->censored );
            }
            elseif( $aStopRegex ) {
                $sText = preg_replace( $aStopRegex, CENSORED, $sText );
            }
            
            $sText = $this->_linkRestore( $sText );
            
            setlocale(LC_ALL, 'en_US.UTF-8');
        }
        
        return $sText;
    }
    
    /**
     * ������������� ����� �������� ���� ���� �� ���� ����������: ��������� ����������, ���� ����� �� �������
     */
    function calculateRegexNoWords() {
        $this->calculate_regex_prefix = '';
        $this->calculate_words        = false;
    }
    
    /**
     * ������������ ���� ����� � ������/�������.
     * ����� ���������� ����������� ����� ��������� ����������
     * ���������� ����� ��������� ������� �����.
     * 
     * ����� �������� ���������� ����� �������� � �������� �� ���������.
     * 
     * @return int ����� ���������� ���� ����
     */
    function calculate() {
        $nRet  = 0;
        $aText = func_get_args();
        
        if ( $aText ) {
            foreach ( $aText as $mText ) {
                if ( is_array($mText) && $mText ) {
                    foreach ( $mText as $sText ) {
                        $nRet += $this->_calculate( $sText );
                    }
                }
                else {
                    $nRet += $this->_calculate( $mText );
                }
            }
        }
        
        $this->calculate_regex_prefix = 'suspect';
        $this->calculate_words        = true;
        
        return $nRet;
    }
    
    /**
     * ������������ ���� ����� � ������
     * 
     * @param  string $sText �����
     * @param  string $regex_prefix �����������. ������� ������ ���������. �� ��������� suspect ����� ��������������
     * @return int ���������� ���� ���� � ������
     */
    function _calculate( $sText = '', $regex_prefix = 'suspect', $calc_words = true ) {
        $nReg  = 0;
        $nStop = 0;
        
        if ( is_string($sText) && !empty($sText) ) {
            $sRegexFld  = $this->calculate_regex_prefix . '_stop_regex';
            $aStopRegex = ( $this->calculate_regex_prefix && isset($this->$sRegexFld) ) ? $this->$sRegexFld : $this->stop_regex;
            
            // ������������ ������ - ��� ������ ��� �� ���������
            $sText = $this->_linkReplace( $sText );
            $sText = preg_replace( $aStopRegex, CENSORED, $sText, -1, $nReg );
            
            if ( $this->calculate_words && $this->stop_words ) {
                $sText = preg_replace( $this->stop_words, CENSORED, $sText, -1, $nStop );
            }
        }
        
        return $nReg + $nStop;
    }
    
    /**
     * ��������������� ������� ��� ������ ����������� ���������
     * ��������� ������. �������� ������ �� ����
     * 
     * @param  string $sText �������� �����
     * @return string 
     */
    private function _linkReplace( $sText = '' ) {
        $this->links = array();
        
        if ( !empty ($sText) ) {
            setlocale(LC_ALL, 'ru_RU.CP1251');
            
            $hre      = HYPER_LINKS ? '{([^}]+)}' : '()'; // ����������� � globals.php.
            $sPattern = '~(https?:/(' . $hre . ')?/|www\.)(([\da-z-_�-���-ߨ]+\.)*([\da-z-_]+|��|��)(:\d+)?([/?#][^"\s<]*)*)~i';
            $sText = preg_replace_callback( $sPattern, array('stop_words', '_callbackLinkReplace'), $sText );
            
            setlocale(LC_ALL, 'en_US.UTF-8');
        }
        
        return $sText;
    }
    
    /**
     * ��������������� ������� ��� ������ ����������� ���������
     * ��������� ������. ���������� ������
     * 
     * @param string $sText
     */
    private function _linkRestore( $sText = '' ) {
        $sReturn = '';
        
        if ( !empty($sText) ) {
            $aParts = explode( self::STOP_LINK_CODE, $sText );
            $j      = 0;
            
            for ( $i = 0; $i < count($aParts); $i++ ) {
                $sReturn .= $aParts[$i];
                
                if ( !empty($this->links[$j]) ) {
                    $sReturn .= $this->links[$j];
                    $j++;
                }
            }
        }
        
        return $sReturn;
    }
    
    /**
     * ��������������� ������� ��� ������ ����������� ���������
     * ��������� ������
     * 
     * @param  array $matches
     * @return string 
     */
    function _callbackLinkReplace( $matches ) {
        $this->links[] = $matches[0];
        
        return self::STOP_LINK_CODE;
    }
    
    /**
     * ��������������� ������� ��� ������ ����������� ���������
     * �������� ���� �� ����������� ���������
     * 
     * @param  string $sText �����
     * @param  string $sCode ���
     * @param  array $aReplace ������ �����
     * @return string 
     */
    private function _restore( $sText = '', $sCode = '', $aReplace = array() ) {
        if ( !empty($sText) && !empty($aReplace) ) {
            for ($i = 0; $i < count($aReplace); $i++) {
                $sText = str_replace( $sCode . ($i+1), $aReplace[$i], $sText );
            }
        }
        
        return $sText;
    }


    /**
     * ��������������� ������� ��� ������ ����������� ���������
     * ��������� ������ $this->censored � �������� ����������� ��������� �� 
     * 
     * @param  array $matches
     * @return string 
     */
    function _callbackReplace( $matches ) {
        $sTxt = $matches[0];

        $sFormat          = $this->replace_mode == 'html' ? self::STOP_REGEX : self::STOP_REGEX_PLAIN;
        $this->censored[] = sprintf( $sFormat, $sTxt );
        
        return self::STOP_REGEX_CODE . count($this->censored);
    }
    
    /**
     * ��������� ������ ����������� � ���������� ������������� ��������� �� ����������
     * 
     * @param  string $sRegex ����� � �����������: ���� ������ - ���� ���������
     * @return string ������ ������ - �����, ��������� � ������� - ������
     */
    function validateAdminStopRegex( $sRegex = '' ) {
        $sError = '';
        $aRegex = explode("\n", $sRegex);
        
        if ( is_array($aRegex) && count($aRegex) ) {
            foreach ( $aRegex as $sOne ) {
                if ( @preg_match( trim($sOne), 'test') === false ) {
                    $sError = $sOne;
                    break;
                }
            }
        }
        
        return $sError;
    }
    
    /**
     * ��������� ������ ���� ��� ��������� ��������������� ���������� � ������������� ���������
     * 
     * @param  string $sWords ����� �� ������� ����� �������
     * @return bool true - �����, false - ������
     */
    function updateAdminStopWords( $sWords = '' ) {
        return self::_updateAdminStopWords( explode(',', $sWords), self::$stop_words_words );
    }
    
    /**
     * ��������� ������ ����������� � ���������� ������������� ���������
     * 
     * @param  string $sRegex ����� � �����������: ���� ������ - ���� ���������
     * @return bool true - �����, false - ������
     */
    function updateAdminStopRegex( $sRegex = '' ) {
        return self::_updateAdminStopWords( explode("\n", $sRegex), self::$stop_words_regex );
    }
    
    /**
     * ��������� ������ �������������� ���� � ���������� ��������� ��� ������������� ����������������� ��������
     * 
     * @param  array $aWords ������ �����
     * @param  int $nType ���: 1 - �����, 2 - ���������� ���������
     * @return bool true - �����, false - ������
     */
    private function _updateAdminStopWords( $aWords = '', $nType = 0 ) {
        $aStopWords = array();
        
        $GLOBALS['DB']->query( 'DELETE FROM '. self::$stop_words_table .' WHERE type = ?i', $nType );
        
        if ( !$GLOBALS['DB']->error ) {
            if ( is_array($aWords) && count($aWords) ) {
                $aWords = array_map( 'trim', $aWords );
                $aWords = array_unique( $aWords );
                $aData = array();
                
                foreach ( $aWords as $sOne ) {
                    if ( $sOne ) {
                        $aData[] = array( 'word' => $sOne, 'type' => $nType );
                    }
                }
                
                if ( $aData ) {
                    $GLOBALS['DB']->insert( self::$stop_words_table, $aData );

                    if ( $GLOBALS['DB']->error ) {
                        return FALSE;
                    }
                }
            }
        }
        else {
            return FALSE;
        }
        
        if ( !$sError ) {
            $sMemKey = self::_getAdminStopWordsMemKey( $nType );
            $oMemBuf = new memBuff();
            $oMemBuf->set( $sMemKey, $aWords, 3600 );
        }
        
        return TRUE;
    }
    
    /**
     * ���������� ������ ���� ��� ��������� ��������������� ���������� � ������������� ���������
     * 
     * @param  bool $bMemBuf ���������� � true ���� ������ ����� �� �������
     * @return array
     */
    function getAdminStopWords( $bMemBuf = true ) {
        return self::_getAdminStopWords( self::$stop_words_words, $bMemBuf );
    }
    
    /**
     * ���������� ������ ����������� � ���������� ������������� ���������
     * 
     * @param  bool $bMemBuf ���������� � true ���� ������ ����� �� �������
     * @return array
     */
    function getAdminStopRegex( $bMemBuf = true ) {
        return self::_getAdminStopWords( self::$stop_words_regex, $bMemBuf );
    }
    
    /**
     * ��������� ������ �������������� ���� � ���������� ��������� ��� ������������� ����������������� ��������
     * 
     * @param  int $nType ���: 1 - �����, 2 - ���������� ���������
     * @param  bool $bMemBuf ���������� � true ���� ������ ����� �� �������
     * @return array
     */
    private function _getAdminStopWords( $nType = 0, $bMemBuf = true ) {
        $aWords  = array();
        $sMemKey = self::_getAdminStopWordsMemKey( $nType );
        $oMemBuf = new memBuff();
        
        if ( $bMemBuf ) {
            $aWords  = $oMemBuf->get( $sMemKey );
        }
        
        if ( !$bMemBuf || $aWords === false ) {
            $aWords = $GLOBALS['DB']->col(
                'SELECT word FROM '. self::$stop_words_table .' WHERE type = ?i ORDER BY id', $nType 
            );
            
            $oMemBuf->set( $sMemKey, $aWords, 3600 );
        }
        
        return $aWords;
    }
    
    /**
     * ���������� ��� ����� � ������� ��� �������� �������������� ���� � ���������� ���������
     * 
     * @param  int $nType ���: 1 - �����, 2 - ���������� ���������
     * @return string 
     */
    private function _getAdminStopWordsMemKey( $nType = 0 ) {
        $sMemKey = ( $nType == self::$stop_words_words ) ? 'words' : 'regex';
        return 'user_content_stop_' . $sMemKey;
    }
}
