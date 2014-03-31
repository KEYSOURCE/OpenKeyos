<?php

function notice_handler($errno, $errstr) {
    return true;
}

class ImapConnector {

    /**
     * Server Address
     * @var string
     */
    private $server = null;
    /**
     * Server Port
     * @var string
     */
    private $port = null;
    /**
     * SSL Setting
     * @var boolean
     */
    private $encrypt = true;
    /**
     * Mailbox Name
     * @var string
     */
    private $mailbox = null;
    /**
     * Validate certificate or not
     * @var boolean
     */
    private $validate_cert = true;
    /**
     * Username
     * @var string
     */
    private $username = null;
    /**
     * Password
     * @var string
     */
    private $password = null;
    private $connection = null;
    private $connection_string = null;
    private $connected = false;
    private $msg_numbers = null;
    private $index = -1;

    function __construct($config = null) {
        if (!empty($config)) {
            if (isset($config['server'])) {
                $this->server = $config['server'];
            } else {
                return;
            }
            if (isset($config['port'])) {
                $this->port = $config['port'];
            } else {
                return;
            }
            if (isset($config['mailbox'])) {
                $this->mailbox = $config['mailbox'];
            } else {
                return;
            }
            if (isset($config['username'])) {
                $this->username = $config['username'];
            } else {
                return;
            }
            if (isset($config['password'])) {
                $this->password = $config['password'];
            } else {
                return;
            }
            if (isset($config['encrypt'])) {
                $this->encrypt = $config['encrypt'];
            }
            if (isset($config['validate_cert'])) {
                $this->validate_cert = $config['validate_cert'];
            }
        } else {
            return;
        }

        $this->build_connection_string();
        set_error_handler('notice_handler', E_NOTICE);
        $mbox = imap_open($this->connection_string, $this->username, $this->password);
        if ($mbox) {
            $this->connected = true;
            $this->connection = $mbox;
        }
    }

    public function connected() {
        return $this->connected;
    }

    private function build_connection_string() {
        $this->connection_string = '{' . $this->server . ':' . $this->port . '/imap';
        if (!empty($this->encrypt)) {
            $this->connection_string .= '/' . $this->encrypt;
        }
        if (!$this->validate_cert) {
            $this->connection_string .= '/novalidate-cert';
        }
        $this->connection_string .= '}';
        $this->connection_string .= $this->mailbox;
    }

    private function get_mime_type(&$structure) {
        $primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
        if ($structure->subtype) {
            return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    private function get_part($msg_number, $mime_type, $structure = false, $part_number = false) {
        if (!$structure) {
            $structure = imap_fetchstructure($this->connection, $msg_number);
        }
        if ($structure) {
            if ($mime_type == $this->get_mime_type($structure)) {
                if (!$part_number) {
                    $part_number = "1";
                }
                $text = imap_fetchbody($this->connection, $msg_number, $part_number, FT_PEEK);
                if ($structure->encoding == 3) {
                    return imap_base64($text);
                } else if ($structure->encoding == 4) {
                    return imap_qprint($text);
                } else {
                    return $text;
                }
            }
            if ($structure->type == 1) { /* multipart */
                while (list($index, $sub_structure) = each($structure->parts)) {
                    $prefix = '';
                    if ($part_number) {
                        $prefix = $part_number . '.';
                    }
                    $data = $this->get_part($msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    public function fetch_msg_numbers($query = null) {
        if (empty($query)) {
            $query = 'UNSEEN';
        }
        $msg_numbers = imap_search($this->connection, $query);
        if (is_array($msg_numbers)) {
            rsort($msg_numbers);
            $this->msg_numbers = $msg_numbers;
        }
    }

    private function fetch_header($msg_number) {
        //return imap_headerinfo($this->connection, $msg_number);
        return imap_rfc822_parse_headers(imap_fetchheader($this->connection, $msg_number));
    }

    private function fetch_mail_content($msgno, $type) {
        $mail = new stdClass();
        $body = $this->get_part($msgno, $type);
        $mail->mimetype = 'TEXT/HTML';
        if (empty($body)) {
            $body = $this->get_part($msgno, 'TEXT/PLAIN');
            $mail->mimetype = 'TEXT/PLAIN';
        }

        $header = $this->fetch_header($msgno);

        //Decode Subject
        $el = imap_mime_header_decode($header->Subject);
        $header->Subject = '';
        foreach($el as $e) {
            $header->Subject .= $e->text;
        }

        //Decode Subject
        $el = imap_mime_header_decode($header->subject);
        $header->subject = '';
        foreach($el as $e) {
            $header->subject .= $e->text;
        }
        
        $mail->header = $header;
        $mail->body = $body;
        return $mail;
    }

    public function fetch_mail($msgno = null, $type = 'TEXT/HTML') {
        if($this->connected() and $msgno !== null) {
                return $this->fetch_mail_content($msgno, $type);
        }
        if ($this->connected() and $this->has_mail()) {
            if (empty($this->msg_numbers)) {
                $this->fetch_msg_numbers();
            }
            if (isset($this->msg_numbers[$this->index + 1])) {
                $this->index++;
                $mail = $this->fetch_mail_content($this->msg_numbers[$this->index], $type);
                return $mail;
            } else {
                return null;
            }
        }
        return null;
    }

    public function set_msgno($msgno) {
        if ($this->connected()) {
            $msg_set = false;
            foreach ($this->msg_numbers as $key => $value) {
                if ($value == $msgno) {
                    $this->index = $key;
                    return true;
                }
            }
        }
        return false;
    }

    public function get_msgnos() {
        if ($this->connected()) {
            return $this->msg_numbers;
        }
        return null;
    }

    public function has_mail() {
        return (!empty($this->msg_numbers));
    }

    public function set_mail_seen($msgno = null) {
        if ($this->connected()) {
            if($msgno !== null) {
                return imap_setflag_full($this->connection, $msgno, "\\Seen");
            }
            return imap_setflag_full($this->connection, $this->msg_numbers[$this->index], "\\Seen");
        } else {
            return false;
        }
    }

    public function set_mail_unseen($msgno = null) {
        if ($this->connected()) {
            if($msgno !== null) {
                return imap_clearflag_full($this->connection, $msgno, "\\Seen");
            }
            return imap_clearflag_full($this->connection, $this->msg_numbers[$this->index], "\\Seen");
        } else {
            return false;
        }
    }

    public function get_msg_number() {
        if ($this->connected()) {
            return $this->msg_numbers[$this->index];
        }
        return null;
    }

    public function disconnect() {
        if ($this->connected()) {
            imap_close($this->connection);
            $this->connection = null;
            $this->connected = false;
        }
    }

    function __destruct() {
        $this->disconnect();
    }

}

?>
