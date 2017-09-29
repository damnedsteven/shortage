<?php

exec ("./send_mail.py", $output, $return);

// Return will return non-zero upon an error
if (!$return) {
    echo "Mail Sent Successfully";
} else {
    echo "Mail Not Sent";
}

header('Location: ' . $_SERVER['HTTP_REFERER']); //redirect back to the other page

?>