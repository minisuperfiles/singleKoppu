<?php
/*-----------@
SingleKoppu v1.0 (file manager)
Provided by Mini Super Files
Developed by Jagadeesan S
Blog: https://minisuperfiles.blogspot.com/
Email: jagadeesanjd11@gmail.com
--------------
Github link:
https://github.com/minisuperfiles
https://github.com/jagadeesanjd11
@-----------*/
class SingleKoppu {
  public $dir;
  public $url;
  function __construct($curDir = null) {
    if ($curDir == null) {
      $info = pathinfo(__FILE__);
      $this->dir = $info['dirname'];
    } else {
      $this->dir = $curDir;
    }
  }
  function fileList() {
    $files = array_slice(scandir($this->dir), 2);
    $list = array();
    for ($i = 0; $i < sizeof($files); $i++) {
      $type = filetype($this->dir . '/' . $files[$i]);
      $download = "?download={$files[$i]}&type={$type}&curDir={$this->dir}";
      $list[] = array(
        'file' => $files[$i],
        'type' => $type,
        'download' => $download,
        'delete' => "?delete={$files[$i]}&type={$type}&curDir={$this->dir}",
        'view' => ($type == 'dir') ? "?goDir={$this->dir}/{$files[$i]}&curDir={$this->dir}" : $download,
      );
    }
    return $list;
  }
  function rename($data) {
   $info = rename($data['curDir'] . '/' . $data['rename'], $data['curDir'] . '/' . $data['newName']);
   $this->dir = $data['curDir'];
  }
  function download($data) {
    if ($data['type'] == 'file') {
      $file=$data['curDir'] . '/' . $data['download'];
      header('Content-Description: File Transfer');
      header("Content-Type:application/octet-stream");
      header("Accept-Ranges: bytes");
      header("Content-Length: " . filesize($file));
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header("Content-Disposition: attachment; filename=" . $data['download']);
      flush(); // Flush system output buffer
      readfile($file);
      exit;
    } else if ($data['type'] == 'dir') {
      echo 'zip download not done.......<br>';
    }
  }
  function delete($data) {
    if ($data['type'] == 'dir') {
      $info = rmdir($data['curDir'] . '/' . $data['delete']);
    } else if ($data['type'] == 'file') {
      $info = unlink($data['curDir'] . '/' . $data['delete']);
    }
    $this->dir = $data['curDir'];
  }
  function goDir($dir) {
    $this->dir = $dir;
  }
  function backDir($dir) {
    $dirAr = explode('/', $dir);
    array_pop($dirAr);
    $bkdir = implode('/', $dirAr);
    $this->dir = $bkdir;
  }
  function createFolder($data) {
   $info = mkdir($data['curDir'] . '/' . $data['createFolder'], 0777);
   $this->dir = $data['curDir'];
  }
  function filesUpload($files, $dir) {
    for ($i = 0; $i < sizeof($files['filesUpload']['error']); $i++) {
      if ($files['filesUpload']['error'][$i] == 0){
        move_uploaded_file($files['filesUpload']['tmp_name'][$i], $dir . '/' . $files['filesUpload']['name'][$i]);
      }
    }
    $this->dir = $dir;
  }
  function auto($get, $files, $post) { 
    //go
    if (isset($get['goDir'])) {
      $this->goDir($get['goDir']);
    }
    //back
    if (isset($get['backDir'])) {
      $this->backDir($get['backDir']);
    }
    //rename
    if (isset($get['rename'])) {
      $this->rename($get);
    }
    //download
    if (isset($get['download'])) {
      $this->download($get);
    }
    //delete
    if (isset($get['delete'])) {
      $this->delete($get);
    }
    //createFolder
    if (isset($get['createFolder'])) {
      $this->createFolder($get);
    }
    //filesUpload
    if (isset($files['filesUpload'])) {
      $this->filesUpload($files, $get['curDir']);
    }
    return error_get_last();
  }
}

//action
$koppu = new SingleKoppu();
$error = $koppu->auto($_GET, $_FILES, $_POST);
$list = $koppu->fileList();
?>
<!DOCTYPE html>
<html>
<head>
<title>SingleKoppu v1.0</title>
</head>
<body>

<table>
  <tr>
    <th>Name</th>
    <th>Type</th>
    <th>Dwonload</th>
    <th>Rename</th>
    <th>Dalete</th>
  </tr>
  <tr><td colspan="5"><hr></td></tr>
  <tr>
    <td><a href="?backDir=<?php echo $koppu->dir; ?>">../back</a></td>
    <td align="center">[dir]</td>
    <td align="center">null</a></td>
    <td align="center">null</td>
    <td align="center">null</a></td>
  </tr>
  <tr><td colspan="5"><hr></td></tr>
  <?php for($i=0; $i<sizeof($list); $i++){ ?>
  <tr>
    <td><a href="<?php echo $list[$i]['view']; ?>"><?php echo $list[$i]['file']; ?></a></td>
    <td align="center">[<?php echo $list[$i]['type']; ?>]</td>
    <td align="center"><a href="<?php echo $list[$i]['download']; ?>" ><b>&veeeq;</b></a></td>
    <td>
      <input type="text" value="<?php echo $list[$i]['file']; ?>" koppu-rename-input><input koppu-data="<?php echo $list[$i]['file']; ?>" koppu-data-type="<?php echo $list[$i]['type']; ?>" koppu-data-new="<?php echo $list[$i]['file']; ?>" type="button" value="Rename" koppu-rename-action>
    </td>
    <td align="center"><a href="<?php echo $list[$i]['delete']; ?>">&xotime;</a></td>
  </tr>
  <tr><td colspan="5"><hr></td></tr>
  <?php } ?>
  <tr>
    <td colspan="2"><input type="text" placeholder="Create folder"  koppu-folder-input><input koppu-folder-action type="button" value="Create"></td>
    <form method="POST" action="?curDir=<?php echo $koppu->dir; ?>" multipart="" enctype="multipart/form-data">
    <td colspan="3"><input type="file" name="filesUpload[]" multiple><input type="submit" value="Upload"></td> 
    </form>
  </tr>
</table>
<p><a href="?goDir=<?php echo $koppu->dir; ?>"><?php echo $koppu->dir; ?></a></p>
<input type="hidden" koppu="dir" value="<?php echo $koppu->dir; ?>">
<pre>
<?php print_r($error); ?>
</pre>
<script>
  var singleKoppu = {
    renameInput: function(elem) {
      elem.nextElementSibling.setAttribute('koppu-data-new', elem.value);
    },
    renameAction: function(elem) {
      var dir = document.querySelector('input[koppu=dir]');
      window.location.href=`?rename=${elem.getAttribute('koppu-data')}&newName=${elem.getAttribute('koppu-data-new')}&type=${elem.getAttribute('koppu-data-type')}&curDir=${dir.value}`;
    },
    folderAction: function(elem) {
      var dir = document.querySelector('input[koppu=dir]');
      var input = document.querySelector('input[koppu-folder-input]');
      window.location.href = `?createFolder=${input.value}&curDir=${dir.value}`;
    }
  };

 var kRi = document.querySelectorAll('input[koppu-rename-input]');
 for (let i = 0; i < kRi.length; i++) {
  kRi[i].addEventListener('input', function() {
    singleKoppu.renameInput(kRi[i]);
  });
 }

 var kRa = document.querySelectorAll('input[koppu-rename-action]');
 for (let i = 0; i < kRa.length; i++) {
  kRa[i].addEventListener('click', function() {
    singleKoppu.renameAction(kRa[i]);
  });
 } 

 document.querySelector('input[koppu-folder-action]').addEventListener('click', function() {
  singleKoppu.folderAction(this);
 });
</script>
</body>
</html>
