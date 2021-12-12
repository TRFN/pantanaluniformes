<?php
    class DOMDocument {
        function __construct(){
            die("<span style='font-family: Arial; font-size: 14px;'><b>Fatal Error</b>: The <i>DOMDocument()</i> class has not been initialized because a <b>DOMDocument</b> library is <u>not installed on the server</u>. <br><br><small style='color: #f00;'>Contact the administrative support of the server to install the library, since the <u>LWDK Engine</u> depends on this class to work correctly.</small></span>");
        }
    }
?>
