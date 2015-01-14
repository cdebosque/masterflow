<?php
/** Class SFTPConnection
 * @see http://www.php.net/manual/fr/function.ssh2-sftp.php#71197
 */
class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host, $port=22, $args=array())
    {
        if (!function_exists("ssh2_connect")) {
            $msg = "Extension ssh2 (ssh2_connect) not included. Also check that allow_url_fopen = 1 on ini conf";
            echo $msg;
            throw new Exception("$msg");
        }

        $this->connection = ssh2_connect($host, $port);

        if (! $this->connection) {
            throw new Exception("Could not connect to $host on port $port.");
        } elseif (!empty($args)) {
            if(!empty($args['user']) and !empty($args['pubkeyfile']) and !empty($args['privkeyfile']) ) {
                ssh2_auth_pubkey_file($this->connection, $args['user'],
                        $args['pubkeyfile'],
                        $args['privkeyfile']);
                $this->sftp = ssh2_sftp($this->connection);
            }
        }
    }

    public function login($username, $password)
    {
        if (! ssh2_auth_password($this->connection, $username, $password)) {
            throw new Exception("Could not authenticate with username $username and $password.");
        }
        $this->sftp = ssh2_sftp($this->connection);
        if (! $this->sftp) {
            throw new Exception("Could not initialize SFTP subsystem.");
        }
        return true;
    }

    public function isAvailable()
    {
        return !empty($this->connection) and !empty($this->sftp);
    }

    public function put($local_file, $remote_file)
    {
        $sftp = $this->sftp;

        $data_to_send = @file_get_contents($local_file);
        if ($data_to_send === false) {
            throw new Exception("Could not open local file: $local_file.");
        }

        $stream = fopen("ssh2.sftp://$sftp$remote_file", 'w');
        if (! $stream) {
            throw new Exception("Could not open file: $remote_file");
        }

        if (fwrite($stream, $data_to_send) === false) {
            throw new Exception("Could not send data from file: $local_file.");
        }
        @fclose($stream);
        return true;
    }

    public function get($remote_file, $local_file)
    {
        $sftp = $this->sftp;
        $stream = fopen("ssh2.sftp://$sftp$remote_file", 'r');
        if (! $stream) {
            throw new Exception("Could not open file: $remote_file");
        }
        $size = $this->getFileSize($remote_file);
        if($size === 0){
            throw new Exception("Remote file exist but is empty : $remote_file");
        }
        $contents = '';
        $read = 0;
        $len = $size;
        while ($read < $len && ($buf = fread($stream, $len - $read))) {
            $read += strlen($buf);
            $contents .= $buf;
        }
        dump("size:", $size, "extrait contents:", substr($contents, 0, 200));
        $r = file_put_contents($local_file, $contents);
        @fclose($stream);
        return $r;
    }

    public function getFileSize($remote_file)
    {
        $sftp = $this->sftp;
        return filesize("ssh2.sftp://$sftp$remote_file");
    }

}//class


/* exemple :
try {
    $sftp = new SFTPConnection("localhost", 22);
    $sftp->login("username", "password");
    $sftp->put("/tmp/to_be_sent", "/tmp/to_be_received");
    //$sftp->get('remote_file.txt', '/home/local_file.txt');
} catch (Exception $e) {
    echo $e->getMessage();
}
*/
?>
