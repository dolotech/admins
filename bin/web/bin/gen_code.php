<?php
// 生成一个激活码
function makeCode($index, $time){
    $bin1 = pack('V1', $time);
    $bin2 = pack('v1', $index);
    $h1 = bin2hex($bin1);
    $h2 = bin2hex($bin2);
    $c1 = mt_rand(0, 1) ? chr(mt_rand(97, 122)) : chr(mt_rand(48, 57));
    $c2 = mt_rand(0, 1) ? chr(mt_rand(97, 122)) : chr(mt_rand(48, 57));
    $c3 = mt_rand(0, 1) ? chr(mt_rand(97, 122)) : chr(mt_rand(48, 57));
    $code = $c1 . $h1 . $c2 . $h2 . $c3;
    $code = strtoupper($code);
    return $code;
}

// 生成批量激活码
function makeCodes($name, $giftid, $num, $platform){
    $num = $num > 10000 ? 10000 : $num;
    $time = time();
    $first = true;
    while($num--){
        $code = makeCode($num, $time);
        if($first){
            $sql = "INSERT INTO `log_giftcode_list` (`giftid`, `code`, `platform`) VALUES ('{$giftid}', '{$code}', '{$platform}')";
            $first = false;
        }elseif($num > 0){
            $sql = ",('{$giftid}', '{$code}')";
        }else{
            $sql = ",('{$giftid}', '{$code}');";
        }
        file_put_contents('./'.$name.'.txt', $code."\r\n",FILE_APPEND);
        file_put_contents('./'.$name.'.sql', $sql."\r\n",FILE_APPEND);
    }
}


// Go ...
makeCodes('gift_code', 1, 100, 'tencent');
