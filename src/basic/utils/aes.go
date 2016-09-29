package utils

import (
	"crypto/aes"
	"crypto/cipher"
)

type AesEncrypt struct {
	key []byte
}

func (this *AesEncrypt) SetKey(key []byte) {
	keyLen := len(key)
	if keyLen < 16 {
		panic("res key 长度不能小于16")
	}
	if keyLen >= 32 {
		//取前32个字节
		this.key = key[:32]
	}
	if keyLen >= 24 {
		//取前24个字节
		this.key = key[:24]
	}
	//取前16个字节
	this.key = key[:16]
}

//加密字符串
func (this *AesEncrypt) Encrypt(strMesg []byte) ([]byte, error) {
	var iv = this.key[:aes.BlockSize]
	encrypted := make([]byte, len(strMesg))
	aesBlockEncrypter, err := aes.NewCipher(this.key)
	if err != nil {
		return nil, err
	}
	aesEncrypter := cipher.NewCFBEncrypter(aesBlockEncrypter, iv)
	aesEncrypter.XORKeyStream(encrypted, strMesg)
	return encrypted, nil
}

//解密字符串
func (this *AesEncrypt) Decrypt(src []byte) (strDesc []byte, err error) {
	var iv = this.key[:aes.BlockSize]
	decrypted := make([]byte, len(src))
	var aesBlockDecrypter cipher.Block
	aesBlockDecrypter, err = aes.NewCipher(this.key)
	if err != nil {
		return nil, err
	}
	aesDecrypter := cipher.NewCFBDecrypter(aesBlockDecrypter, iv)
	aesDecrypter.XORKeyStream(decrypted, src)
	return decrypted, nil
}
