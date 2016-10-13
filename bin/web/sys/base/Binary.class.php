<?php
/**
 * 二进制操作类
 * @author Rolong<rolong@vip.qq.com>
 */

# a - NUL-padded string
# A - SPACE-padded string
# h - Hex string, low nibble first
# H - Hex string, high nibble first
# c - signed char
# C - unsigned char
# s - signed short (always 16 bit, machine byte order)
# S - unsigned short (always 16 bit, machine byte order)
# n - unsigned short (always 16 bit, big endian byte order)
# v - unsigned short (always 16 bit, little endian byte order)
# i - signed integer (machine dependent size and byte order)
# I - unsigned integer (machine dependent size and byte order)
# l - signed long (always 32 bit, machine byte order)
# L - unsigned long (always 32 bit, machine byte order)
# N - unsigned long (always 32 bit, big endian byte order)
# V - unsigned long (always 32 bit, little endian byte order)
# f - float (machine dependent size and representation)
# d - double (machine dependent size and representation)
# x - NUL byte
# X - Back up one byte
# @ - NUL-fill to absolute position

# a 一个填充空的字节串
# A 一个填充空格的字节串
# b 一个位串，在每个字节里位的顺序都是升序
# B 一个位串，在每个字节里位的顺序都是降序
# c 一个有符号 char（8位整数）值
# C 一个无符号 char（8位整数）值；关于 Unicode 参阅 U
# d 本机格式的双精度浮点数
# f 本机格式的单精度浮点数
# h 一个十六进制串，低四位在前
# H 一个十六进制串，高四位在前
# i 一个有符号整数值，本机格式
# I 一个无符号整数值，本机格式
# l 一个有符号长整形，总是 32 位
# L 一个无符号长整形，总是 32 位
# n 一个 16位短整形，“网络”字节序（大头在前）
# N 一个 32 位短整形，“网络”字节序（大头在前）
# p 一个指向空结尾的字串的指针
# P 一个指向定长字串的指针
# q 一个有符号四倍（64位整数）值
# Q 一个无符号四倍（64位整数）值
# s 一个有符号短整数值，总是 16 位
# S 一个无符号短整数值，总是 16 位
# u 一个无编码的字串
# U 一个 Unicode 字符数字
# v 一个“VAX”字节序（小头在前）的 16 位短整数
# V 一个“VAX”字节序（小头在前）的 32 位短整数
# w 一个 BER 压缩的整数
# x 一个空字节（向前忽略一个字节）
# X 备份一个字节
# Z 一个空结束的（和空填充的）字节串
# @ 用空字节填充绝对位置

class Binary
{
    public $bin;
    public $index = 0;

    public function __construct($bin)
    {
        $this->bin = $bin;
    }

    public function get_data($len)
    {
        if(strlen($this->bin) > 0){
            $result = substr($this->bin, $this->index, $len);
            $this->index += $len;
            return $result;
        }else{
            exit("No Binary");
        }
    }

    public function read_uint8()
    {
        $data = $this->get_data(1);
        $result = unpack('C1data', $data);
        return $result['data'];
    }

    public function read_int8()
    {
        $data = $this->get_data(1);
        $result = unpack('c1data', $data);
        return $result['data'];
    }

    public function read_uint16()
    {
        $data = $this->get_data(2);
        $result = unpack('V1data', $data);
        return $result['data'];
    }

    public function read_uint32()
    {
        $data = $this->get_data(4);
        $result = unpack('L1data', $data);
        return $result['data'];
    }

    public function read_uint32_big()
    {
        $data = $this->get_data(4);
        $result = unpack('N1data', $data);
        return $result['data'];
    }

}
