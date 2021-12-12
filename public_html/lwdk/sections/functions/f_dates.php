<?php
    trait function_group_dates {
        function diff_dates(String $data1, String $data2, String $format = "Y-m-d", String $separator = "-"){
            $format = explode($separator, $format);
            foreach($format as $index=>$value){
                switch($value){
                    case "Y": $format[$index] = 0; break;
                    case "m": $format[$index] = 1; break;
                    case "d": $format[$index] = 2; break;
                }
            }
            $data1 = explode($separator, $data1);
            $data1 = "{$data1[$format[0]]}-{$data1[$format[1]]}-{$data1[$format[2]]}";

            $data2 = explode($separator, $data2);
            $data2 = "{$data2[$format[0]]}-{$data2[$format[1]]}-{$data2[$format[2]]}";

            $c = 0;

            foreach(new DatePeriod(
                new DateTime($data1), // 1st PARAM: start date
                new DateInterval('P1D'), // 2nd PARAM: interval (1 day interval in this case)
                new DateTime($data2), // 3rd PARAM: end date
                null // 4th PARAM (optional): self-explanatory
            ) as $_c): $c++; endforeach;

            foreach(new DatePeriod(
                new DateTime($data2), // 1st PARAM: start date
                new DateInterval('P1D'), // 2nd PARAM: interval (1 day interval in this case)
                new DateTime($data1), // 3rd PARAM: end date
                null // 4th PARAM (optional): self-explanatory
            ) as $_c): $c++; endforeach;

            return (int)$c;
        }

        function sum_dates(String $data, int $dias, int $meses = 0, int $anos = 0, String $output = "Y-m-d", String $format = "Y-m-d", String $separator = "-"){
            $o_data = $data;
            $o_format = $format;

            $format = explode($separator, $format);
            foreach($format as $index=>$value){
                switch($value){
                    case "Y": $format[$index] = 2; break;
                    case "m": $format[$index] = 1; break;
                    case "d": $format[$index] = 0; break;
                }
            }

            $diasmes = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            if($data == "now"): $data = date($format); endif;

            $data = explode($separator, $data);

            $data[0] = (int)$data[$format[0]];
            $data[1] = (int)$data[$format[1]];
            $data[2] = (int)$data[$format[2]];

            $data[0] += (int)$dias;

            $do_while = true;

            while(1){

                while($data[0] > $diasmes[$data[1]-1]){
                    $data[0] -= $diasmes[$data[1]-1];
                    $data[1]++;

                    while($data[1] > 12){
                        $data[1] -= 12;
                        $data[2]++;
                    }

                    while($data[1] < 1){
                        $data[1] += 12;
                        $data[2]--;
                    }
                }

                while($data[0] < 1){
                    $data[0] += $diasmes[$data[1]-2];
                    $data[1]--;

                    while($data[1] > 12){
                        $data[1] -= 12;
                        $data[2]++;
                    }

                    while($data[1] < 1){
                        $data[1] += 12;
                        $data[2]--;
                    }
                }

                $data[1] += (int)$meses;
                $data[2] += (int)$anos;

                while($data[1] > 12){
                    $data[1] -= 12;
                    $data[2]++;
                }

                while($data[1] < 1){
                    $data[1] += 12;
                    $data[2]--;
                }

                $data[0] = ($data[0] < 10?"0":"") . "{$data[0]}";
                $data[1] = ($data[1] < 10?"0":"") . "{$data[1]}";

                $diff = $this->diff_dates($o_data, implode($data[2], explode("Y", implode($data[1], explode("m", implode($data[0], explode("d", $o_format)))))), $o_format) - abs($dias);

                if($diff > 0){
                    $data[0]++;
                    $diff--;
                } elseif($diff < 0){
                    $data[0]--;
                    $diff++;
                } else {
                    break;
                }
            }
            return implode($data[2], explode("Y", implode($data[1], explode("m", implode($data[0], explode("d", $output))))));
        }
    }
?>
