<?php

$i = 1;
$max = 100;
while ($i <= $max){
    $val = $i;
    if ($i % 3 === 0){$val = 'foo';}
    if ($i % 5 === 0){$val = 'bar';}
    if ($i % 3 === 0 && $i % 5 === 0){$val = 'foobar';}
    $val = $i < $max ? $val . ",":$val;
    echo $val;
    $i++;
}
?>
