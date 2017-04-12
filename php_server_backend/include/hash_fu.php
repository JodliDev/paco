<?php
//error_reporting(0);
function check_local() {
	return $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1';
}

//Thanks to:
//https://github.com/whitehat101/apr1-md5

//The MIT License (MIT)

//Copyright (c) 2015 Jeremy

//Permission is hereby granted, free of charge, to any person obtaining a copy
//of this software and associated documentation files (the "Software"), to deal
//in the Software without restriction, including without limitation the rights
//to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
//copies of the Software, and to permit persons to whom the Software is
//furnished to do so, subject to the following conditions:

//The above copyright notice and this permission notice shall be included in all
//copies or substantial portions of the Software.

//THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
//FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
//AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
//LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
//OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
//SOFTWARE.
class APR1_MD5 {
    const BASE64_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    const APRMD5_ALPHABET = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    // Source/References for core algorithm:
    // http://www.cryptologie.net/article/126/bruteforce-apr1-hashes/
    // http://svn.apache.org/viewvc/apr/apr-util/branches/1.3.x/crypto/apr_md5.c?view=co
    // http://www.php.net/manual/en/function.crypt.php#73619
    // http://httpd.apache.org/docs/2.2/misc/password_encryptions.html
    // Wikipedia
    public static function hash($mdp, $salt = null) {
        if (is_null($salt))
            $salt = self::salt();
        $salt = substr($salt, 0, 8);
        $max = strlen($mdp);
        $context = $mdp.'$apr1$'.$salt;
        $binary = pack('H32', md5($mdp.$salt.$mdp));
        for($i=$max; $i>0; $i-=16)
            $context .= substr($binary, 0, min(16, $i));
        for($i=$max; $i>0; $i>>=1)
            $context .= ($i & 1) ? chr(0) : $mdp[0];
        $binary = pack('H32', md5($context));
        for($i=0; $i<1000; $i++) {
            $new = ($i & 1) ? $mdp : $binary;
            if($i % 3) $new .= $salt;
            if($i % 7) $new .= $mdp;
            $new .= ($i & 1) ? $binary : $mdp;
            $binary = pack('H32', md5($new));
        }
        $hash = '';
        for ($i = 0; $i < 5; $i++) {
            $k = $i+6;
            $j = $i+12;
            if($j == 16) $j = 5;
            $hash = $binary[$i].$binary[$k].$binary[$j].$hash;
        }
        $hash = chr(0).chr(0).$binary[11].$hash;
        $hash = strtr(
            strrev(substr(base64_encode($hash), 2)),
            self::BASE64_ALPHABET,
            self::APRMD5_ALPHABET
        );
        return '$apr1$'.$salt.'$'.$hash;
    }
    // 8 character salts are the best. Don't encourage anything but the best.
    public static function salt() {
        $alphabet = self::APRMD5_ALPHABET;
        $salt = '';
        for($i=0; $i<8; $i++) {
            $offset = hexdec(bin2hex(openssl_random_pseudo_bytes(1))) % 64;
            $salt .= $alphabet[$offset];
        }
        return $salt;
    }
    public static function check($plain, $hash) {
        $parts = explode('$', $hash);
        return self::hash($plain, $parts[2]) === $hash;
    }
}

function hash_pass($plainpasswd) {
	return APR1_MD5::hash(base64_decode($plainpasswd));
}
function check_user() {
	$cookie_user = $_COOKIE['user'];
	$cookie_pass = hash_pass($_COOKIE['pass']);

	$user = null;
	if(!file_exists('data/.logins'))
		return null;
	$h = fopen('data/.logins', 'r');
	while(!feof($h)) {
		$line = substr(fgets($h), 0, -1);
		if($line == '')
			continue;
		$data = explode(':', $line);
		if($data[0] == $cookie_user && $data[1] == $cookie_pass) {
			$user = $data[0];
			break;
		}
	}
	fclose($h);
	return $user;
}


?>