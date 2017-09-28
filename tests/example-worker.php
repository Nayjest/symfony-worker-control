<?php
$worker_id = @$argv[1]?:rand(0, 999);
echo "[worker $worker_id]\n";
$i=1;
while (true) {
    $date = date('Y-m-d H:i:s');
    $msg = "[$date] worker #$worker_id: $i";
    echo "$msg\n";
    fwrite(STDERR, "$msg(stderr)\n");
    $i++;
    sleep(1);
}