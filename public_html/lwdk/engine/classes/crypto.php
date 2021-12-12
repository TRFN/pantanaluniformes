<?php

    class crypto {
        private static function outCastIndex(Array $array, int $index){
            while($index >= count($array)){
                $index -= count($array);
            }
            return $array[$index];
        }

        private static function operation(String $string, String $password, int $operation){
            $password = sha1($password);
            $password = str_split($password);
            $string = str_split($string);
            $output = "";

            for( $i = 0; $i < count($string); $i++ ){
                $char = chr(ord($string[$i]) + (ord(self::outCastIndex($password,$i))*$operation));
                $output = "$output{$char}";
            }

            return $output;
        }

        public static function crypt(String $string, String $password){
            return self::operation($string, $password, 1);
        }

        public static function unCrypt(String $string, String $password){
            return self::operation($string, $password, -1);
        }
    }
