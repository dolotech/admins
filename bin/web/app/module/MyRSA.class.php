<?php

/*-----------------------------------------------------+
 * RSA
 * 需要 openssl 支持
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

class MyRSA
{
    private $_privKey;
    private $_pubKey;

    public function __construct() {

    }

    /**
     * * setup the private key
     */
    public function setPrivKey($key) {
        $pem = chunk_split($key, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n".$pem."-----END PRIVATE KEY-----\n";
        $this->_privKey = openssl_pkey_get_private ( $pem );
        return true;
    }

    /**
     * * setup the public key
     */
    public function setPubKey($key) {
        $pem = chunk_split($key, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
        $this->_pubKey = openssl_pkey_get_public ( $pem );
        return true;
    }

    /**
     * 签名
     * @param 被签名数据 $dataString
     * @return string
     */
    public function sign($dataString){
        $signature = false;
        openssl_sign($dataString, $signature, $this->_privKey);
        return base64_encode($signature);
    }

    /**
     * 验证签名
     * @param 被签名数据 $dataString
     * @param 已经签名的字符串 $signString
     * @return 1 if the signature is correct, 0 if it is incorrect, and -1 on error.
     */
    public function verify($dataString, $signString) {
        $signature =base64_decode($signString);
        return openssl_verify($dataString, $signature, $this->_pubKey);
    }

    public function check($dataString,$signString) {
        if($this->verify($dataString, $signString)==1) return true;
        return false;
    }

    /**
     * * encrypt with the private key
     */
    public function privEncrypt($data) {
        $r = openssl_private_encrypt ( $data, $encrypted, $this->_privKey );
        if ($r) {
            return base64_encode ( $encrypted );
        }
        return null;
    }

    /**
     * * decrypt with the private key
     */
    public function privDecrypt($encrypted) {
        $encrypted = base64_decode ( $encrypted );
        $r = openssl_private_decrypt ( $encrypted, $decrypted, $this->_privKey );
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    /**
     * * encrypt with public key
     */
    public function pubEncrypt($data) {
        $r = openssl_public_encrypt ( $data, $encrypted, $this->_pubKey );
        if ($r) {
            return base64_encode ( $encrypted );
        }
        return null;
    }

    /**
     * * decrypt with the public key
     */
    public function pubDecrypt($crypted) {
        $crypted = base64_decode ( $crypted );
        $r = openssl_public_decrypt ( $crypted, $decrypted, $this->_pubKey );
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    public function __destruct() {
        @ openssl_free_key ( $this->_privKey );
        @ openssl_free_key ( $this->_pubKey );
    }

}
